<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

class MemcacheSession
{
    private $enabled = false;
    private $lib;

    /**
     * Set up the session cache, hijacking handlers from ADODB_Session
     * presumably already in place.
     */
    public function init()
    {

        session_set_save_handler(
            [ $this, 'open' ],
            [ $this, 'close' ],
            [ $this, 'read' ],
            [ $this, 'write' ],
            [ $this, 'destroy' ],
            [ $this, 'gc' ]
        );

        $this->enabled = TikiLib::lib("memcache")->isEnabled();
        $this->lib = TikiLib::lib("memcache");
    }

    /**
     * Build a memcache key based on a given session key
     *
     * @param  string Session key
     * @return string Memcache key
     */
    private function buildCacheKey($session_key)
    {
        return $this->lib ? $this->lib->buildKey(['role' => 'session-cache', 'session_key' => $session_key]) : false;
    }

    public function __destruct()
    {
        session_write_close();
    }

    public function open($save_path, $session_name, $persist = null)
    {
        return $this->enabled;
    }

    public function close()
    {
        return $this->enabled;
    }

    public function read($key)
    {
        $cache_key = $this->buildCacheKey($key);

        if ($this->enabled) {
            return $this->lib->get($cache_key) ?: '';
        }
    }

    public function write($key, $val)
    {
        global $prefs;

        if ($this->enabled) {
            $lock_key = $this->buildCacheKey($key . '.lock');
            if (! $this->lib->get($lock_key)) {
                $this->lib->set($lock_key, $key, 60 * $prefs['session_lifetime']);
                $this->lib->set($this->buildCacheKey($key), $val, 60 * $prefs['session_lifetime']);
                $this->lib->delete($lock_key);
            }
        }

        return $this->enabled;
    }

    public function destroy($key)
    {
        if ($this->enabled) {
            $this->lib->delete($this->buildCacheKey($key));
        }

        return $this->enabled;
    }

    public function gc($maxlifetime)
    {
        return $this->enabled;
    }
}

$memcache_session = new MemcacheSession();
$memcache_session->init();
