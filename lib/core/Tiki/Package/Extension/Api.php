<?php

namespace Tiki\Package\Extension;

use Tiki\Package\ExtensionManager;

class Api extends Utilities
{
    protected static $objects = [];

    private function loadObjects($folder)
    {
        if (strpos($folder, '/') !== false && strpos($folder, '_') === false) {
            $package = $folder;
        } else {
            $package = str_replace('_', '/', $folder);
        }

        if (! ExtensionManager::isExtensionEnabled($package)) {
            return [];
        }

        $extension = ExtensionManager::get($package);

        $ret = [];
        $table = $this->table('tiki_profile_symbols');

        $domain = 'file://' . $extension->getPath() . '/profiles';

        $all_info = $table->fetchAll(
            ['object', 'type', 'value'],
            ['domain' => $domain]
        );

        foreach ($all_info as $v) {
            $ret[$v['object']] = ['type' => $v['type'], 'id' => $v['value']];
        }

        self::$objects[$folder] = $ret;

        return $ret;
    }

    public function getObjects($folder)
    {
        if (! empty(self::$objects[$folder])) {
            return self::$objects[$folder];
        } else {
            return $this->loadObjects($folder);
        }
    }

    public function getObjectsFromToken($token)
    {
        $folder = $this->getFolderFromToken($token);
        return $this->getObjects($folder);
    }

    public function getFolderFromToken($token)
    {
        $pos1 = strpos($token, '_');
        if ($pos1) {
            if ($pos2 = strpos($token, '_', $pos1 + 1)) {
                $folder = substr($token, 0, $pos2);
                return $folder;
            } elseif ($pos2 === false) {
                return $token;
            }
        }
        return '';
    }

    public function getItemIdFromToken($token)
    {
        if (! $this->isInstalled($this->getFolderFromToken($token))) {
            return '';
        }

        preg_match('/\d+/', $token, $matches);
        if (! $matches[0]) {
            return '';
        }
        return $matches[0];
    }

    public function getItemTitleFromToken($token, $type, $ref)
    {
        $objects = $this->getObjectsFromToken($token);
        if (empty($objects[$ref])) {
            return '';
        }

        $ret = '';
        if ($type == 'tracker') {
            $ret = \TikiLib::lib('trk')->get_isMain_value($objects[$ref]['id'], $this->getItemIdFromToken($token));
        }

        return $ret;
    }

    public function getItemIdFromRef($token, $ref)
    {
        $objects = $this->getObjectsFromToken($token);
        return $objects[$ref]['id'];
    }
}
