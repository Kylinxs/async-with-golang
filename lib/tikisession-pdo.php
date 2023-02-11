<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

/* from
        http://www.spiration.co.uk/post/1333/PHP 5 sessions in mysql database with PDO db objects
*/

/**
 *
 */
class Session
{
    public $db;

    public function __destruct()
    {
        session_write_close();
    }

    /**
     * @param $path
     * @param $name
     * @return bool
     */
    public function open($path, $name)
    {
        return true;
    }

    /**
     * @return bool
     */
    public function close()
    {
        return true;
    }

    /**
     * @param $sesskey
     * @return mixed
     */
    public function read($sesskey)
    {
        global $prefs;

        $bindvars = [ $sesskey ];

        if ($prefs['session_lifetime'] > 0) {
            $qry = 'select data from sessions where sesskey = ? and expiry > ?';
            $bindvars[] = $prefs['session_lifetime'];
        } else {
            $qry = 'select data from sessions where sesskey = ?';
        }

        return TikiDb::get()->getOne($qry, $bindvars) ?: '';
    }

    /**
     * @param $sesskey
     * @param $data
     * @return bool
     */
    public function write($sesskey, $data)
    {
        global $prefs;

        if (TikiDb::get()->getLock($sesskey)) {
            $expiry = time() + ($prefs['session_lifetime'] * 60);
            TikiDb::get()->query('insert into sessions (sesskey, data, expiry) values( ?, ?, ? ) on duplicate key update data=values(data), expiry=values(expiry)', [$sesskey, $data, $expiry]);
            TikiDb::get()->releaseLock('sessions');
            return true;
        }
        return false;
    }

    /**
     * @param $sesskey
     * @return bool
     */
    public function destroy($sesskey)
    {
        $qry = 'delete from sessions where sesskey = ?';
        TikiDb::get()->query($qry, [ $sesskey ]);
        return true;
    }

    /**
     * @param $maxlifetime
     * @return bool
     */
    public function gc($maxlifetime)
    {
        global $prefs;

        if ($prefs['session_lifetime'] > 0) {
            $qry = 'delete from sessions where expiry < ?';
            TikiDb::get()->query($qry, [ time() ]);
        }

        return true;
    }
}

$session = new Session();

session_set_save_handler(
    [$session, 'open'],
    [$session, 'close'],
    [$session, 'read'],
    [$session, 'write'],
    [$session, 'destroy'],
    [$session, 'gc']
);
