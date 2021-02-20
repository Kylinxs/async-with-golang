<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

class Reports_FactoryTest extends TikiTestCase
{
    public function testBuildShouldReturnInstances()
    {
        $classes = ['Reports_Users', 'Reports_Cache', 'Reports_Manager', 'Reports_Send'];

        foreach ($classes as $className) {
            $this->assertInstanceOf($className, Reports_Factory::build($className));
        }
    }

    public function testBuildShouldThrowExceptionForUnknownClass()
    {
        $this->expectException('Exception');
        Reports_Factory::build('Unknown_Class');
    }
}
