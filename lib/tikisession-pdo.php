<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

/* from
        http://www.spiration.co.uk/post/1333/PHP 5 sessions in mysql database with PDO db objects
*/

/**
 *
 */
class Session
{
    public $db;

    public function __destruct()
    {
     