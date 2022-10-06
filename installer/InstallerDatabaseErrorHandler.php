<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

namespace Tiki\Installer;

use TikiDb;
use TikiDb_ErrorHandler;

class InstallerDatabaseErrorHandler implements TikiDb_ErrorHandler
{
    /**
     * @param TikiDb $db
     * @param        $query
     * @param        $values
     * @param        $result
     */
    public function handle(TikiDb $db, $query, $values, $result)
    {
    }
}
