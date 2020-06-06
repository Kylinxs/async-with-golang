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

/**
 * Class Services_Utilities
 */
class Services_Utilities
{
    public $items;
    public $itemsCount;
    public $extra;
    public $toList;
    public $action;
    public $confirmController;

    /**
     * Provide referer url if javascript not enabled.
     *
     * @return bool|string
     */
    public static function noJsPath()
    {
        global $prefs;
        //no javascript
        if ($prefs['javascript_enabled'] !== 'y') {
            global $base_url;
            $referer = substr($_SERVER['HTTP_REFERER'], strlen($base_url));
        //javascript
        } else {
            $referer = false;
        }
        return $referer;
    }

    /**
     * Handle feedback after a non-modal form is clicked
     * Send feedback using Feedback class (using 'session' for the method parameter) first before using this.
     * Improves handling when javascript is not enabled compared to throwing a Services Exception because it takes the
     * user back to the page where the action was initiated and shows the error message there.
     *
     * @param bool $referer
     * @throws Exception
     */
    public static function sendFeedback($referer = false)
    {
        //no javascript
        if (! empty($referer)) {
            TikiLib::lib('access')->redirect($referer);
        //javascript
        } else {
            Feedback::sendHeaders();
            die;
        }
    }

    /**
     * Handle Feedback message after a modal is clicked.
     * Send feedback using Feedback class (using 'session' for the method parameter) first before using this.
     * Improves handling when javascript is not enabled compared to throwing a Services Exception because it takes the
     * user back to the page where the action was initiated and shows the error message there.
     *
     * @param bool $referer
     * @return array
     * @throws Exception
     */
    public static function closeModal($referer = false)
    {
        //no javascript
        if (! empty($referer)) {
            TikiLib::lib('access')->redirect($referer);
        //javascript
        } else {
            Feedback::sendHeaders();
            //the js confirmAction function in tiki-confirm.js uses this to close the modal
            return ['extra' => 'close'];
        }
    }

    /**
     * Handle feedback message when the page is being refreshed, e.g., after a successful action
     * Send feedback using Feedback class (using 'session' for the method parameter) first before using this.
     * Allows the same type of detailed feedback to be provided when javascript is not enabled.
     *
     * @param bool   $referer       Used in case javascript is disabled, otherwise set to false
     * @param string $strip         The url query or quary and anchor string can be stripped before reloading the page
     *
     * @return array
     * @throws Exception
     */
    public static function refresh($referer = false, $strip = '')
    {
        if (TIKI_API) {
            return ['feedback' => Feedback::get()];
        }
        //no javascript
        if (! empty($referer)) {
            $referer = new JitFilter(['referer' => $referer]);
            TikiLib::lib('access')->redirect($referer->referer->striptags());
        //javascript
        } else {
            //the js confirmAction function in tiki-confirm.js uses this to close the modal and refresh the page
            if (! empty($strip) && in_array($strip, ['anchor', 'queryAndAnchor'])) {
                return ['extra' => 'refresh', 'strip' => $strip];
            } else {
                return ['extra' => 'refresh'];
            }
        }
    }

    /**
     * Handle a redirect depending on whether javascript is enabled or not
     * Send any feedback using Feedback class (using 'session' for the method parameter) first before using this.
     *
     * @param $url
     * @return array
     * @throws Exception
     */
    public static function redirect($url)
    {
        //no javascript
        global $prefs;
        if ($prefs['javascript_enabled'] !== 'y') {
            TikiLib::lib('access')->redirect($url);
        //javascript
        } else {
            return ['url' => $url];
        }
    }

    /**
     * Handle exception when initially clicking a modal service action according to whether javascript is enabled or not.
     * Improves handling when javascript is not enabled compared to throwing a Services Exception because it takes the
     * user back to the page where the action was initiated and shows the error message there.
     *
     * @param $mes
     * @throws Exception
     * @throws Services_Exception
     */
    public static function modalException($mes)
    {
        $referer = self::noJsPath();
        //no javascript
        if (! empty($referer)) {
            TikiLib::lib('access')->redirect($referer, $mes, 0, 'error');
        //javascript
        } else {
            //this will show as a modal if exception occurs when first clicking the action
            throw new Services_Exception($mes);
        }
    }

    /**
     * The following functions are used in the services actions that first present a popup for confirmation before the
     * action is completed by the user confirm the action
     */


    /**
     * CSRF ticket - Check the ticket to either set it or match to the ticket previously set
     *
     * @param string $error
     * @return bool
     * @throws Exception
     * @throws Services_Exception
     */
    public function checkCsrf($error = 'services')
    {
        return TikiLib::lib('access')->checkCsrf(null, null, null, null, null, $error);
    }

    public function isConfirmPost()
    {
        $return = TikiLib::lib('access')->isActionPost() && isset($_POST['confirmForm']) && $_POST['confirmForm'] === 'y';
        if ($return) {
            return $this->checkCsrf();
        } else {
            return false;
        }
    }

    public function notConfirmPost()
    {
        return ! TikiLib::lib('access')->isActionPost() || ! isset($_POST['confirmForm']) || $_POST['confirmForm'] !== 'y';
    }

    public function isActionPost()
    {
        $access = TikiLib::lib('access');
        return $access->isActionPost() && $access->checkCsrf(null, null, null, null, null, 'services');
    }

    public function setTicket()
    {
        return TikiLib::lib('access')->setTicket();
    }

    public function getTicket()
    {
        return TikiLib::lib('access')->getTicket();
    }

    /**
     * Set the items, action and extra variables, and apply any filters
     *
     * @param JitFilter $input
     * @param array $filters
     * @param bool $itemsOffset
     * @throws Exception
     */
    public function setVars(JitFilter &$input, array $filters = [], $itemsOffset = false)
    {
        if (! empty($filters)) {
            $input->replaceFilters($filters);
        }
        $this->extra = $input->asArray();
        $this->action = $input->action->word();
        $this->confirmController = $input->controller->alnumdash();
        $this->toList = $input->asArray('toList');
        unset(
            $this->extra['action'],
            $this->extra['controller'],
            $this->extra['modal'],
            $this->extra['toList']
        );
        if ($itemsOffset) {
            $this->items = $input->asArray($itemsOffset);
            $this->itemsCount = count($this->items);
            unset($this->extra[$itemsOffset]);
        }
    }

    /**
     * Create array for standard confirmation popup
     *
     * @param $msg
     * @param $button
     * @param array $moreExtra
     * @return array
     */
    public function confirm($msg, $button, array $moreExtra = [])
    {
        $thisExtra = [];
        if (is_array($this->extra)) {
            $thisExtra = $this->extra;
        } elseif ($this->extra instanceof JitFilter) {
            $thisExtra = $this->extra->asArray();
        } elseif (strlen($this->extra) > 0) {
            $thisExtra = [$this->extra];
        }
        //provide redirect if js is not enabled
        $extra['referer'] = ! empty($moreExtra['referer']) ? $moreExtra['referer'] : Services_Utilities::noJsPath();
        $extra = array_merge($thisExtra, $extra, $moreExtra);
        $ret = [
            'FORWARD' => [
                'modal' => '1',
                'controller' => 'access',
                'action' => 'confirm',
                'confirmAction' => $this->action,
                'confirmController' => $this->confirmController,
                'customMsg' => $msg,
                'confirmButton' => $button,
                'items' => $this->items,
                'extra' => $extra,
            ]
        ];
        return $ret;
    }
}
