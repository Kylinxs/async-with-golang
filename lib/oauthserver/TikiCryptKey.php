<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

use Lcobucci\JWT\Signer\Key;
use League\OAuth2\Server\CryptKey;

class TikiCryptKey extends CryptKey
{
    protected $key;
    public function __construct($key, $passPhrase = null, $keyPermissionsCheck = true)
    {
        $this->key = $key;
    }

    public function getKeyPath()
    {
        return new Key($this->key);
    }

    public function isNullKey()
    {
        return is_null($this->key);
    }
}
