<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

/**
 * Tiki tracker modules
 * @package modules
 * @subpackage tiki
 */

if (! defined('DEBUG_MODE')) {
    die();
}

/**
 * Prepare message page title
 * @subpackage tiki/handler
 */
class Hm_Handler_tracker_message_list_type extends Hm_Handler_Module
{
    public function process()
    {
        $path = $this->request->get['list_path'];
        if (! strstr($path, 'tracker_folder_')) {
            return;
        }
        $title = ['Tracker Folder'];
        $trackerId = str_replace('tracker_', '', $this->request->get['list_parent']);
        $path = str_replace('tracker_folder_', '', $this->request->get['list_path']);
        list ($itemId, $fieldId) = explode('_', $path);
        $definition = Tracker_Definition::get($trackerId);
        if ($definition) {
            $title[] = $definition->getConfiguration('name');
            $field = $definition->getField($fieldId);
            if ($field) {
                $title[] = $field['name'];
            }
            if ($itemId) {
                $title[] = TikiLib::lib('trk')->get_isMain_value($trackerId, $itemId);
            }
        }
        $this->out('mailbox_list_title', $title);
    }
}

/**
 * Check for tracker item link redirect on message list page
 * @subpackage tiki/handler
 */
class Hm_Handler_check_path_redirect extends Hm_Handler_Module
{
    public function process()
    {
        global $smarty;
        $smarty->loadPlugin('smarty_modifier_sefurl');
        $path = $this->request->get['list_path'];
        if (preg_match("/tracker_folder_(\d+)_(\d+)/", $path, $m)) {
            $url = smarty_modifier_sefurl($m[1], 'trackeritem');
            Hm_Dispatch::page_redirect($url);
        }
    }
}

/**
 * Check for tracker item link redirect after compose msg is sent
 * @subpackage tiki/handler
 */
class Hm_Handler_check_path_redirect_after_sent extends Hm_Handler_Module
{
    public function process()
    {
        global $smarty;
        $smarty->loadPlugin('smarty_modifier_sefurl');
        if (! $this->get('msg_sent')) {
            return;
        }
        $path = $this->request->post['compose_msg_path'];
        if (preg_match("/tracker_folder_(\d+)_(\d+)/", $path, $m)) {
            $url = smarty_modifier_sefurl($m[1], 'trackeritem');
            Hm_Dispatch::page_redirect($url);
        }
    }
}

/**
 * Move an email to a tracker Email Folder field
 * @subpackage tiki/handler
 */
class Hm_Handler_move_to_tracker extends Hm_Handler_Module
{
    public function process()
    {
        global $smarty;

        list($success, $form) = $this->process_form(['tracker_field_id', 'tracker_item_id', 'imap_msg_ids', 'list_path', 'folder']);
        if (! $success) {
            return;
        }

        $msg_ids = explode(',', $form['imap_msg_ids']);
        $errors = 0;
        $msgs = [];
        $headers_array = [];

        if (preg_match("/^imap_(\w+)_(.+)/", $form['list_path'], $matches)) {
            $imap_server_id = $matches[1];
            $folder = hex2bin($matches[2]);
            $cache = Hm_IMAP_List::get_cache($this->cache, $imap_server_id);
            $imap = Hm_IMAP_List::connect($imap_server_id, $cache);
            if (! imap_authed($imap)) {
                Hm_Msgs::add('ERRCould not authenticate with mail server');
                return;
            }
            if (! $imap->select_mailbox($folder)) {
                Hm_Msgs::add('ERRMailbox not found');
                return;
            }


            foreach ($msg_ids as $msg_id) {
                list($msgs[], $headers_array[]) = get_message_data($imap, $msg_id);
            }

            bind_tracker_item_update_event($imap, $form, $msg_ids);
        } elseif (preg_match("/^tracker_folder_/", $form['list_path'], $matches)) {
            $email = tiki_parse_message($form['list_path'], $msg_ids[0]);
            if (! $email) {
                Hm_Msgs::add('ERRMessage could not be loaded');
                return;
            }
            if (isset($form['folder']) && $form['folder'] != 'archive') {
                tiki_flag_message($email['fileId'], 'remove', 'archive');
            }
            if (isset($form['folder']) && $form['folder'] != 'trash') {
                tiki_flag_message($email['fileId'], 'remove', 'deleted');
            }
            $file = Tiki\FileGallery\File::id($email['fileId']);
            $msg = $file->getContents();

            $headers = [
                'Message-ID' => $email['message_id'],
                'Subject' => $email['subject'],
            ];

            $msgs[] = $msg;
            $headers_array[] = $headers;

            // ensure file was saved before removing it from Tiki
            TikiLib::events()->bind('tiki.trackeritem.update', function ($args) {
                $email = $args['email'];
                $trk = TikiLib::lib('trk');
                $field = $trk->get_field_info($email['fieldId']);
                if (! $field) {
                    return;
                }
                static $called = false;
                if (! $called) {
                    $called = true;
                    $field['value'] = [
                        'delete' => $email['fileId'],
                        'skip_trash' => true
                    ];
                    $trk->replace_item($email['trackerId'], $email['itemId'], [
                        'data' => [$field]
                    ]);
                }
            }, ['email' => $email]);
        } elseif (in_array($form['list_path'], ['sent', 'unread', 'combined_inbox', 'flagged'])) {
            $imaps = [];
            $ids = [];
            foreach ($msg_ids as $msg_id) {
                $full_path = explode('_', $msg_id);
                $imap_server_id = $full_path[1];
                $folder = hex2bin($full_path[3]);

                if (! in_array($imap_server_id, array_keys($imaps))) {
                    $cache = Hm_IMAP_List::get_cache($this->cache, $imap_server_id);
                    $imap_data = Hm_IMAP_List::connect($imap_server_id, $cache);

                    if (! imap_authed($imap_data)) {
                        $errors++;
                        continue;
                    }

                    $imaps[$imap_server_id] = $imap_data;
                }

                $imap = $imaps[$imap_server_id];
                if (! $imap->select_mailbox($folder)) {
                    $errors++;
                    continue;
                }

                list($msgs[], $headers_array[]) = get_message_data($imap, $full_path[2]);
                $ids[] = $full_path[2];
            }
            if (count($ids)) {
                bind_tracker_item_update_event($imap, $form, $ids);
            }
        } else {
            Hm_Msgs::add('ERRMessage from this source could not be moved');
            return;
        }

        $trk = TikiLib::lib('trk');
        $item = $trk->get_item_info($form['tracker_item_id']);

        if (! $item) {
            Hm_Msgs::add('ERRTracker item not found');
            return;
        }

        $field = $trk->get_field_info($form['tracker_field_id']);
        if (! $field) {
            Hm_Msgs::add('ERRTracker field not found');
            return;
        }

        $new_values = [];
        foreach ($msgs as $key => $msg) {
            $headers = $headers_array[$key];
            if ($this->session->get('page_id')) {
                $msg = "X-Tiki-Source: " . $this->session->get('page_id') . "\r\n" . $msg;
            }

            $new_values[] = [
                'name' => ! empty($headers['Message-ID']) ? $headers['Message-ID'] : $headers['Subject'],
                'size' => strlen($msg),
                'type' => 'message/rfc822',
                'content' => $msg
            ];
        }

        $field['value'] = [
            'new' => $new_values,
            'folder' => $form['folder'] ?? 'inbox'
        ];

        $trk->replace_item($item['trackerId'], $item['itemId'], [
            'data' => [$field]
        ]);

        $total_msg_ids = count($msg_ids);
        if ($errors > 0 && $errors < $total_msg_ids) {
            Hm_Msgs::add('Some messages moved');
        } elseif ($total_msg_ids == $errors) {
            Hm_Msgs::add('ERRUnable to move/copy selected messages');
            return;
        } else {
            Hm_Msgs::add('Messages moved');
        }

        $smarty->loadPlugin('smarty_modifier_sefurl');
        $url = smarty_modifier_sefurl($item['itemId'], 'trackeritem');
        $this->out('tiki_redirect_url', $url);
    }
}

/**
 * Marks a Tiki-stored message as answered
 * @subpackage tiki/handler
 */
class Hm_Handler_tiki_mark_as_answered extends Hm_Handler_Module
{
    public function process()
    {
        if (! $this->get('msg_sent')) {
            return;
        }

        $path = $this->request->post['compose_msg_path'];
        if (! strstr($path, 'tracker_folder_')) {
            return;
        }

        $uid = $this->request->post['compose_msg_uid'];
        Hm_Msgs::add('Uid - ' . $uid);
        tiki_flag_message($uid, 'add', 'answered');
    }
}

/**
 * Save a sent message to EmailFolder field
 * @subpackage tiki/handler
 */
class Hm_Handler_tiki_save_sent extends Hm_Handler_Module
{
    public function process()
    {
        if (! $this->get('save_sent_msg')) {
            return;
        }
        $mime = $this->get('save_sent_msg');
        $msg = $mime->get_mime_msg();
        $headers = $mime->get_headers();

        $path = $this->request->post['compose_msg_path'];
        if (! strstr($path, 'tracker_folder_')) {
            return;
        }
        $path = str_replace('tracker_folder_', '', $path);
        list ($itemId, $fieldId) = explode('_', $path);

        $trk = TikiLib::lib('trk');
        $item = $trk->get_item_info($itemId);
        if (! $item) {
            Hm_Msgs::add('ERRTracker item not found');
            return;
        }
        $field = $trk->get_field_info($fieldId);
        if (! $field) {
            Hm_Msgs::add('ERRTracker field not found');
            return;
        }
        $field['value'] = [
            'folder' => 'sent',
            'new' => [
                'name' => ! empty($headers['Message-Id']) ? $headers['Message-Id'] : $headers['Subject'],
                'size' => strlen($msg),
                'type' => 'message/rfc822',
                'content' => $msg
            ]
        ];
        $trk->replace_item($item['trackerId'], $item['itemId'], [
            'data' => [$field]
        ]);
    }
}

/**
 * Archive a replied message
 * @subpackage tiki/handler
 */
class Hm_Handler_tiki_archive_replied extends Hm_Handler_Module
{
    public function process()
    {
        if (empty($this->request->post['tiki_archive_replied'])) {
            return;
        }

        $path = $this->request->post['compose_msg_path'];
        if (! strstr($path, 'tracker_folder_')) {
            return;
        }
        $msg_uid = $this->request->post['compose_msg_uid'];
        if (! $msg_uid) {
            return;
        }

        tiki_flag_message($msg_uid, 'add', 'archive');
        tiki_flag_message($msg_uid, 'remove', 'deleted');

        $path = str_replace('tracker_folder_', '', $path);
        list ($itemId, $fieldId) = explode('_', $path);

        $trk = TikiLib::lib('trk');
        $item = $trk->get_item_info($itemId);
        if (! $item) {
            Hm_Msgs::add('ERRTracker item not found');
            return;
        }

        $field = $trk->get_field_info($fieldId);
        if (! $field) {
            Hm_Msgs::add('ERRTracker field not found');
            return;
        }

        $field['value'] = [
            'archive' => $msg_uid
        ];
        $trk->replace_item($item['trackerId'], $item['itemId'], [
            'data' => [$field]
        ]);
    }
}

/**
 * Delete a message
 * @subpackage tiki/handler
 */
class Hm_Handler_tiki_delete_message extends Hm_Handler_Module
{
    /**
     * Remove from the related EmailFolder field
     */
    public function process()
    {
        list($success, $form) = $this->process_form(['imap_msg_uid', 'list_path']);
        if ($success) {
            tiki_flag_message($form['imap_msg_uid'], 'add', 'deleted');
            tiki_flag_message($form['imap_msg_uid'], 'remove', 'archive');

            $path = str_replace('tracker_folder_', '', $form['list_path']);
            list ($itemId, $fieldId) = explode('_', $path);
            $trk = TikiLib::lib('trk');
            $item = $trk->get_item_info($itemId);
            if (! $item) {
                Hm_Msgs::add('ERRTracker item not found');
                $this->out('delete_error', true);
                return;
            }
            $field = $trk->get_field_info($fieldId);
            if (! $field) {
                Hm_Msgs::add('ERRTracker field not found');
                $this->out('delete_error', true);
                return;
            }
            $field['value'] = [
                'delete' => $form['imap_msg_uid']
            ];
            $trk->replace_item($item['trackerId'], $item['itemId'], [
                'data' => [$field]
            ]);
            Hm_Msgs::add('Message deleted');
            $this->out('delete_error', false);
        }
    }
}

/**
 * Archive a message
 * @subpackage tiki/handler
 */
class Hm_Handler_tiki_archive_message extends Hm_Handler_Module
{
    /**
     * Move from the related EmailFolder field to Archive folder
     */
    public function process()
    {
        list($success, $form) = $this->process_form(['imap_msg_uid', 'list_path']);
        if ($success) {
            tiki_flag_message($form['imap_msg_uid'], 'add', 'archive');
            tiki_flag_message($form['imap_msg_uid'], 'remove', 'deleted');

            $path = str_replace('tracker_folder_', '', $form['list_path']);
            list ($itemId, $fieldId) = explode('_', $path);
            $trk = TikiLib::lib('trk');
            $item = $trk->get_item_info($itemId);
            if (! $item) {
                Hm_Msgs::add('ERRTracker item not found');
                $this->out('archive_error', true);
                return;
            }
            $field = $trk->get_field_info($fieldId);
            if (! $field) {
                Hm_Msgs::add('ERRTracker field not found');
                $this->out('archive_error', true);
                return;
            }
            $field['value'] = [
                'archive' => $form['imap_msg_uid']
            ];
            $trk->replace_item($item['trackerId'], $item['itemId'], [
                'data' => [$field]
            ]);
            Hm_Msgs::add('Message archived');
            $this->out('archive_error', false);
        }
    }
}

/**
 * Flag a message
 * @subpackage tiki/handler
 */
class Hm_Handler_flag_tiki_message extends Hm_Handler_Module
{
    /**
     * Use IMAP to flag the selected message uid
     */
    public function process()
    {
        list($success, $form) = $this->process_form(['imap_msg_uid', 'list_path']);
        if ($success) {
            $email = tiki_parse_message($form['list_path'], $form['imap_msg_uid']);
            if (! $email) {
                return;
            }
            $flag_state = tiki_toggle_flag_message($email['fileId'], 'flagged');
            $this->out('flag_state', $flag_state);
            $this->out('show_archive', $email['show_archive']);
        }
    }
}

/**
 * Perform an action against a Tiki stored message
 * @subpackage imap/handler
 */
class Hm_Handler_tiki_message_action extends Hm_Handler_Module
{
    public function process()
    {
        list($success, $form) = $this->process_form(['action_type', 'imap_msg_uid', 'list_path']);
        if ($success) {
            $email = tiki_parse_message($form['list_path'], $form['imap_msg_uid']);
            if (! $email) {
                return;
            }
            switch ($form['action_type']) {
                case 'unread':
                    tiki_flag_message($email['fileId'], 'remove', 'seen');
                    break;
            }
        }
    }
}

/**
 * Get message content from Tiki tracker EmailField storage and prepare for imap module display
 * @subpackage tiki/handler
 */
class Hm_Handler_tiki_message_content extends Hm_Handler_Module
{
    public function process()
    {
        list($success, $form) = $this->process_form(['imap_msg_uid']);
        if (! $success) {
            return;
        }

        $this->out('msg_text_uid', $form['imap_msg_uid']);
        $this->out('msg_list_path', $this->request->post['list_path']);
        $part_num = false;
        if (isset($this->request->post['imap_msg_part']) && preg_match("/[0-9\.]+/", $this->request->post['imap_msg_part'])) {
            $part_num = $this->request->post['imap_msg_part'];
        }
        if (array_key_exists('imap_allow_images', $this->request->post) && $this->request->post['imap_allow_images']) {
            $this->out('imap_allow_images', true);
        }
        $this->out('header_allow_images', $this->config->get('allow_external_image_sources'));

        $email = tiki_parse_message($this->request->post['list_path'], $form['imap_msg_uid']);
        if (! $email) {
            return;
        }

        tiki_flag_message($email['fileId'], 'add', 'seen');

        $message = $email['message_raw'];

        $msg_headers = [];
        foreach ($message->getAllHeaders() as $header) {
            if (function_exists('mb_decode_mimeheader')) {
                $msg_headers[$header->getName()] = mb_decode_mimeheader($header->getRawValue());
            } else {
                $msg_headers[$header->getName()] = $header->getRawValue();
            }
        }
        $msg_struct = tiki_mime_part_to_bodystructure($message);
        $msg_struct_current = [];

        if ($part_num !== false) {
            $part = tiki_get_mime_part($message, $part_num);
        } else {
            if (! $this->user_config->get('text_only_setting', false)) {
                $part = $message->getHtmlPart();
                if (! $part) {
                    $part = $message->getTextPart();
                }
            } else {
                $part = $message->getTextPart();
            }
        }
        if ($part_num !== false && $part_num == 0) {
            $file = Tiki\FileGallery\File::id($email['fileId']);
            $msg_text = $file->getContents();
        } else {
            $msg_text = $part->getContent();
        }
        $list = explode('/', $part->getContentType());
        $msg_struct_current = [
            'type' => $list[0],
            'subtype' => $list[1],
        ];
        if ($msg_struct_current['subtype'] == 'html') {
            $msg_text = tiki_add_attached_images($message, $msg_text);
        }

        $this->out('msg_struct', $msg_struct);
        $this->out('list_headers', get_list_headers($msg_headers));
        $this->out('msg_headers', $msg_headers);
        $this->out('imap_msg_part', "$part_num");
        $this->out('use_message_part_icons', $this->user_config->get('msg_part_icons_setting', false));
        $this->out('simple_msg_part_view', $this->user_config->get('simple_msg_parts_setting', false));
        if ($msg_struct_current) {
            $this->out('msg_struct_current', $msg_struct_current);
        }
        $this->out('msg_text', $msg_text);
        $this->out('msg_download_args', sprintf("page=message&amp;uid=%s&amp;list_path=%s&amp;tiki_download_message=1", $form['imap_msg_uid'], $this->request->post['list_path']));
        $this->out('msg_show_args', sprintf("page=message&amp;uid=%s&amp;list_path=%s&amp;tiki_show_message=1", $form['imap_msg_uid'], $this->request->post['list_path']));
        if ($email['prev']) {
            $this->out('msg_prev_link', sprintf("?page=message&amp;uid=%s&amp;list_path=tracker_folder_%s_%s&list_parent=tracker_%s", $email['prev']['fileId'], $email['prev']['itemId'], $email['prev']['fieldId'], $email['prev']['trackerId']));
            $this->out('msg_prev_subject', $email['prev']['subject']);
        }
        if ($email['next']) {
            $this->out('msg_next_link', sprintf("?page=message&amp;uid=%s&amp;list_path=tracker_folder_%s_%s&list_parent=tracker_%s", $email['next']['fileId'], $email['next']['itemId'], $email['next']['fieldId'], $email['next']['trackerId']));
            $this->out('msg_next_subject', $email['next']['subject']);
        }
        $this->out('show_archive', $email['show_archive']);

        clear_existing_reply_details($this->session);
        if ($part == 0) {
            $msg_struct_current['type'] = 'text';
            $msg_struct_current['subtype'] = 'plain';
        }
        $this->session->set(
            sprintf('reply_details_%s_%s', $this->request->post['list_path'], $form['imap_msg_uid']),
            ['ts' => time(), 'msg_struct' => $msg_struct_current, 'msg_text' => $msg_text, 'msg_headers' => $msg_headers]
        );
    }
}

/**
 * Retrieve a message part and show in the browser (mainly images)
 * @subpackage tiki/handler
 */
class Hm_Handler_tiki_download_message extends Hm_Handler_Module
{
    public function process()
    {
        $show = $download = false;
        if (array_key_exists('tiki_show_message', $this->request->get) && $this->request->get['tiki_show_message']) {
            $show = true;
        }
        if (array_key_exists('tiki_download_message', $this->request->get) && $this->request->get['tiki_download_message']) {
            $download = true;
        }
        if ($show || $download) {
            $uid = null;
            $list_path = null;
            $msg_id = null;
            if (array_key_exists('uid', $this->request->get) && $this->request->get['uid']) {
                $uid = $this->request->get['uid'];
            }
            if (array_key_exists('list_path', $this->request->get) && $this->request->get['list_path']) {
                $list_path = $this->request->get['list_path'];
            }
            if (array_key_exists('imap_msg_part', $this->request->get) && preg_match("/^[0-9\.]+$/", $this->request->get['imap_msg_part'])) {
                $msg_id = $this->request->get['imap_msg_part'];
            }
            if ($list_path !== null && $uid !== null && $msg_id !== null) {
                $email = tiki_parse_message($list_path, $uid);
                if (! $email) {
                    return;
                }

                $message = $email['message_raw'];
                $part = tiki_get_mime_part($message, $msg_id);

                if ($download) {
                    $struct = tiki_mime_part_to_bodystructure($part, "0");
                    $name = get_imap_part_name($struct[0], $uid, preg_replace("/^0\./", "", $msg_id));
                    header('Content-Disposition: attachment; filename="' . $name . '"');
                }

                header('Content-Type: ' . $part->getContentType());
                header('Content-Transfer-Encoding: binary');
                ob_end_clean();
                echo $part->getContent();
                Hm_Functions::cease();
            }
            Hm_Msgs::add('ERRAn Error occurred trying to download the message');
        }
    }
}

/**
 * Process a move/copy action
 * @subpackage tiki/handler
 */
class Hm_Handler_tiki_process_move extends Hm_Handler_Module
{
    public function process()
    {
        list($success, $form) = $this->process_form(['imap_move_to', 'imap_move_action', 'imap_move_ids']);
        if ($success) {
            $moved = 0;
            $dest_path = explode('_', $form['imap_move_to']);
            $msg_ids = explode(',', $form['imap_move_ids']);
            foreach ($msg_ids as $msg_id) {
                list($list_path, $uid) = explode('#', $msg_id);
                $email = tiki_parse_message($list_path, $uid);
                if (! $email) {
                    continue;
                }
                $result = tiki_move_to_imap_server($email, $form['imap_move_action'], $dest_path, $this->cache);
                if ($result) {
                    $moved++;
                }
            }
            if ($moved == 0) {
                Hm_Msgs::add('ERRUnable to move/copy selected messages');
            } elseif ($form['imap_move_action'] == 'move') {
                Hm_Msgs::add($moved == 1 ? 'Message moved' : $moved . ' messages moved');
            } else {
                Hm_Msgs::add($moved == 1 ? 'Message copied' : $moved . ' messages copied');
            }
            $this->out('move_count', $moved);
        }
    }
}

/**
 * Add Move to trackers button
 * @subpackage tiki/output
 */
class Hm_Output_add_move_to_trackers extends Hm_Output_Module
{
    protected function output()
    {
        $res = '| ' . tiki_move_to_tracker_dropdown($this);
        $headers = $this->get('msg_headers');
        $headers = preg_replace("#<a class=\"archive_link[^>]*>.*?</a>#", "\\0 " . $res, $headers);
        $this->out('msg_headers', $headers, false);
    }
}

/**
 * Add move/copy dialog to the message list controls
 * @subpackage imap/output
 */
class Hm_Output_add_multiple_move_to_trackers extends Hm_Output_Module
{
    protected function output()
    {
        $res = tiki_move_to_tracker_dropdown($this);
        $this->concat('msg_controls_extra', $res);
    }
}

/**
 * @subpackage tiki/handler
 */
class Hm_Handler_tiki_get_trackers extends Hm_Handler_Module
{
    public function process()
    {
    }
}


class Hm_Output_tiki_get_trackers_output extends Hm_Output_Module
{
    public function output()
    {
        $trackers = '<div id="trackers_dropdown">' . tiki_move_to_tracker_dropdown($this) . '</div>';
        $this->out('trackers', $trackers);
    }
}

/**
 * @subpackage tiki/handler
 */
class Hm_Handler_tiki_tracker_info extends Hm_Handler_Module
{
    public function process()
    {
        list($success, $form) = $this->process_form(['tracker_field_id', 'tracker_item_id', 'folder']);
        if (! $success) {
            return;
        }
        $folder_name = 'Inbox';
        $trklib = TikiLib::lib('trk');
        $objectlib = TikiLib::lib('object');
        $field_data = $trklib->get_field_info($form['tracker_field_id']);

        $item = $objectlib->get_title('trackeritem', $form['tracker_item_id']);
        $tracker = $objectlib->get_title('tracker', $field_data['trackerId']);
        $field = $objectlib->get_title('trackerfield', $form['tracker_field_id']);

        $handler = $trklib->get_field_handler($field_data);
        if ($handler->getOption('useFolders')) {
            $folder_name = $handler->getFolders()[$form['folder']];
        }

        $out = $tracker . ' - ' . $field . ' - ' . $item . ' (' . $folder_name . ')';

        $this->out('tracker_data', $out);
    }
}


class Hm_Output_tiki_tracker_info_output extends Hm_Output_Module
{
    public function output()
    {
        $this->out('tracker_data', $this->get('tracker_data'));
    }
}

/**
 * Forward handler vars to the frontend
 * @subpackage tiki/output
 */
class Hm_Output_forward_variables extends Hm_Output_Module
{
    protected function output()
    {
        $this->out('msg_prev_link', $this->get('msg_prev_link'));
        $this->out('msg_prev_subject', $this->get('msg_prev_subject'));
        $this->out('msg_next_link', $this->get('msg_next_link'));
        $this->out('msg_next_subject', $this->get('msg_next_subject'));
        $this->out('show_archive', $this->get('show_archive'));
        $this->out('flag_state', $this->get('flag_state'));
    }
}

/**
 * Pass redirect_url param to the output
 * @subpackage tiki/output
 */
class Hm_Output_pass_redirect_url extends Hm_Output_Module
{
    protected function output()
    {
        $url = $this->get('tiki_redirect_url');
        $this->out('tiki_redirect_url', $url);
    }
}
