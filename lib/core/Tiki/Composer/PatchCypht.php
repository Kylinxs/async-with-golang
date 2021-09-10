<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

namespace Tiki\Composer;

use Composer\Script\Event;
use Composer\Util\FileSystem;

class PatchCypht
{
    public static function setup(Event $event)
    {

        $cypht = __DIR__ . '/../../../cypht/';
        $vendors = $event->getComposer()->getConfig()->get('vendor-dir');
        $io = $event->getIO();

        if (substr($vendors, -1, 1) !== DIRECTORY_SEPARATOR) {
            $vendors .= DIRECTORY_SEPARATOR;
        }

        $fs = new FileSystem();
        umask(0);

        // setup stock version with missing files
        copy($cypht . 'hm3.ini', $vendors . 'jason-munro/cypht/hm3.ini');
        $tiki_module = $vendors . 'jason-munro/cypht/modules/tiki';
        if (! is_dir($tiki_module)) {
            mkdir($tiki_module, 0755);
        }
        $fs->copy($cypht . 'modules/tiki', $tiki_module);
        chdir($cypht . '../../');

        // generate storage dirs
        if (! is_dir('temp/cypht')) {
            mkdir('temp/cypht', 0777);
            mkdir('temp/cypht/app_data', 0777);
            mkdir('temp/cypht/attachments', 0777);
            mkdir('temp/cypht/users', 0777);
        }

        // generate Cypht config
        $php_binary = PHP_BINARY;
        $output = `cd {$vendors}jason-munro/cypht && $php_binary scripts/config_gen.php`;
        if (! strstr($output, 'hm3.rc file written')) {
            $io->write('Could not build Cypht package configuration. Check the output below and make sure minimum PHP version is available and executable as CLI.');
            $io->write($output);
        }

        // copy site.js and site.css
        copy($vendors . 'jason-munro/cypht/site/site.js', $cypht . 'site.js');
        copy($vendors . 'jason-munro/cypht/site/site.css', $cypht . 'site.css');

        // js custom pacthes
        $js = file_get_contents($cypht . 'site.js');
        $js = str_replace("url: ''", "url: 'tiki-ajax_services.php?controller=cypht&action=ajax&'+window.location.search.substr(1)", $js);
        $js = str_replace("xhr.open('POST', window.location.href)", "xhr.open('POST', 'tiki-ajax_services.php?controller=cypht&action=ajax&'+window.location.search.substr(1))", $js);
        $js = str_replace("xhr.open('POST', '', true);", "xhr.open('POST', 'tiki-ajax_services.php?controller=cypht&action=ajax&'+window.location.search.substr(1), true);", $js);
        $js = str_replace("var ajax = new Hm_Ajax_Request", "var ajax = new tiki_Hm_Ajax_Request", $js);
        $js = preg_replace("#^.*/\* swipe event handler \*/#s", "", $js);
        $js = file_get_contents($vendors . "jason-munro/cypht/third_party/resumable.min.js") . "\n\n" . $js;
        $js = file_get_contents($vendors . "jason-munro/cypht/third_party/tingle.min.js") . "\n\n" . $js;
        file_put_contents($cypht . 'site.js', $js);
    }
}
