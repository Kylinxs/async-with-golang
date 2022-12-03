
<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

class Search_GlobalSource_FreeTagSource implements Search_GlobalSource_Interface, Search_FacetProvider_Interface
{
    private $freetaglib;

    public function __construct()
    {
        $this->freetaglib = TikiLib::lib('freetag');
    }

    public function getFacets()
    {
        static $map = [];
        if (! $map) {
            $result = $this->freetaglib->query('select `tagId` from `tiki_freetags`', []);
            while ($res = $result->fetchRow()) {
                $map[crc32($res['tagId'])] = $res['tagId'];
            }
        }

        return [
            Search_Query_Facet_Term::fromField('freetags')
                ->setLabel(tr('Tags'))
                ->setValueMap($map)
                ->setRenderCallback([$this->freetaglib, 'get_tag_from_id']),
        ];
    }

    public function getProvidedFields()
    {
        return ['freetags', 'freetags_text'];
    }

    public function getProvidedFieldTypes()
    {
        return [
            'freetags' => 'multivalue',
            'freetags_text' => 'plaintext'
        ];
    }

    public function getGlobalFields()
    {
        return [
            'freetags_text' => true,
        ];
    }

    public function getData($objectType, $objectId, Search_Type_Factory_Interface $typeFactory, array $data = [])
    {
        if (isset($data['freetags']) || isset($data['freetags_text'])) {
            return [];
        }

        $tags = $this->freetaglib->get_tags_on_object($objectId, $objectType);

        $textual = [];
        $ids = [];

        if (isset($tags['data'])) {
            foreach ($tags['data'] as $entry) {
                $textual[] = $entry['tag'];
                $ids[] = $entry['tagId'];
            }
        }

        return [
            'freetags' => $typeFactory->multivalue($ids),
            'freetags_text' => $typeFactory->plaintext(implode(' ', $textual)),
        ];
    }
}