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
     * user back to the page where the action was initiated 