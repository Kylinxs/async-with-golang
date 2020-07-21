<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

/**
 * DCSLib
 *
 * @uses TikiLib
 */
class DCSLib extends TikiLib
{
    /**
     * @param $result
     * @param null $lang
     * @return mixed
     */
    private function convert_results($result, $lang = null)
    {
        foreach ($result as &$row) {
            $row['page_name'] = '';

            if ($row['content_type'] == 'page' && substr($row['data'], 0, 5) == 'page:') {
                $row['page_name'] = substr($row['data'], 5);

                $row['data'] = $this->g