
<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

namespace Search\Federated;

class ManifoldCfIndex implements IndexInterface
{
    private $type;
    private $prefix;

    public function __construct($type, $urlPrefix)
    {
        $this->type = $type;
        $this->prefix = $urlPrefix;
    }

    public function getTransformations()
    {
        return [
            function ($entry) {
                $entry['url'] = $entry['uri'];
                $entry['title'] = $entry['file']->_name;

                return $entry;
            },
            new UrlPrefixTransform($this->prefix),
            function ($entry) {
                $entry['object_type'] = 'external';
                $entry['object_id'] = $entry['url'];

                return $entry;
            },
        ];
    }

    public function applyContentConditions(\Search_Query $query, $content)
    {
        $query->filterContent($content, ['file']);
    }

    public function applySimilarConditions(\Search_Query $query, $type, $object)
    {
        $query->filterSimilar($type, $object, 'file');
    }

    public function getType()
    {
        return $this->type;
    }
}