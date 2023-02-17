<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$


class Search_Formatter_ValueFormatter_Slug extends Search_Formatter_ValueFormatter_Abstract
{
    private $manager = null;

    public function __construct($arguments)
    {
        $this->manager = TikiLib::lib('slugmanager');
    }

    public function render($name, $value, array $entry)
    {
        global $prefs;

        $slug = $this->manager->generate($prefs['wiki_url_scheme'], $value, $prefs['url_only_ascii'] === 'y');

        return $slug;
    }
}
