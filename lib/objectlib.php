
<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

//this script may only be included - so its better to die if called directly.
if (strpos($_SERVER['SCRIPT_NAME'], basename(__FILE__)) !== false) {
    header('location: index.php');
    exit;
}

class ObjectLib extends TikiLib
{
    const SECONDSPERDAY = 86400;

    /**
     *  Create an object record for the given Tiki object if one doesn't already exist.
     * Returns the object record OID. If the designated object does not exist, may return NULL.
     * If the object type is not handled and $checkHandled is TRUE, fail and return FALSE.
     * $checkHandled A boolean indicating whether only handled object types should be accepted when the object has no object record (legacy).
     * When creating, if $description is given, use the description, name and URL given as information.
     * Otherwise retrieve it from the object (if $checkHandled is FALSE, fill with empty strings if the object type is not handled).
     * Handled object types: "article", "blog", "calendar", "directory", "faq",
     * "file", "file gallery", "forum", "image gallery", "poll", "quiz", "tracker", "trackeritem", "wiki page" and "template".
     *
     * Remember to update get_supported_types if this changes
     */
    public function add_object($type, $itemId, $checkHandled = true, $description = null, $name = null, $href = null)
    {
        $objectId = $this->get_object_id($type, $itemId);

        if ($objectId) {
            if (! empty($description) || ! empty($name) || ! empty($href)) {
                $query = "update `tiki_objects` set `description`=?,`name`=?,`href`=? where `objectId`=?";
                $this->query($query, [$description, $name, $href, $objectId]);
            }
        } else {
            if (is_null($description)) {
                switch ($type) {
                    case 'article':
                        $artlib = TikiLib::lib('art');
                        $info = $artlib->get_article($itemId);

                        $description = $info['heading'];
                        $name = $info['title'];
                        $href = 'tiki-read_article.php?articleId=' . $itemId;
                        break;

                    case 'blog':
                        $bloglib = TikiLib::lib('blog');
                        $info = $bloglib->get_blog($itemId);

                        $description = $info['description'];
                        $name = $info['title'];
                        $href = 'tiki-view_blog.php?blogId=' . $itemId;
                        break;

                    case 'calendar':
                        $calendarlib = TikiLib::lib('calendar');
                        $info = $calendarlib->get_calendar($itemId);

                        $description = $info['description'];
                        $name = $info['name'];
                        $href = 'tiki-calendar.php?calId=' . $itemId;
                        break;

                    case 'directory':
                        $info = $this->get_directory($itemId);

                        $description = $info['description'];
                        $name = $info['name'];
                        $href = 'tiki-directory_browse.php?parent=' . $itemId;
                        break;

                    case 'faq':
                        $info = TikiLib::lib('faq')->get_faq($itemId);

                        $description = $info['description'];
                        $name = $info['title'];
                        $href = 'tiki-view_faq.php?faqId=' . $itemId;
                        break;

                    case 'file':
                        $filegallib = TikiLib::lib('filegal');
                        $info = $filegallib->get_file_info($itemId, false, false, false);

                        $description = $info['description'];
                        $name = $info['name'];
                        $href = 'tiki-upload_file.php?fileId=' . $itemId;
                        break;

                    case 'file gallery':
                        $filegallib = TikiLib::lib('filegal');
                        $info = $filegallib->get_file_gallery($itemId);

                        $description = $info['description'];
                        $name = $info['name'];
                        $href = 'tiki-list_file_gallery.php?galleryId=' . $itemId;
                        break;

                    case 'forum':
                        $commentslib = TikiLib::lib('comments');
                        $info = $commentslib->get_forum($itemId);

                        $description = $info['description'];
                        $name = $info['name'];
                        $href = 'tiki-view_forum.php?forumId=' . $itemId;
                        break;

                    case 'perspective':
                        $info = TikiLib::lib('perspective')->get_perspective($itemId);
                        $name = $info['name'];
                        $href = 'tiki-switch_perspective.php?perspective=' . $itemId;
                        break;

                    case 'poll':
                        $polllib = TikiLib::lib('poll');
                        $info = $polllib->get_poll($itemId);

                        $description = $info['title'];
                        $name = $info['title'];
                        $href = 'tiki-poll_form.php?pollId=' . $itemId;
                        break;

                    case 'quiz':
                        $info = TikiLib::lib('quiz')->get_quiz($itemId);

                        $description = $info['description'];
                        $name = $info['name'];
                        $href = 'tiki-take_quiz.php?quizId=' . $itemId;
                        break;

                    case 'tracker':
                        $trklib = TikiLib::lib('trk');
                        $info = $trklib->get_tracker($itemId);

                        $description = $info['description'];
                        $name = $info['name'];
                        $href = 'tiki-view_tracker.php?trackerId=' . $itemId;
                        break;

                    case 'trackeritem':
                        $trklib = TikiLib::lib('trk');
                        $info = $trklib->get_tracker_item($itemId);

                        $description = '';
                        $name = $trklib->get_isMain_value($info['trackerId'], $itemId);
                        $href = "tiki-view_tracker_item.php?itemId=$itemId&trackerId=" . $info['trackerId'];
                        break;

                    case 'wiki page':
                        if (! ($info = $this->get_page_info($itemId))) {
                            return;
                        }
                        $description = $info["description"];
                        $name = $itemId;
                        $href = 'tiki-index.php?page=' . urlencode($itemId);
                        break;

                    case 'template':
                        $info = TikiLib::lib('template')->get_template($itemId);

                        $description = '';
                        $name = $info['name'];
                        $href = "tiki-admin_content_templates.php?templateId=$itemId";
                        break;

                    default:
                        if ($checkHandled) {
                            return false;
                        } else {
                            $description = '';
                            $name = '';
                            $href = '';
                        }
                }
            }
            $objectId = $this->insert_object($type, $itemId, $description, $name, $href);
        }

        return $objectId;
    }

    /**
     * Returns an array of object types supported (and therefore can be categorised etc)
     *
     * @return array
     */
    public static function get_supported_types()
    {
        return [
            'article',
            'blog',
            'calendar',
            'directory',
            'faq',
            'file',
            'file gallery',
            'forum',
            'image gallery',
            'perspective',
            'poll',
            'quiz',
            'tracker',
            'trackeritem',
            'wiki page',
            'template',
        ];
    }

    public function getSelectorType($type)
    {
        $supported = [
            'category' => 'category',
            'file_gallery' => 'file gallery',
            'forum' => 'forum',
            'group' => 'group',
            'tracker' => 'tracker',
            'tracker_field' => 'trackerfield',
            'trackerfield' => 'trackerfield',
            'wiki_page' => 'wiki page',
            'wiki page' => 'wiki page',
            'template' => 'template',
        ];

        if (isset($supported[$type])) {
            return $supported[$type];
        } else {
            return false;
        }
    }

    public function insert_object($type, $itemId, $description = '', $name = '', $href = '')
    {
        if (! $itemId) {
            // When called with a blank page name or any other empty value, no insertion should be made
            return false;
        }

        $tikilib = TikiLib::lib('tiki');
        $table = $this->table('tiki_objects');
        return $table->insert(
            [
                'type' => $type,
                'itemId' => (string) $itemId,
                'description' => $description,
                'name' => $name,
                'href' => $href,
                'created' => (int) $tikilib->now,
                'hits' => 0,
                'comments_locked' => 'n',
            ]
        );
    }

    public function get_object_id($type, $itemId)
    {
        $query = "select `objectId` from `tiki_objects` where `type`=? and `itemId`=?";
        return $this->getOne($query, [$type, $itemId]);
    }

    // Returns an array containing the object ids of objects of the same type.
    // Each entry uses the item id as key and the object id as key. Items with no object id are ignored.
    public function get_object_ids($type, $itemIds)
    {
        if (empty($itemIds)) {
            return [];
        }

        $query = 'select `objectId`, `itemId` from `tiki_objects` where `type`=? and `itemId` IN (' .
                        implode(',', array_fill(0, count($itemIds), '?')) . ')';

        $result = $this->query($query, array_merge([$type], $itemIds));
        $objectIds = [];

        while ($res = $result->fetchRow()) {
            $objectIds[$res['itemId']] = $res['objectId'];
        }
        return $objectIds;
    }

    public function get_needed_perm($objectType, $action)
    {
        switch ($objectType) {
            case 'wiki page':
                // no return
            case 'wiki':
                switch ($action) {
                    case 'view':
                        // no return
                    case 'read':
                        return 'tiki_p_view';

                    case 'edit':
                        return 'tiki_p_edit';
                }
                // no return
            case 'article':
                switch ($action) {
                    case 'view':
                    case 'read':
                        return 'tiki_p_read_article';

                    case 'edit':
                        return 'tiki_p_edit_article';
                }
                // no return
            case 'post':
                switch ($action) {
                    case 'view':
                    case 'read':
                        return 'tiki_p_read_blog';

                    case 'edit':
                        return 'tiki_p_create_blog';
                }
                // no return
            case 'blog':
                switch ($action) {
                    case 'view':
                    case 'read':
                        return 'tiki_p_read_blog';

                    case 'edit':
                        return 'tiki_p_create_blog';
                }
                // no return
            case 'faq':
                switch ($action) {
                    case 'view':
                    case 'read':
                        return 'tiki_p_view_faqs';

                    case 'edit':
                        return 'tiki_p_admin_faqs';
                }
                // no return
            case 'file gallery':
                switch ($action) {
                    case 'view':
                    case 'read':
                        return 'tiki_p_view_file_gallery';

                    case 'edit':
                        return 'tiki-admin_file_galleries';
                }
                // no return
            case 'poll':
                switch ($action) {
                    case 'view':
                    case 'read':
                        return 'tiki_p_vote_poll';

                    case 'edit':
                        return 'tiki_p_admin';
                }
                // no return
            case 'comment':
            case 'comments':
                switch ($action) {
                    case 'view':
                    case 'read':
                        return 'tiki_p_read_comments';

                    case 'edit':
                        return 'tiki_p_edit_comments';
                }
                // no return
            case 'trackeritem':
                switch ($action) {
                    case 'view':
                    case 'read':
                        return 'tiki_p_view_trackers';

                    case 'edit':
                        return 'tiki_p_modify_tracker_items';
                }
                // no return
            case 'trackeritem_closed':
                switch ($action) {
                    case 'view':
                    case 'read':
                        return 'tiki_p_view_trackers';

                    case 'edit':
                        return 'tiki_p_modify_tracker_items_closed';
                }
                // no return
            case 'trackeritem_pending':
                switch ($action) {
                    case 'view':
                    case 'read':
                        return 'tiki_p_view_trackers';

                    case 'edit':
                        return 'tiki_p_modify_tracker_items_pending';
                }
                // no return
            case 'tracker':
                switch ($action) {
                    case 'view':
                    case 'read':
                        return 'tiki_p_list_trackers';

                    case 'edit':
                        return 'tiki_p_admin_trackers';
                }
                // no return
            case 'template':
                switch ($action) {
                    case 'view':
                    case 'read':
                        return 'tiki_p_use_content_templates';

                    case 'edit':
                        return 'tiki_p_edit_content_templates';
                }
                // no return
            default:
                return '';
        }
    }

    public function get_info($objectType, $object)
    {
        switch ($objectType) {
            case 'wiki':
            case 'wiki page':
                $tikilib = TikiLib::lib('tiki');
                $info = $tikilib->get_page_info($object);
                return ['title' => $object, 'data' => $info['data'], 'is_html' => $info['is_html']];

            case 'article':
                $artlib = TikiLib::lib('art');
                $info = $artlib->get_article($object);
                return ['title' => $info['title'], 'data' => $info['body']];

            case 'file gallery':
                $info = TikiLib::lib('filegal')->get_file_gallery_info($object);
                return ['title' => $info['name']];

            case 'blog':
                $info = TikiLib::lib('blog')->get_blog($object);
                return ['title' => $info['title']];

            case 'post':
            case 'blog post':
            case 'blogpost':
                $info = TikiLib::lib('blog')->get_post($object);
                return ['title' => $info['title']];

            case 'forum':
                $info = TikiLib::lib('comments')->get_forum($object);
                return ['title' => $info['name']];

            case 'forum post':
                $info = TikiLib::lib('comments')->get_comment($object);
                return ['title' => $info['title']];

            case 'tracker':
                $info = TikiLib::lib('trk')->get_tracker($object);
                return ['title' => $info['name']];

            case 'trackerfield':
                $info = TikiLib::lib('trk')->get_tracker_field($object);
                return ['title' => $info['name']];

            case 'goal':
                return TikiLib::lib('goal')->fetchGoal($object);

            case 'template':
                $info = TikiLib::lib('template')->get_template($object);
                return ['title' => $info['name']];
        }
        return (['error' => 'true']);
    }

    public function set_data($objectType, $object, $data)
    {
        switch ($objectType) {
            case 'wiki':
            case 'wiki page':
                global $user;
                $tikilib = TikiLib::lib('tiki');
                $tikilib->update_page($object, $data, tra('section edit'), $user, $tikilib->get_ip_address());
                break;
        }
    }

    public function delete_object($type, $itemId)
    {
        $query = 'delete from `tiki_objects` where `itemId`=? and `type`=?';
        $this->query($query, [$itemId, $type]);
    }

    public function delete_object_via_objectid($objectId)
    {
        $query = 'delete from `tiki_objects` where `objectId`=?';
        $this->query($query, [(int) $objectId]);
    }

    public function get_object($type, $itemId)
    {
        $query = 'select * from `tiki_objects` where `itemId`=? and `type`=?';
        $result = $this->query($query, [$itemId, $type]);
        return $result->fetchRow();
    }

    public function get_object_via_objectid($objectId)
    {
        $query = 'select * from `tiki_objects` where `objectId`=?';
        $result = $this->query($query, [(int) $objectId]);
        return $result->fetchRow();
    }

    /**
     * @param string      $type
     * @param string      $id
     * @param string|null $format - trackeritem format coming from ItemLink field or null by default
     *
     * @return void|string
     * @throws Exception
     */
    public function get_title($type, string $id, ?string $format = null)
    {
        $detail = '';
        switch ($type) {
            case 'trackeritemfield':
                $type = 'trackeritem';
                $ids = explode(':', $id);
                $id = (int)$ids[0];
                $fieldId = (int)$ids[1];
                $trackerlib = TikiLib::lib('trk');
                $info = $trackerlib->get_field_info($fieldId);
                $extra = $info['name'];
                // no return
            case 'trackeritem':
                $defaultTitle = TikiLib::lib('trk')->get_isMain_value(null, $id);
                $extra = $extra ?? '';
                return $this->getFormattedTitle($type, $id, $defaultTitle, $format, $extra);
            case 'category':
                return TikiLib::lib('categ')->get_category_name($id);
            case 'file':
                return TikiLib::lib('filegal')->get_file_label($id);
            case 'topic':
                $meta = TikiLib::lib('art')->get_topic($id);
                return $meta['name'];
            case 'group':
                return $id;
            case 'user':
                if (is_int($id)) {
                    $id = TikiLib::lib('tiki')->get_user_login($id);
                }
                return TikiLib::lib('user')->clean_user($id);
            case 'calendar':
                $info = TikiLib::lib('calendar')->get_calendar($id);
                return $info['name'];
            case 'calendar event':
            case 'calendaritem':
                $info = TikiLib::lib('calendar')->get_item($id);
                return $this->getFormattedTitle($type, $id, $info['name'], $format);
        }

        $title = $this->table('tiki_objects')->fetchOne(
            'name',
            [
                'type' => $type,
                'itemId' => $id,
            ]
        );

        if ($title) {
            return $title;
        }

        $info = $this->get_info($type, $id);

        if (isset($info['title'])) {
            return $info['title'];
        }

        if (isset($info['name'])) {
            return $info['name'];
        }
    }

    /**
     * @param string      $type
     * @param string      $id usually an int but strings for wiki pages
     * @param string|null $defaultTitle
     * @param string|null $format
     *
     * @param string|null $extra
     *
     * @return string
     * @throws Exception
     */
    public function getFormattedTitle(string $type, string $id, ?string $defaultTitle, ?string $format = '', ?string $extra = ''): string
    {
        if ($format) {
            $lib = TikiLib::lib('unifiedsearch');
            $query = $lib->buildQuery([
                'object_type' => $type,
                'object_id'   => $id
            ]);
            $result = $query->search($lib->getIndex());
            $result->applyTransform(function ($item) use ($format) {
                return preg_replace_callback('/\{(\w+)\}/', function ($matches) use ($item, $format) {
                    $key = $matches[1];
                    if (isset($item[$key])) {
                        return $item[$key];
                    } elseif (! $format || $format == '{title}') {
                        return tr('empty');
                    } else {
                        return '';
                    }
                }, $format);
            });
            $titles = $result->getArrayCopy();
            $title = array_shift($titles);
        } else {
            $title = $defaultTitle;
        }
        if (empty($title)) {
            $title = "$type:$id";
        }
        if (isset($extra) && $extra) {
            $title .= ' (' . $extra . ')';
        }
        return $title;
    }

    /**
     * Gets a wiki parsed content for an object. This is used in case an object can have wiki parsed
     * content that generates relations (ex: Plugin Include).
     *
     * This content can be used to find elements, but displaying to user might not be a good idea, since
     * text from different fields can be concatenated.
     *
     * @param string $type
     * @param $id
     * @return void|string
     */
    public function get_wiki_content($type, $objectId)
    {
        if (substr($type, -7) == 'comment') {
            $comment_info = TikiLib::lib('comments')->get_comment((int)$objectId);
            return $comment_info['data'];
        }

        switch ($type) {
            case 'wiki':
                $type = 'wiki page';
                // no return
            case 'wiki page':
                $info = $this->get_page_info($objectId);
                return $info['data'];
            case 'forum post':
                $comment_info = TikiLib::lib('comments')->get_comment((int)$objectId);
                return $comment_info['data'];
            case 'article':
                $info = TikiLib::lib('art')->get_article((int)$objectId);
                return $info['heading'] . "\n" . $info['body'];
            case 'tracker':
                $tracker_info = TikiLib::lib('trk')->get_tracker((int)$objectId);
                return $tracker_info['description'];
            case 'trackerfield':
                $field_info = TikiLib::lib('trk')->get_field_info((int)$objectId);
                return $field_info['description'];
            case 'trackeritemfield':
                $objectId = explode(':', $objectId);
                $itemId = (int)$objectId[0];
                $fieldId = (int)$objectId[1];
                $trackerlib = TikiLib::lib('trk');
                $item_info = $trackerlib->get_tracker_item($itemId);
                return $item_info[$fieldId];
        }
    }

    /**
     * @param string $type
     * @return string
     */
    public function get_verbose_type($type)
    {
        if (substr($type, -7) == 'comment') {
            $isComment = true;
            $type = substr($type, 0, strlen($type) - 8);
        } else {
            $isComment = false;
        }

        switch ($type) {
            case 'trackeritem':
                $type = 'tracker item';
                break;
            case 'trackeritemfield':
                $type = 'tracker item field';
                break;
            case 'trackerfield':
                $type = 'tracker field';
                break;
            case 'wiki':
                $type = 'wiki page';
                break;
        }

        if ($isComment) {
            $type .= " comment";
        }

        return tra(ucwords($type));
    }

    /**
     * Returns a hash indicating which permission is needed for viewing an object of desired type.
     *
     * @param boolean $comment - indicate if returned permission must be comment-related, e.g.
     * am I allowed to see comments on a tracker item if I have or don't have tiki_p_tracker_view_comments.
     * This allows search index to properly update comment permissions not basing them on viewing
     * parent tracker item or wiki page but the comment itself.
     */
    public static function map_object_type_to_permission($comment = false)
    {
        return [
            'wiki page' => $comment ? 'tiki_p_wiki_view_comments' : 'tiki_p_view',
            'wiki' => $comment ? 'tiki_p_wiki_view_comments' : 'tiki_p_view',
            'forum' => 'tiki_p_forum_read',
            'forum post' => 'tiki_p_forum_read',
            'file gallery' => 'tiki_p_view_file_gallery',
            'tracker' => 'tiki_p_view_trackers',
            'blog' => 'tiki_p_read_blog',
            'quiz' => 'tiki_p_take_quiz',
            'template' => 'tiki_p_use_content_templates',

            // overhead - we are checking individual permission on types below, but they
            // can't have individual permissions, although they can be categorized.
            // should they have permissions too?
            'poll' => 'tiki_p_vote_poll',
            'survey' => 'tiki_p_take_survey',
            'directory' => 'tiki_p_view_directory',
            'faq' => 'tiki_p_view_faqs',
            'sheet' => 'tiki_p_view_sheet',

            // these ones are tricky, because permission type is for container, not object itself.
            // I think we need to refactor permission schemes for them to be wysiwyca - lfagundes
            //
            // by now they're not showing, list_category_objects needs support for ignoring permissions
            // for a type.
            'blog post' => 'tiki_p_read_blog',
            'article' => 'tiki_p_read_article',
            'submission' => 'tiki_p_approve_submission',
            'calendar' => 'tiki_p_view_calendar',
            'file' => 'tiki_p_download_files',
            'trackeritem' => $comment ? 'tiki_p_tracker_view_comments' : 'tiki_p_view_trackers',

            // newsletters can't be categorized, although there's some code in tiki-admin_newsletters.php
            // 'newsletter' => ?,
            // 'events' => ?,
        ];
    }

    public function get_metadata($type, $object, &$classList)
    {
        $smarty = TikiLib::lib('smarty');
        $smarty->loadPlugin('smarty_modifier_escape');

        $escapedType = smarty_modifier_escape($type);
        $escapedObject = smarty_modifier_escape($object);
        $metadata = ' data-type="' . $escapedType . '" data-object="' . $escapedObject . '"';

        if ($coordinates = TikiLib::lib('geo')->get_coordinates($type, $object)) {
            $classList[] = 'geolocated';
            $metadata .= " data-geo-lat=\"{$coordinates['lat']}\" data-geo-lon=\"{$coordinates['lon']}\"";

            if (isset($coordinates['zoom'])) {
                $metadata .= " data-geo-zoom=\"{$coordinates['zoom']}\"";
            }
        }

        $attributelib = TikiLib::lib('attribute');
        $attributes = $attributelib->get_attributes($type, $object);

        if (isset($attributes['tiki.icon.src'])) {
            $escapedIcon = smarty_modifier_escape($attributes['tiki.icon.src']);
            $metadata .= " data-icon-src=\"$escapedIcon\"";
        }

        return $metadata;
    }

    public function get_typeItemsInfo($type) // Returns information on all items of an object type (eg: menu, article, etc) from tiki_objects table
    {
        //get objects
        $queryObjectInfo = 'select * from `tiki_objects` where `type`=?';
        $resultObjectInfo = $this->fetchAll($queryObjectInfo, [$type]);

        //get object attributes
        foreach ($resultObjectInfo as &$tempInfo) {
            $objectAttributes = TikiLib::lib('attribute')->get_attributes($tempInfo['type'], $tempInfo['objectId']);
            $tempInfo = array_merge($tempInfo, $objectAttributes);
        }
        unset($tempInfo);

        //return information
        return $resultObjectInfo;
    }

    public function get_maintainers() // get all objects that have maintainers ??? GET_MAINTAINED_OBJECTS
    {
        $relationlib = TikiLib::lib('relation');
        return $relationlib->get_related_objects('tiki.object.maintainer');
    }

    public function set_maintainers($objectId, array $maintainers, $type = 'wiki page')
    {
        $relationlib = TikiLib::lib('relation');

        foreach ($maintainers as $maintainer) {
            $relationlib->add_relation('tiki.object.maintainer', $type, $objectId, 'user', $maintainer);
        }
    }

    public function get_freshness($objectId, $type = 'wiki page')
    {
        if ($type === 'wiki page') {
            $info = TikiLib::lib('tiki')->get_page_info($objectId, false);
            if (isset($info)) {
                $lastModif = $info['lastModif'];
                $freshness = (int) ((time() - $lastModif) / self::SECONDSPERDAY);
                return $freshness;
            }
        } else {
            Feedback::error(tr('Object freshness not supported yet for object type %0', $type));
        }

        return false;
    }
}