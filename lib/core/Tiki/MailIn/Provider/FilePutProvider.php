<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

namespace Tiki\MailIn\Provider;

use Tiki\MailIn\Action;

class FilePutProvider implements ProviderInterface
{
    public function isEnabled()
    {
        global $prefs;
        return $prefs['feature_file_galleries'] == 'y';
    }

    public function getType()
    {
        return 'file-put';
    }

    public function getLabel()
    {
        return tr('Save email as a file');
    }

    public function getActionFactory(array $acc)
    {
        return new Action\DirectFactory('Tiki\MailIn\Action\FilePut', [
            'galleryId' => $acc['galleryId'],
        ]);
    }
}
