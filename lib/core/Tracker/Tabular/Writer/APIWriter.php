
<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

namespace Tracker\Tabular\Writer;

class APIWriter
{
    private $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function write(\Tracker\Tabular\Source\SourceInterface $source)
    {
        $schema = $source->getSchema();
        $schema = $schema->getPlainOutputSchema();
        $schema->validate();

        $columns = $schema->getColumns();

        $succeeded = $failed = $skipped = 0;
        $errors = [];

        $owner_columns = [];
        foreach ($schema->getDefinition()->getItemOwnerFields() as $ownerField) {
            $ownerField = $schema->getDefinition()->getField($ownerField);
            foreach ($columns as $column) {
                if ($ownerField && $column->getField() == $ownerField['permName']) {
                    $owner_columns[] = $column;
                }
            }
        }

        foreach ($source->getEntries() as $entry) {
            $row = [];

            $id = null;
            foreach ($columns as $column) {
                $row[$column->getLabel()] = $entry->render($column, false);
                if ($column->isPrimaryKey()) {
                    $id = $row[$column->getLabel()];
                }
            }

            foreach (explode('.', $this->config['modify_data_path']) as $field) {
                if ($field) {
                    $row = [$field => $row];
                }
            }

            $user = null;
            foreach ($owner_columns as $column) {
                $owners = $entry->raw($column);
                $owners = \TikiLib::lib('trk')->parse_user_field($owners);
                if ($owners) {
                    $user = $owners[0];
                    break;
                }
            }

            try {
                if ($id) {
                    if (! empty($this->config['update_limit'])) {
                        parse_str($this->config['update_limit'], $params);
                        foreach ($params as $field => $value) {
                            if (! empty($value)) {
                                if (isset($row[$field]) && $row[$field] == $value) {
                                    continue;
                                }
                            } else {
                                if (empty($row[$field])) {
                                    continue;
                                }
                            }
                            $skipped++;
                            continue 2;
                        }
                    }
                    if (! empty($this->config['update_url'])) {
                        $url = str_replace('#id', $id, $this->config['update_url']);
                        $client = new \Services_ApiClient($url, false);
                        $client->setContextUser($user);
                        $method = strtolower($this->config['update_method'] ?? 'patch');
                        $formatted_row = $this->formatRow(@$this->config['update_format'], $columns, $row);
                        if (is_string($formatted_row) && @json_decode($formatted_row) !== null) {
                            $result = $client->$method('', $formatted_row, 'application/json');
                        } else {
                            $result = $client->$method('', $formatted_row);
                        }
                    } else {
                        $skipped++;
                        continue;
                    }
                } else {
                    if (! empty($this->config['create_url'])) {
                        $url = $this->config['create_url'];
                        $client = new \Services_ApiClient($url, false);
                        $client->setContextUser($user);
                        $method = strtolower($this->config['create_method'] ?? 'post');
                        $formatted_row = $this->formatRow(@$this->config['create_format'], $columns, $row);
                        if (is_string($formatted_row) && @json_decode($formatted_row) !== null) {
                            $result = $client->$method('', $formatted_row, 'application/json');
                        } else {
                            $result = $client->$method('', $formatted_row);
                        }
                    } else {
                        $skipped++;
                        continue;
                    }
                }
            } catch (\Services_Exception $e) {
                if (! in_array($e->getMessage(), $errors)) {
                    $errors[] = $e->getMessage();
                }
                $result = false;
            }

            if ($result && ! $id && method_exists($entry, 'backfillPK')) {
                foreach (explode('.', $this->config['modify_data_path']) as $field) {
                    if ($field !== '' && $result[$field]) {
                        $result = $result[$field];
                    }
                }
                $pk = null;
                foreach ($columns as $column) {
                    if ($column->isPrimaryKey()) {
                        $pk = $column->getField();
                        $id = $result[$column->getLabel()] ?? null;
                    }
                }
                if ($id && $pk) {
                    $entry->backfillPK($pk, $id);
                }
            }

            if ($result || is_array($result)) {
                $succeeded++;
            } else {
                $failed++;
            }
        }
        return compact('succeeded', 'failed', 'skipped', 'errors');
    }

    private function formatRow($format, $columns, $row)
    {
        if (! empty($format)) {
            $formatted_row = $format;
            foreach ($columns as $column) {
                $formatted_row = str_replace('%' . $column->getLabel() . '%', $row[$column->getLabel()], $formatted_row);
            }
        } else {
            $formatted_row = $row;
        }
        return $formatted_row;
    }
}