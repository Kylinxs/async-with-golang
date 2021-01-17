
<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

class Tiki_Profile_InstallHandler_PluginAlias extends Tiki_Profile_InstallHandler
{
    public function getData()
    {
        if ($this->data) {
            return $this->data;
        }

        $defaults = [
            'body' => [
                'input' => 'ignore',
                'default' => '',
                'params' => []
            ],
            'params' => [
            ],
        ];

        $data = array_merge($defaults, $this->obj->getData());

        return $this->data = $data;
    }

    public function canInstall()
    {
        $data = $this->getData();

        if (! isset($data['name'], $data['implementation'], $data['description'])) {
            return false;
        }

        if (! is_array($data['description']) || ! is_array($data['body']) || ! is_array($data['params'])) {
            return false;
        }

        return true;
    }

    public function doInstall()
    {
        global $tikilib;
        $data = $this->getData();

        $this->replaceReferences($data);

        $name = $data['name'];
        unset($data['name']);

        $parserlib = TikiLib::lib('parser');
        $parserlib->plugin_alias_store($name, $data);

        return $name;
    }

    /**
     * Remove plugin alias
     *
     * @param string $pluginAlias
     * @return bool
     */
    public function remove($pluginAlias)
    {
        if (! empty($pluginAlias)) {
            $parserlib = TikiLib::lib('parser');
            if ($parserlib->plugin_alias_delete($pluginAlias)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get current plugin alias data
     *
     * @param array $pluginAlias
     * @return mixed
     */
    public function getCurrentData($pluginAlias)
    {
        $pluginAliasName = ! empty($pluginAlias['name']) ? $pluginAlias['name'] : '';
        if (! empty($pluginAliasName)) {
            $parserlib = TikiLib::lib('parser');
            $pluginNameData = $parserlib->plugin_alias_info($pluginAliasName);
            if (! empty($pluginNameData)) {
                return $pluginNameData;
            }
        }
        return false;
    }
}