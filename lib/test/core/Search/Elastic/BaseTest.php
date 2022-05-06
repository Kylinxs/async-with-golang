<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

class Search_Elastic_BaseTest extends Search_Index_BaseTest
{
    protected function setUp(): void
    {
        static $count = 0;

        $elasticSearchHost = empty(getenv('ELASTICSEARCH_HOST')) ? 'localhost' : getenv('ELASTICSEARCH_HOST');
        $connection = new Search_Elastic_Connection('http://' . $elasticSearchHost . ':9200');

        $status = $connection->getStatus();
        if (! $status->ok) {
            $this->markTestSkipped('Elasticsearch needs to be available on ' . $elasticSearchHost . ':9200 for the test to run.');
        }

        $this->index = new Search_Elastic_Index($connection, 'test_index');
        $this->index->destroy();

        $this->populate($this->index);
    }

    protected function tearDown(): void
    {
        if ($this->index) {
            $this->index->destroy();
        }
    }

    public function testIndexProvidesHighlightHelper()
    {
        $query = new Search_Query('foobar or bonjour');
        $resultSet = $query->search($this->index);

        $plugin = new Search_Formatter_Plugin_WikiTemplate('{display name=highlight}');
        $formatter = new Search_Formatter($plugin);
        $output = $formatter->format($resultSet);

        $this->assertRegExp('/<em.*>Bonjour<\/em>/', $output);
        $this->assertStringNotContainsString('<body>', $output);
    }
}
