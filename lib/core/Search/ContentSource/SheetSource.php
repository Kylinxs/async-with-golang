
<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

class Search_ContentSource_SheetSource implements Search_ContentSource_Interface
{
    private $db;

    public function __construct()
    {
        $this->db = TikiDb::get();
    }

    public function getDocuments()
    {
        return $this->db->table('tiki_sheets')->fetchColumn('sheetId', []);
    }

    public function getDocument($objectId, Search_Type_Factory_Interface $typeFactory)
    {
        $sheetlib = TikiLib::lib('sheet');

        $info = $sheetlib->get_sheet_info($objectId);

        if (! $info) {
            return false;
        }

        $values = $this->db->table('tiki_sheet_values');
        $contributors = $values->fetchColumn(
            $values->expr('DISTINCT `user`'),
            [
                'sheetId' => $objectId,
            ]
        );
        $lastModif = $values->fetchOne(
            $values->max('begin'),
            [
                'sheetId' => $objectId,
            ]
        );

        $loader = new TikiSheetDatabaseHandler($objectId);
        $writer = new TikiSheetCSVHandler('php://output');

        $grid = new TikiSheet();
        $grid->import($loader);

        $grid->export($writer);
        $text = $writer->output;

        $data = [
            'title' => $typeFactory->sortable($info['title']),
            'description' => $typeFactory->sortable($info['description']),
            'modification_date' => $typeFactory->timestamp($lastModif),
            'date' => $typeFactory->timestamp($lastModif),
            'contributors' => $typeFactory->multivalue($contributors),

            'sheet_content' => $typeFactory->plaintext($text),

            'view_permission' => $typeFactory->identifier('tiki_p_view_sheet'),
        ];

        return $data;
    }

    public function getProvidedFields()
    {
        return [
            'title',
            'description',
            'modification_date',
            'date',
            'contributors',

            'sheet_content',

            'view_permission',
        ];
    }

    public function getProvidedFieldTypes()
    {
        return [
            'title' => 'sortable',
            'description' => 'sortable',
            'modification_date' => 'timestamp',
            'date' => 'timestamp',
            'contributors' => 'multivalue',

            'sheet_content' => 'plaintext',

            'view_permission' => 'identifier',
        ];
    }

    public function getGlobalFields()
    {
        return [
            'title' => true,
            'description' => true,
            'date' => true,

            'sheet_content' => false,
        ];
    }
}