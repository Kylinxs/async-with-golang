<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$
//author : aris002@yahoo.co.uk
require_once('lib/socnets/PrefsGen.php');
use TikiLib\Socnets\PrefsGen\PrefsGen;


/**
* @return array
* the name _socnets_ must correspond this filename.
* Otherwise, you will not get your prefs set!!!
**/
function prefs_socnets_list()
{
    $prefix = substr(basename(__FILE__), 0, -4) . '_';

    $prefs = PrefsGen::getSocPrefs($prefix);
    return $prefs;
}

//to generate a function depending on the file name?
//$funcname = "blah";
//$args = "args"
//$funcname = 'function ' . $funcname . "({$args}) { ... }";
//eval($funcname);
