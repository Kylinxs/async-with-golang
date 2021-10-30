<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

class Search_Formatter_AppendPagination implements Search_Formatter_Plugin_Interface
{
    private $parent;
    private $arguments;

    public function __construct(Search_Formatter_Plugin_Interface $parent, array $arguments = [])
    {
        $this->parent = $parent;
        $this->arguments = $arguments;
    }

    public function getFields()
    {
        return $this->parent->getFields();
    }

    public function getFormat()
    {
        return $this->parent->getFormat();
    }

    public function prepareEntry($entry)
    {
        return $this->parent->prepareEntry($entry);
    }

    public function renderEntries(Search_ResultSet $entries)
    {
        if ($entries->getTsOn()) {
            return $this->parent->renderEntries($entries);
        }
        $smarty = TikiLib::lib('smarty');
        $smarty->loadPlugin('smarty_block_pagination_links');
        $arguments = $this->arguments;
        $arguments['resultset'] = $entries;
        $tmp = false;

        $url = parse_url(@$_SERVER["REQUEST_URI"], PHP_URL_PATH);
        $filters = [];
        foreach ($_GET as $key => $val) {
            $filters[$key] = $val;
        }
        foreach ($_POST as $key => $val) {
            if (substr($key, 0, 3) == 'tf_' || $key == 'tzoffset' || $key == 'tzname') {
                $filters[$key] = $val;
            }
        }
        $url .= '?' . http_build_query($filters);

        $pagination = smarty_block_pagination_links($arguments, $url, $smarty, $tmp);

        if ($this->getFormat() == Search_Formatter_Plugin_Interface::FORMAT_WIKI) {
            $pagination = "~np~$pagination~/np~";
        }
        return $this->parent->renderEntries($entries) . $pagination;
    }
}
