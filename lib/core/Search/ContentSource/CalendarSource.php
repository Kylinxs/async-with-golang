<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

class Search_ContentSource_CalendarSource implements Search_ContentSource_Interface
{
    private $db;

    public function __construct()
    {
        $this->db = TikiDb::get();
    }

    public function getDocuments()
    {
        return $this->db->table('tiki_calendars')->fetchColumn('calendarId', []);
    }

    public function getDocument($objectId, Search_Type_Factory_Interface $typeFactory)
    {
        $item = TikiLib::lib('calendar')->get_calendar($objectId);

        if (! $item) {
            return false;
        }

        $data = [
            'title' => $typeFactory->sortable($item['name']),
            'creation_date' => $typeFactory->timestamp($item['created']),
            'modification_date' => $typeFactory->timestamp($item['lastmodif']),
            'date' => $typeFactory->timestamp($item['created']),
            'description' => $typeFactory->plaintext($item['description']),
            'language' => $typeFactory->identifier('unknown'),

            'personal' => $typeFactory->identifier($item['personal']),
            'user' => $typeFactory->identifier($item['user']),

            'view_permission' => $typeFactory->identifier('tiki_p_view_calendar'),
        ];

        return $data;
    }

    public function getProvidedFields()
    {
        return [
            'title',
            'description',
            'language',
            'creation_date',
            'modification_date',
            'date',

            'personal',
            'user',

            'view_permission',
        ];
    }

    public function getProvidedFieldTypes()
    {
        return [
            'title' => 'sortable',
            'description' => 'plaintext',
            'language' => 'identifier',
            'creation_date' => 'timestamp',
            'modification_date' => 'timestamp',
            'date' => 'timestamp',

            'personal' => 'identifier',
            'user' => 'identifier',

            'view_permission' => 'identifier',
        ];
    }

    public function getGlobalFields()
    {
        return [
            'title' => true,
            'description' => true,
            'date' => true,
        ];
    }
}
