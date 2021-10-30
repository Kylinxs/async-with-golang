<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

namespace Tracker\Tabular\Source;

class ODBCSource implements SourceInterface
{
    private $schema;
    private $odbc_manager;
    private $last_import_time;

    public function __construct(\Tracker\Tabular\Schema $schema, array $odbc_config)
    {
        $this->schema = $schema;
        $this->odbc_manager = new \Tracker\Tabular\ODBCManager($odbc_config);
    }

    public function getEntries()
    {
        $definition = $this->schema->getDefinition();
        $modifiedField = $definition->getConfiguration('tabularSyncModifiedField');
        $lastImport = $definition->getConfiguration('tabularSyncLastImport', null);
        if ($modifiedField) {
            $modifiedField = $definition->getField($modifiedField);
        }
        if ($lastImport) {
            $lastImport = gmdate("Y-m-d H:i:s", $lastImport);
        }
        $fields = [];
        foreach ($this->schema->getColumns() as $column) {
            $fields = array_merge($fields, $column->getRemoteFields());
            if ($modifiedField && is_array($modifiedField) && $modifiedField['permName'] == $column->getField()) {
                $modifiedField = $column->getRemoteField();
            }
        }
        $this->last_import_time = time();
        foreach ($this->odbc_manager->iterate($fields, $modifiedField, $lastImport) as $row) {
            yield new ODBCSourceEntry($row);
        }
    }

    public function getSchema()
    {
        return $this->schema;
    }

    public function getRemoteSchema()
    {
        $result = [];
        $schema = $this->odbc_manager->getSchema();
        foreach ($schema as $row) {
            $result[] = [
                'name' => $row['COLUMN_NAME'],
                'type' => $row['TYPE_NAME'],
                'size' => $row['COLUMN_SIZE'],
                'remarks' => $row['REMARKS'],
            ];
        }
        return $result;
    }

    public function importSuccess()
    {
        $definition = $this->schema->getDefinition();
        if ($definition->getConfiguration('tabularSyncModifiedField')) {
            \TikiLib::lib('trk')->replace_tracker_option($definition->getConfiguration('trackerId'), 'tabularSyncLastImport', $this->last_import_time);
        }
    }
}
