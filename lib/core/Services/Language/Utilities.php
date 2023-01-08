
<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$
// Utility functions for language, translation related controllers

class Services_Language_Utilities
{
    /**
     * Attach (link) translation source object to target object (eg: relation between two wiki pages that are translations of each other)
     *
     * @param string $type Tiki object type, eg: wiki page
     * @param int $source The id of an instance of the object type, eg: a wiki page id
     * @param int $target The id of an instance of the object type, eg: a wiki page id
     *
     * @return
     */
    public function insertTranslation($type, $source, $target)
    {
        $multilinguallib = TikiLib::lib('multilingual');
        $sourceLang = $this->getLanguage($type, $source);
        $sourceId = $this->toInternalId($type, $source);

        $targetLang = $this->getLanguage($type, $target);
        $targetId = $this->toInternalId($type, $target);

        $out = $multilinguallib->insertTranslation($type, $sourceId, $sourceLang, $targetId, $targetLang);

        return ! $out;
    }

    /**
     * Detach (unlink) translation source object from target object (eg: relation between two wiki pages that are translations of each other)
     *
     * @param string $type Tiki object type, eg: wiki page
     * @param int $source The id of an instance of the object type, eg: a wiki page id
     * @param int $target The id of an instance of the object type, eg: a wiki page id
     *
     * @return
     */
    public function detachTranslation($type, $source, $target)
    {
        $multilinguallib = TikiLib::lib('multilingual');
        $targetId = $this->toInternalId($type, $target);

        $multilinguallib->detachTranslation($type, $targetId);
    }

    /**
     * Get translations of an object
     *
     * @param string $type Tiki object type, eg: wiki page
     * @param int $object The id of an instance of the object type, eg: a wiki page id
     *
     * @return array List of language codes, eg: en, hu, de, etc
     */
    public function getTranslations($type, $object)
    {
        $multilinguallib = TikiLib::lib('multilingual');
        $langLib = TikiLib::lib('language');

        $objId = $this->toInternalId($type, $object);

        $translations = $multilinguallib->getTrads($type, $objId);
        $languages = $langLib->get_language_map();

        foreach ($translations as & $trans) {
            $trans['objId'] = $this->toExternalId($type, $trans['objId']);
            $trans['language'] = $languages[$trans['lang']];
        }

        return $translations;
    }

    /**
     * Get language for an object
     *
     * @param string $type Tiki object type, eg: wiki page
     * @param int $object The id of an instance of the object type, eg: a wiki page id
     *
     * @return string A language code, eg: en
     *
     * @throws Services_Exception
     */
    public function getLanguage($type, $object)