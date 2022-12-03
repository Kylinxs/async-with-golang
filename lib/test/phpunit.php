<?php

declare(strict_types=1);

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.

// This is a modified version of vendor_bundled/vendor/phpunit/phpunit/phpunit. The
// reason to this file exists is phpunit seems to have problem when reading relative
// paths for the parameter `-c` or `--configuration`. This file set the current
// directory to TIKI_PATH before starting phpunit, and make possible to run
// `composer test -d vendor_bundled` again.
// https://github.com/sebastianbergmann/phpunit/issues/552

if (! ini_get('date.timezone')) {
    ini_set('date.timezone', 'UTC');
}

chdir(dirname(dirname(__DIR__)));
define('PHPUNIT_COMPOSER_INSTALL', implode(DIRECTORY_SEPARATOR, ['vendor_bundled', 'vendor', 'autoload.php']));

$options = getopt('', ['prepend:']);
if (isset($options['prepend'])) {
    require $options['prepend'];
}
unset($options);

require PHPUNIT_COMPOSER_INSTALL;
PHPUnit\TextUI\Command::main();
