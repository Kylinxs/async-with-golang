<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

class Search_Query implements Search_Query_Interface
{
    private $objectList;
    private $expr;
    private $sortOrder;
    private $start = 0;
    private $count = 50;
    private $weightCalculator = null;
    private $identifierFields = null;

    private $postFilter;
    private $subQueries = [];
    private $facets = [];
    private $foreignQueries = [];
    private $transformations = [];
    private $returnOnlyResultList = [];

    public function __construct($query = null, $expr = 'and')
    {
        if ($expr === 'or') {
            $this->expr = new Search_Expr_Or([]);
        } else {
            $this->expr = new Search_Expr_And([]);
        }

        if ($query) {
            $this->filterContent($query);
        }
    }

    public function __clone()
    {
        $this->expr = clone $this->expr;
    }

    public function setIdentifierFields(array $fields)
    {
        $this->identifierFields = $fields;
    }

    public function addObject($type, $objectId)
    {
        if (is_null($this->objectList)) {
            $this->objectList = new Search_Expr_Or([]);
            $this->expr->addPart($this->objectList);
        }

        $type = new Search_Expr_Token($type, 'identifier', 'object_type');
        $objectId = new Search_Expr_Token($objectId, 'identifier', 'object_id');

        $this->objectList->addPart(new Search_Expr_And([$type, $objectId]));
    }

    public function filterContent($query, $field = 'contents')
    {
        global $prefs;
        if ($prefs['unified_search_default_operator'] == 1 && is_string($query) && strpos($query, '*') !== false) {
            // Wildcard queries with spaces need to be OR otherwise "*foo bar*" won't match "foo bar" if set to AND.
            $query = preg_replace('/\s+/', '* *', trim($query));
            $query = str_replace(['*AND*', '*OR*', '**'], ['', 'OR', '*'], $query);
        }
        $this->addPart($query, 'plaintext', $field);
    }

    public function filterIdentifier($query, $field)
    {
        $this