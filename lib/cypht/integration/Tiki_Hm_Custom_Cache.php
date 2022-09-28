<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

/**
 * Generic cache
 * @package framework
 * @subpackage cache
 */
class Tiki_Hm_Custom_Cache extends Hm_Cache
{
    /**
     * @param Hm_Config $config site config object
     * @param object $session session object
     * @return void
     */
    public function __construct($config, $session)
    {
        $this->backend = new Tiki_Hm_Tiki_Cache();
    }

   /**
     * @param string $key name of value to cache
     * @param mixed $val value to cache
     * @param integer $lifetime how long to cache (if applicable for the backend)
     * @param boolean $session store in the session instead of the enabled cache
     * @return boolean
     */
    public function set($key, $val, $lifetime = 600, $session = false)
    {
        return $this->tiki_set($key, $val);
    }

     /**
     * @param string $key name of value to fetch
     * @param mixed $default value to return if not found
     * @param boolean $session fetch from the session instead of the enabled cache
     * @return mixed
     */
    public function get($key, $default = false, $session = false)
    {
        return $this->tiki_get($key, $default);
    }

    /**
     * @param string $key name to delete
     * @param boolean $session fetch from the session instead of the enabled cache
     * @return boolean
     */
    public function del($key, $session = false)
    {
        return $this->tiki_del($key);
    }

    /**
     * @param string $key name of value to fetch
     * @param mixed $default value to return if not found
     * @return mixed
     */
    private function tiki_get($key, $default)
    {
        $res = $this->backend->get($key, $default);
        if ($res === $default) {
            return $default;
        }
        return $res;
    }

    /**
     * @param string $key name to delete
     * @return boolean
     */
    private function tiki_del($key)
    {
        return $this->backend->del($key);
    }

    /*
     * @param string $key name of value to cache
     * @param mixed $val value to cache
     * @param integer $lifetime how long to cache (if applicable for the backend)
     * @return boolean
     */
    private function tiki_set($key, $val)
    {
        return $this->backend->set($key, $val);
    }
}
