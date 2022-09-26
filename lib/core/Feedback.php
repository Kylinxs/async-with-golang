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
 * Class Feedback
 *
 * Class for adding feedback to the top of the page either through the php SESSION['tikifeedback'] global variable or
 * through a {$tikifeedback} Smarty template variable. The {remarksbox} Smarty function is used so that errors,
 * warnings, notes, success feedback types and related styling are available.
 *
 * Through this class and the use of the smarty function {feedback} in the basic page layout templates (layout_view.tpl),
 * such feedback can be sent, retreived and displayed without any Smarty template coding needed. Custom templates can
 * also be added for additional use cases.
 *
 */
class Feedback
{
    /**
     * Add error feedback
     *
     * This is a specific 