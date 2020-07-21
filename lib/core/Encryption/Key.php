<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

namespace Tiki\Encryption;

use TikiLib;
use Services_Exception_Denied;

class Key
{
    protected $encryption_key;

    public function __construct($encryptionKeyId)
    {
        $result = TikiLib::lib('service')->internal('encryption', 'get_key', ['keyId' => $encryptionKeyId]);
        $this->encryption_key = $result['key'];
        if (! $this->encryption_key) {
            throw new NotFoundException(tr('Encryption key you are trying to access no longer exists!'));
        }
    }

    public function get($attr)
    {
        if (isset($this->encryption_key[$attr])) {
            return $this->encryption_key[$attr];
        } else {
            return null;
        }
    }

    public function encryptData($data)
    {
        $key = $this->decryptKey();
        $crypt = TikiLib::lib('crypt');
        $crypt->initSeed($key);
        try {
            return $crypt->encryptData($data);
        } catch (\Exception $e) {
            throw new KeyException($e->getMessage());
        }
    }

    public function decryptData($data)
    {
        $key = $this->decryptKey();
        $crypt = TikiLib::lib('crypt');
        $crypt->initSeed($key);
        try {
            return $crypt->decryptData($data);
        } catch (\Exception $e) {
            throw new KeyException($e->getMessage());
        }
    }

    protected function decryptKey()
    {
        try {
            $key = TikiLib::lib('service')->internal('encryption', 'decrypt_key', ['keyId' => $this->encryption_key['keyId']]);
            if (! $key) {
                throw new Services_Exception_Denied(tr('Could not decrypt key.'));
            }
            return $key;
        } catch (Services_Exception_Denied $e) {
            throw new KeyException($e->getMessage());
        }
    }

    public function manualEntry()
    {
        $smarty = TikiLib::lib('smarty');
        $smarty->loadPlugin('smarty_function_bootstrap_modal');
        $href = smarty_function_bootstrap_modal(['controller' => 'encryption', 'action' => 'enter_key', 'keyId' => $this->encryption_key['keyId']], $smarty);
        return '<a href="' . $href . '" class="encryption-key-entry">' . tr('Try with a manually entered key.') . '</a>';
    }
}
