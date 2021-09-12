
<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

class Tiki_Version_Utils
{
    /**
     * Validates if there are some updates for a given version of Tiki
     *
     * @param $version
     * @return array
     * @throws Exception
     */
    public static function checkUpdatesForVersion($version)
    {
        $tikilib = TikiLib::lib('tiki');

        $checker = new Tiki_Version_Checker();
        $checker->setVersion(Tiki_Version_Version::get($version));
        $checker->setCycle($tikilib->get_preference('tiki_release_cycle'));

        $expiry = $tikilib->now - $tikilib->get_preference('tiki_version_check_frequency');
        $upgrades = $checker->check(
            function ($url) use ($expiry) {
                $cachelib = TikiLib::lib('cache');
                $tikilib = TikiLib::lib('tiki');

                $content = $cachelib->getCached($url, 'http', $expiry);

                if ($content === false) {
                    $content = $tikilib->httprequest($url);
                    $cachelib->cacheItem($url, $content, 'http');
                }

                return $content;
            }
        );

        return array_map(
            function ($upgrade) {
                return $upgrade->getMessage();
            },
            $upgrades
        );
    }
}