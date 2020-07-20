<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

class Search_ContentSource_FileGallerySource implements Search_ContentSource_Interface
{
    private $db;

    public function __construct()
    {
        $this->db = TikiDb::get();
    }

    public function getDocuments()
    {
        return $this->db->table('tiki_file_galleries')->fetchColumn('galleryId', []);
    }

    public function getDocument($objectId, Search_Type_Factory_Interface $typeFactory)
    {
        $lib = TikiLib::lib('filegal');

        $item = $lib->get_file_gallery_info($objectId);

        if (! $item) {
            return false;
        }

        $data = [
            'title' => $typeFactory->sortable($item['name']),
            'creation_date' => $typeFactory->timestamp($item['created']),
            'modification_date' => $typeFactory->timestamp($item['lastModif']),
            'date' => $typeFactory->timestamp($item['created']),
            'description' => $typeFactory->plaintext($item[