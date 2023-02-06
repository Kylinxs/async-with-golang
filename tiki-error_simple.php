
<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.

if (basename($_SERVER['SCRIPT_NAME']) === basename(__FILE__)) {
    // this file now should be included not redirected to
    header('location: index.php');
}