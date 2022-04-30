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
        $this->addPart(new Search_Expr_Token($query), 'identifier', $field);
    }

    public function filterType($types)
    {
        if (is_array($types)) {
            foreach ($types as $type) {
                if ($type) {
                    $tokens[] = new Search_Expr_Token($type);
                }
            }
            if (isset($tokens)) {
                $or = new Search_Expr_Or($tokens);
                $this->addPart($or, 'identifier', 'object_type');
            }
        } elseif ($types) {
            $token = new Search_Expr_Token($types);
            $this->addPart($token, 'identifier', 'object_type');
        }
    }

    public function filterMultivalue($query, $field)
    {
        $this->addPart($query, 'multivalue', $field);
    }

    public function filterContributors($query)
    {
        $this->filterMultivalue($query, 'contributors');
    }

    public function filterCategory($query, $deep = false)
    {
        $this->filterMultivalue($query, $deep ? 'deep_categories' : 'categories');
    }

    public function filterTags($query)
    {
        $this->filterMultivalue($query, 'freetags');
    }

    public function filterLanguage($query)
    {
        $this->addPart($query, 'identifier', 'language');
    }

    public function filterPermissions(array $groups, $user = null)
    {
        $tokens = [];
        foreach ($groups as $group) {
            $tokens[] = new Search_Expr_Token($group);
        }

        $or = new Search_Expr_Or($tokens);

        if ($user) {
            $sub = $this->getSubQuery('permissions');
            $sub->filterMultivalue($or, 'allowed_groups');
            $sub->filterMultivalue(new Search_Expr_Token($user), 'allowed_users');
        } else {
            $this->addPart($or, 'multivalue', 'allowed_groups');
        }

        $this->applyTransform(new Search_Formatter_Transform_FieldPermissionEnforcer());
    }

    /**
     * Sets up Laminas search term for a date range
     *
     * @param string    $from date - a unix timestamp or most date strings such as 'now', '2011-11-21', 'last week' etc
     * @param string    $to date as with $from (other examples: '-42 days', 'last tuesday')
     * @param string    $field to search in such as 'tracker_field_42'. default: modification_date
     * @param boolean   $allow_empty - also include records with empty value for this field
     * @link            http://www.php.net/manual/en/datetime.formats.php
     * @return void
     */
    public function filterRange($from, $to, $field = 'modification_date', $allow_empty = false)
    {
        if (! is_numeric($from) && $from !== "") {
            $from2 = strtotime($from);
            if ($from2) {
                $from = $from2;
            } else {
                Feedback::error(tra('filterRange: "from" value not parsed'));
            }
        }
        if (! is_numeric($to)) {
            $to2 = strtotime($to);
            if ($to2) {
                $to = $to2;
            } else {
                Feedback::error(tra('filterRange: "to" value not parsed'));
            }
        }

        /* make the range filter work regardless of ordering - if from > to, swap */
        if (is_numeric($from) && is_numeric($to) && $to < $from) {
            $temp = $to;
            $to = $from;
            $from = $temp;
        }
        if ($allow_empty) {
            $parts = array_map(function ($field) use ($from, $to) {
                    return new Search_Expr_Range($from, $to, 'timestamp', $field);
            }, explode(',', $field));
            $parts[] = new Search_Expr_And(
                array_map(function ($field) {
                    return new Search_Expr_Token('', 'timestamp', $field);
                }, explode(',', $field))
            );
            $sub = $this->getSubQuery('range' . $field);
            $sub->getExpr()->addPart(new Search_Expr_Or($parts));
        } else {
            $this->addPart(new Search_Expr_Range($from, $to), 'timestamp', $field);
        }
    }

    public function filterTextRange($from, $to, $field = 'title')
    {
        /* make the range filter work regardless of ordering - if from > to, swap */
        if (strcmp($from, $to) > 0) {
            $temp = $to;
            $to = $from;
            $from = $temp;
        }
        $this->addPart(new Search_Expr_Range($from, $to), 'plaintext', $field);
    }

    public function filterNumericRange($from, $to, $field)
    {
        /* make the range filter work regardless of ordering - if from > to, swap */
        if ($to < $from) {
            $temp = $to;
            $to = $from;
            $from = $temp;
        }
        $this->addPart(new Search_Expr_Range($from, $to), 'numeric', $field);
    }

    public function filterInitial($initial, $field = 'title')
    {
        $this->addPart(new Search_Expr_Initial($initial), 'plaintext', $field);
    }

    public function filterNotInitial($initial, $field = 'title')
    {
        $this->addPart(new Search_Expr_Not(new Search_Expr_Initial($initial)), 'plaintext', $field);
    }

    public function filterRelation($query, array $invertable = [])
    {
        $query = $this->parse($query);
        $replacer = new Search_Query_RelationReplacer($invertable);
        $query = $query->walk([$replacer, 'visit']);
        $this->addPart($query, 'multivalue', 'relations');
    }

    public function filterSimilar($type, $object, $field = 'contents')
    {
        $part = new Search_Expr_And(
            [
                new Search_Expr_Not(
                    new Search_Expr_And(
                        [
                            new Search_Expr_Token($type, 'identifier', 'object_type'),
                            new Search_Expr_Token($object, 'identifier', 'object_id'),
                        ]
                    )
                ),
                $mlt = new Search_Expr_MoreLikeThis($type, $object),
            ]
        );
        $mlt->setField($field);
        $this->expr->addPart($part);
    }

    public function filterSimilarToThese($objects, $content, $field = 'contents')
    {
        $excluded = [];
        foreach ($objects as $object) {
            $excluded[] = new Search_Expr_And(
                [
                    new Search_Expr_Token($object['object_type'], 'identifier', 'object_type'),
                    new Search_Expr_Token($object['object_id'], 'identifier', 'object_id'),
                ]
            );
        }

        $mlt = new Search_Expr_MoreLikeThis($content);
        $mlt->setField($field);

        $part = new Search_Expr_And(
            [
                $mlt,
                new Search_Expr_Not(new Search_Expr_Or($excluded)),
            ]
        );
        $this->expr->addPart($part);
    }

    public function filterDistance($distance, $lat, $lon, $field = 'geo_point')
    {
        $this->addPart(new Search_Expr_Distance($distance, $lat, $lon), 'geo_distance', $field);
    }

    private function addPart($query, $type, $field)
    {
        if (is_string($field)) {
            $field = explode(',', $field);
        }

        $parts = [];
        foreach ((array) $field as $f) {
            $part = $this->parse($query);
            $part->setType($type);
            $part->setField($f);
            $parts[] = $part;
        }

        if (count($parts) === 1) {
            $this->expr->addPart($parts[0]);
        } else {
            $this->expr->addPart(new Search_Expr_Or($parts));
        }
    }

    public function setOrder($order)
    {
        if (is_string($order)) {
            $this->sortOrder = Search_Query_Order::parse($order);
        } else {
            $this->sortOrder = $order;
        }
    }

    public function setRange($start, $count = null)
    {
        $this->start = (int) $start;

        if ($count) {
            $this->count = (int) $count;
        }
    }

    public function setCount($count = null)
    {
        if ($count) {
            $this->count = (int) $count;
        }
    }

    /**
     * Affects the range from a numeric value
     * @param $pageNumber int Page number from 1 to n
     */
    public function setPage($pageNumber)
    {
        $pageNumber = max(1, (int) $pageNumber);
        $this->setRange(($pageNumber - 1) * $this->count);
    }

    public function setWeightCalculator(Search_Query_WeightCalculator_Interface $calculator)
    {
        $this->weightCalculator = $calculator;
    }

    public function getSortOrder()
    {
        if ($this->sortOrder) {
            return $this->sortOrder;
        } else {
            return Search_Query_Order::getDefault();
        }
    }

    /**
     * @param Search_Index_Interface $index
     * @param string $multisearchId : When provided, it means that the current query is to be added to an
     * Elasticsearch Multisearch query, rather than executed as a single query search. Triggering of the Multisearch
     * query is done though the Index object.
     * @param Search_Elastic_ResultSet $resultFromMultisearch : When provided, it means that this is one of the sub-results from an
     * Elasticsearch Multisearch query, and so it just has to be processed as if a result had come back in the case
     * of a single query.
     * @return Search_ResultSet
     */
    public function search(Search_Index_Interface $index, $multisearchId = '', $resultFromMultisearch = '')
    {
        $this->finalize();
        try {
            $resultset = $index->find($this, $this->start, $this->count, $multisearchId, $resultFromMultisearch);
        } catch (Search_Elastic_SortException $e) {
            //on sort exception, try again without the sort field
            $this->sortOrder = null;
            $resultset = $index->find($this, $this->start, $this->count);
        } catch (Exception $e) {
            if (empty($e->suppress_feedback)) {
                Feedback::error(tr('Malformed search query:') . ' ' . $e->getMessage());
                trigger_error($e->getMessage(), E_USER_WARNING);
            }
            return Search_ResultSet::create([]);
        }

        if ($multisearchId > '') {
            // This individual query would already be added to a Multisearch in the Index object and there would be
            // no results to deal with until the Multisearch is triggered later
            return;
        }

        $resultset->applyTransform(function ($entry) {
            if (! isset($entry['_index']) || ! isset($this->foreignQueries[$entry['_index']])) {
                foreach ($this->transformations as $trans) {
                    if (is_callable($trans)) {
                        $entry = $trans($entry);
                    }
                }
            }

            return $entry;
        });

        foreach ($this->foreignQueries as $indexName => $query) {
            $resultset->applyTransform(function ($entry) use ($query, $indexName) {
                if (isset($entry['_index']) && $entry['_index'] == $indexName) {
                    foreach ($query->transformations as $trans) {
                        if (is_callable($trans)) {
                            $entry = $trans($entry);
                        }
                    }
                }

                return $entry;
            });
        }

        $resultset = $this->processReturnOnlyResultsFromList($resultset);

        return $resultset;
    }

    public function scroll($index)
    {
        $this->finalize();

        try {
            $res = $index->scroll($this);
            foreach ($res as $row) {
                foreach ($this->transformations as $trans) {
                    if (is_callable($trans)) {
                        $row = $trans($row);
                    }
                }
                yield $row;
            }
        } catch (Exception $e) {
            Feedback::error(tr('Malformed search query:') . ' ' . $e->getMessage());
            trigger_error($e->getMessage(), E_USER_WARNING);
        }
    }

    public function applyTransform(callable $transform)
    {
        $this->transformations[] = $transform;
    }

    public function store($name, $index)
    {
        if ($index instanceof Search_Index_QueryRepository) {
            $this->finalize();
            $index->store($name, $this->expr);
            return true;
        }

        return false;
    }

    private function finalize()
    {
        if ($this->weightCalculator) {
            $this->expr->walk([$this->weightCalculator, 'calculate']);

            if ($this->postFilter) {
                $this->postFilter->expr->walk([$this->weightCalculator, 'calculate']);
            }

            foreach ($this->foreignQueries as $query) {
                $query->expr->walk([$this->weightCalculator, 'calculate']);
            }
        }

        if ($this->identifierFields) {
            $fields = $this->identifierFields;
            $this->expr->walk(
                function (Search_Expr_Interface $expr) use ($fields) {
                    if (method_exists($expr, 'getField') && in_array($expr->getField(), $fields)) {
                        $expr->setType('identifier');
                    }
                }
            );

            if ($this->postFilter) {
                $this->postFilter->expr->walk(
                    function (Search_Expr_Interface $expr) use ($fields) {
                        if (method_exists($expr, 'getField') && in_array($expr->getField(), $fields)) {
                            $expr->setType('identifier');
                        }
                    }
                );
            }

            foreach ($this->foreignQueries as $query) {
                $query->expr->walk(
                    function (Search_Expr_Interface $expr) use ($fields) {
                        if (method_exists($expr, 'getField') && in_array($expr->getField(), $fields)) {
                            $expr->setType('identifier');
                        }
                    }
                );
            }
        }
    }

    public function getExpr()
    {
        return $this->expr;
    }

    private function parse($query)
    {
        if (is_string($query)) {
            $parser = new Search_Expr_Parser();
            $query = $parser->parse($query);
        } elseif ($query instanceof Search_Expr_Interface) {
            $query = clone $query;
        }

        return $query;
    }

    public function getTerms()
    {
        $terms = [];

        $extractor = new Search_Type_Factory_Direct();

        $this->expr->walk(
            function ($expr) use (&$terms, $extractor) {
                if ($expr instanceof Search_Expr_Token && $expr->getField() == 'contents') {
                    $terms[] = $expr->getValue($extractor)->getValue();
                }
            }
        );

        return $terms;
    }

    public function getSubQuery($name)
    {
        if (empty($name)) {
            return $this;
        }

        if (! isset($this->subQueries[$name])) {
            $subquery = new self();
            $subquery->expr = new Search_Expr_Or([]);
            $this->expr->addPart($subquery->expr);

            $this->subQueries[$name] = $subquery;
        }

        return $this->subQueries[$name];
    }

    public function getPostFilter()
    {
        if (! $this->postFilter) {
            $subquery = new self();
            $this->postFilter = $subquery;
            $subquery->postFilter = $subquery;
        }

        return $this->postFilter;
    }

    public function requestFacet(Search_Query_Facet_Interface $facet)
    {
        $this->facets[] = $facet;
    }

    public function getFacets()
    {
        return $this->facets;
    }

    public function includeForeign($indexName, Search_Query $query)
    {
        $this->foreignQueries[$indexName] = $query;
    }

    public function getForeignQueries()
    {
        return $this->foreignQueries;
    }

    /**
     * Set list of results to return
     *
     * @param array $returnOnlyResultList
     * @return void
     */
    public function setReturnOnlyResultList($returnOnlyResultList)
    {
        $this->returnOnlyResultList = $returnOnlyResultList;
    }

    /**
     * Get list of results to return
     *
     * @return array
     */
    public function getReturnOnlyResultList()
    {
        return $this->returnOnlyResultList;
    }

    /**
     * Filter the results of the query
     *
     * @param Search_ResultSet $resultSet
     * @return Search_ResultSet
     */
    protected function processReturnOnlyResultsFromList($resultSet)
    {
        if (! empty($this->getReturnOnlyResultList())) {
            $tmpResults = [];
            foreach ($this->getReturnOnlyResultList() as $resultPosition) {
                $arrayKey = $resultPosition - 1;
                if (isset($resultSet[$arrayKey])) {
                    $tmpResults[] = $resultSet[$arrayKey];
                }
            }
            $resultSet = Search_ResultSet::create($tmpResults);
        }
        return $resultSet;
    }
}
