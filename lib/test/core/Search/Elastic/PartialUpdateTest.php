<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

class Search_Elastic_PartialUpdateTest extends Search_Index_PartialUpdateTest
{
    protected $index;

    protected function setUp(): void
    {
        $this->index = $this->getIndex();
        $this->index->destroy();
    }

    protected function getIndex()
    {
        $elasticSearchHost = empty(getenv('ELASTICSEARCH_HOST')) ? 'localhost' : getenv('ELASTICSEARCH_HOST');
        $connection = new Search_Elastic_Connection('http://' . $elasticSearchHost . ':9200');

        $status = $connection->getStatus();
        if (! $status->ok) {
            $this->markTestSkipped('Elasticsearch needs to be available on ' . $elasticSearchHost . ':9200 for the test to run.');
        }

        return new Search_Elastic_Index($connection, 'test_index');
    }

    protected function tearDown(): void
    {
        if ($this->index) {
            $this->index->destroy();
        }
    }
}
