
<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

class Search_Formatter_DataSource_Declarative implements Search_Formatter_DataSource_Interface
{
    private $contentSources = [];
    private $globalSources = [];
    private $prefilter;

    public function addContentSource($type, Search_ContentSource_Interface $contentSource)
    {
        $this->contentSources[$type] = $contentSource;
    }

    public function addGlobalSource(Search_GlobalSource_Interface $globalSource)
    {
        $this->globalSources[] = $globalSource;
    }

    public function getData($entry, $requestedField)
    {
        $type = $entry['object_type'];
        $object = $entry['object_id'];
        $hash = isset($entry['hash']) ? $entry['hash'] : null;
        $missingFields = $this->handlePrefilter([$requestedField], $entry);

        $primaryFields = $this->obtainFromContentSource($type, $object, $hash, $missingFields);

        if (! empty($primaryFields)) {
            return $primaryFields;
        } else {
            // Do not use array merge as the entry may be an object
            foreach ($primaryFields as $key => $value) {
                $entry[$key] = $value;
            }

            foreach ($this->globalSources as $globalSource) {
                $local = $this->obtainFromGlobalSource($globalSource, $type, $object, $missingFields, $entry);
                if (! empty($local)) {
                    return $local;
                }
            }
        }

        return [];
    }

    private function obtainFromContentSource($type, $object, $hash, &$missingFields)
    {
        if (isset($this->contentSources[$type])) {
            $contentSource = $this->contentSources[$type];

            if (in_array('highlight', $missingFields)) {
                $missingFields = array_merge($missingFields, array_keys($contentSource->getGlobalFields()));
            }

            if ($this->sourceProvidesValue($contentSource, $missingFields)) {
                $data = $contentSource->getDocument($object, new Search_Type_Factory_Direct());
                $used = $data;

                if (is_int(key($data)) && ! is_null($hash)) {
                    $used = reset($data);

                    foreach ($data as $entry) {
                        if (isset($entry['hash']) && $entry['hash']->getValue() == $hash) {
                            $used = $entry;
                            break;
                        }
                    }
                } elseif (is_int(key($data))) {
                    $used = reset($data);
                }

                return $this->getRaw($used, $missingFields);
            }
        }

        return [];
    }

    private function obtainFromGlobalSource($globalSource, $type, $object, &$missingFields, $data)
    {
        if (is_array($missingFields)) {
            if (in_array('highlight', $missingFields)) {
                $missingFields = array_merge($missingFields, array_keys($globalSource->getGlobalFields()));
            }
        }

        if ($this->sourceProvidesValue($globalSource, $missingFields)) {
            // The field may exist, but contain mangled data
            foreach ($globalSource->getProvidedFields() as $field) {
                unset($data[$field]);
            }

            $data = $globalSource->getData($type, $object, new Search_Type_Factory_Direct(), $data);

            return $this->getRaw($data, $missingFields);
        }

        return [];
    }

    private function sourceProvidesValue($contentSource, $missingFields)
    {
        return ! empty($missingFields) && count(array_intersect($missingFields, $contentSource->getProvidedFields())) > 0;
    }

    private function getRaw($data, &$missingFields)
    {
        $data = array_intersect_key($data, array_combine($missingFields, $missingFields));

        $missingFields = array_diff($missingFields, array_keys($data));

        $raw = [];
        foreach ($data as $key => $value) {
            $value = $value->getValue();
            if (! empty($value)) {
                $raw[$key] = $value;
            }
        }

        return $raw;
    }

    /**
     * Check if the prefilter has been set
     * @return bool
     */
    public function isPrefilterSet()
    {
        return ! empty($this->prefilter);
    }

    /**
     * Set a filter function to determine the fields to select.
     * First parameter, field list
     * Second parameter, the entry
     */
    public function setPrefilter($callback)
    {
        $this->prefilter = $callback;
    }

    /**
     * Get prefilter closure
     * @return mixed
     */
    public function getPrefilter()
    {
        return $this->prefilter;
    }

    private function handlePrefilter(array $fields, $entry)
    {
        if ($callback = $this->prefilter) {
            return $callback($fields, $entry);
        } else {
            return $fields;
        }
    }
}