
<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

class Search_MySql_Index implements Search_Index_Interface
{
    private $db;
    private $table;
    private $builder;
    private $tfTranslator;
    private $index_name;

    public function __construct(TikiDb $db, $index)
    {
        $this->db = $db;
        $this->index_name = $index;
        $this->table = new Search_MySql_Table($db, $index);
        $this->builder = new Search_MySql_QueryBuilder($db);
        $this->tfTranslator = new Search_MySql_TrackerFieldTranslator();
    }

    public function destroy()
    {
        $this->table->drop();
        return true;
    }

    public function exists()
    {
        return $this->table->exists();
    }

    public function addDocument(array $data)
    {
        foreach ($data as $key => $value) {
            $this->handleField($key, $value);
        }

        $data = array_map(
            function ($entry) {
                return $this->getValue($entry);
            },
            $data
        );

        $this->table->insert($data);
    }

    private function getValue(Search_Type_Interface $data)
    {
        $value = $data->getValue();

        if (
            ($data instanceof Search_Type_Whole
                || $data instanceof Search_Type_PlainShortText
                || $data instanceof Search_Type_PlainText
                || $data instanceof Search_Type_MultivalueText)
            && strlen($value) >= 65535
        ) {
            $value = function_exists('mb_strcut') ?
                mb_strcut($value, 0, 65535) : substr($value, 0, 65535);
        }

        return $value;
    }

    private function handleField($name, $value)
    {
        if ($value instanceof Search_Type_Whole) {
            $this->table->ensureHasField($name, 'TEXT');
        } elseif ($value instanceof Search_Type_Numeric) {
            $this->table->ensureHasField($name, 'FLOAT');
        } elseif ($value instanceof Search_Type_PlainShortText) {
            $this->table->ensureHasField($name, 'TEXT');
        } elseif ($value instanceof Search_Type_PlainText) {
            $this->table->ensureHasField($name, 'TEXT');
        } elseif ($value instanceof Search_Type_PlainMediumText) {
            $this->table->ensureHasField($name, 'MEDIUMTEXT');
        } elseif ($value instanceof Search_Type_WikiText) {
            $this->table->ensureHasField($name, 'MEDIUMTEXT');
        } elseif ($value instanceof Search_Type_MultivalueText) {
            $this->table->ensureHasField($name, 'TEXT');
        } elseif ($value instanceof Search_Type_Timestamp) {
            $this->table->ensureHasField($name, $value->isDateOnly() ? 'DATE' : 'DATETIME');
        } else {
            throw new Exception('Unsupported type: ' . get_class($value));
        }
    }

    public function endUpdate()
    {
        $this->table->flush();
    }

    public function optimize()
    {
        $this->table->flush();
    }

    public function invalidateMultiple(array $objectList)
    {
        foreach ($objectList as $object) {
            $this->table->deleteMultiple($object);
        }
    }

    public function find(Search_Query_Interface $query, $resultStart, $resultCount)
    {
        try {
            $words = $this->getWords($query->getExpr());

            $condition = $this->builder->build($query->getExpr());
            $conditions = empty($condition) ? [] : [
                $this->table->expr($condition),
            ];

            $scoreFields = [];
            $indexes = $this->builder->getRequiredIndexes();
            foreach ($indexes as $index) {
                $this->table->ensureHasIndex($index['field'], $index['type']);

                if (! in_array($index, $scoreFields) && $index['type'] == 'fulltext') {
                    $scoreFields[] = $index;
                }
            }

            $this->table->flush();

            $order = $this->getOrderClause($query, (bool) $scoreFields);

            $selectFields = $this->table->all();

            if ($scoreFields) {
                $str = $this->db->qstr(implode(' ', $words));
                $scoreCalc = '';
                foreach ($scoreFields as $field) {
                    $scoreCalc .= $scoreCalc ? ' + ' : '';
                    $scoreCalc .= "ROUND(MATCH(`{$this->tfTranslator->shortenize($field['field'])}`) AGAINST ($str),2) * {$field['weight']}";
                }
                $selectFields['score'] = $this->table->expr($scoreCalc);
            }
            $count = $this->table->fetchCount($conditions);
            $entries = $this->table->fetchAll($selectFields, $conditions, $resultCount, $resultStart, $order);

            foreach ($entries as &$entry) {
                foreach ($entry as $key => $val) {
                    $normalized = $this->tfTranslator->normalize($key);
                    if ($normalized != $key) {
                        $entry[$normalized] = $val;
                        unset($entry[$key]);
                    }
                }
            }

            $resultSet = new Search_ResultSet($entries, $count, $resultStart, $resultCount);
            $resultSet->setHighlightHelper(new Search_MySql_HighlightHelper($words));

            return $resultSet;
        } catch (Search_MySql_QueryException $e) {
            if (empty($e->suppress_feedback)) {
                Feedback::error($e->getMessage());
            }
            $resultSet = new Search_ResultSet([], 0, $resultStart, $resultCount);
            $resultSet->errorInQuery = true;
            return $resultSet;
        }
    }

    public function scroll(Search_Query_Interface $query)
    {
        $perPage = 100;
        $hasMore = true;

        for ($from = 0; $hasMore; $from += $perPage) {
            $result = $this->find($query, $from, $perPage);
            foreach ($result as $row) {
                yield $row;
            }

            $hasMore = $result->hasMore();
        }
    }

    private function getOrderClause($query, $useScore)
    {
        $order = $query->getSortOrder();

        if ($order->getField() == Search_Query_Order::FIELD_SCORE) {
            if ($useScore) {
                return ['score' => 'DESC'];
            } else {
                return; // No specific order
            }
        }

        $this->table->ensureHasIndex($order->getField(), 'sort');

        if ($order->getMode() == Search_Query_Order::MODE_NUMERIC) {
            return $this->table->expr("CAST(`{$this->tfTranslator->shortenize($order->getField())}` as SIGNED) {$order->getOrder()}");
        } else {
            return $this->table->expr("`{$this->tfTranslator->shortenize($order->getField())}` {$order->getOrder()}");
        }
    }

    private function getWords($expr)
    {
        $words = [];
        $factory = new Search_Type_Factory_Direct();
        $expr->walk(
            function ($node) use (&$words, $factory) {
                if ($node instanceof Search_Expr_Token && $node->getField() !== 'searchable') {
                    $word = $node->getValue($factory)->getValue();
                    if (is_string($word) && ! in_array($word, $words)) {
                        $words[] = $word;
                    }
                }
            }
        );

        return $words;
    }

    public function getTypeFactory()
    {
        return new Search_MySql_TypeFactory();
    }

    public function getFieldsCount()
    {
        return count($this->db->fetchAll("show columns from `{$this->index_name}`"));
    }

    /**
     * Function responsible for restoring old indexes
     * @param $indexesToRestore
     * @param $currentIndexTableName
     * @throws Exception
     */
    public function restoreOldIndexes($indexesToRestore, $currentIndexTableName)
    {
        $columns = array_column(TikiDb::get()->fetchAll("SHOW COLUMNS FROM $currentIndexTableName"), 'Field');

        foreach ($indexesToRestore as $indexToRestore) {
            if (! in_array($indexToRestore['Column_name'], $columns)) {
                continue;
            }

            $indexType = strtolower($indexToRestore['Index_type']) == 'fulltext' ? 'fulltext' : 'index';

            try {
                $this->table->ensureHasIndex($indexToRestore['Column_name'], $indexType);
            } catch (Search_MySql_QueryException $exception) {
                // Left blank on purpose
            }
        }
    }
}