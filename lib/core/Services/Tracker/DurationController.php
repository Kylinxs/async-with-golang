<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

class Services_Tracker_DurationController
{
    /**
     * Returns the section for use with certain features like banning
     * @return string
     */
    function getSection()
    {
        return 'trackers';
    }

    function action_drafts($input)
    {
        $id = $input->id->text();
        if (! empty($id)) {
            return $_SESSION['duration_drafts'][$id] ?? [];
        } else {
            return $_SESSION['duration_drafts'] ?? [];
        }
    }

    function action_update_draft($input)
    {
        $id = $input->id->text();
        $array1 = $_SESSION['duration_drafts'][$id];
        $array2 = $input->field->asArray();
        $_SESSION['duration_drafts'][$id] = array_merge((array)$array1, $array2);
        return $_SESSION['duration_drafts'][$id];
    }

    function action_delete_draft($input)
    {
        $id = $input->id->text();
        $deleted = $_SESSION['duration_drafts'][$id];
        if ($id) {
            unset($_SESSION['duration_drafts'][$id]);
        }
        return $deleted;
    }
}
