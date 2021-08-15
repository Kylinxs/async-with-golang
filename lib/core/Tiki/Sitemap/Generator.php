<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

namespace Tiki\Sitemap;

use Perms;
use Melbahja\Seo\Sitemap;

/**
 * Generate XML files following the XML Protocol that can be submitted to search engines
 */
class Generator
{
    /**
     * The prefix to be added when scanning the sub folder for valid type handlers
     */
    public const NAMESPACE_PREFIX = '\\Tiki\\Sitemap\\Type\\';

    /**
     * The base class the type handlers must extend
     */
    public const BASE_CLASS = '\\Tiki\\Sitemap\\AbstractType';

    /**
     * The base of the sitemap file name
     */
    public const BASE_FILE_NAME = 'sitemap';

    protected $basePath;

    public function __construct($basePath = null)
    {
        global $tikipath;
        if (is_null($basePath)) {
            $basePath = $tikipath;
        }

        $this->basePath = $basePath;

        if (! function_exists('filter_our_sefurl') && file_exists($basePath . 'tiki-sefurl.php')) {
            include_once($basePath . 'tiki-sefurl.php');
        }
    }

    /**
     * Function for generate sitemap XML
     * @param string $baseUrl
     */
    public function generate($baseUrl)
    {
        global $user;

        /** @var \Perms $perms */
        $perms = Perms::getInstance();
        $oldGroups = $perms->getGroups();
        $loggedUser = $user;

        $perms->setGroups(['Anonymous']); // ensure that permissions are processed as Anonymous
        $user = null;

        $baseUrl = rtrim($baseUrl, '/');
        $relativePath = $this->getRelativePath();

        $sitemap = new Sitemap($baseUrl);
        $sitemap->setSavePath($this->basePath . $relativePath);
        $sitemap->setSitemapsUrl($baseUrl . '/' . $relativePath);
        $sitemap->setIndexName($this->getSitemapFilename());

        // Execute all other handlers, for the different type of content
        $directoryFiles = new \GlobIterator(__DIR__ . '/Type/*.php');
        /** @var \SplFileInfo $file */
        foreach ($directoryFiles as $file) {
            if ($file->getFilename() === 'index.php') {
                continue; // file to prevent directory browsing
            }

            $name = $file->getBasename('.php');
            $class = self::NAMESPACE_PREFIX . $name;

            if (! class_exists($class)) {
                continue;
            }

            /** @var AbstractType $typeHandler */
            $typeHandler = new $class($sitemap);
            if (is_subclass_of($typeHandler, self::BASE_CLASS)) {
                $typeHandler->generate();
            }
        }

        // Save sitemap files.
        $sitemap->save();

        $user = $loggedUser; // restore the configuration for permissions
        $perms->setGroups($oldGroups);
    }

    /**
     * Return the path to the sitemap
     *
     * @param bool $relative if it should return only the relative path (default true)
     * @return string
     */
    public function getSitemapPath($relative = true)
    {
        $path = $this->getRelativePath() . $this->getSitemapFilename();

        if (! $relative) {
            $path = $this->basePath . $path;
        }

        return $path;
    }

    /**
     * Return the sitemap file name
     *
     * @return string
     */
    public function getSitemapFilename()
    {
        return self::BASE_FILE_NAME . '-index.xml';
    }

    /**
     * Get relative path based on our current domain
     *
     * @return mixed|string
     */
    public function getRelativePath()
    {
        global $tikidomain;

        $base = 'storage/public/';

        return ! empty($tikidomain) ? $base . $tikidomain . '/' : $base;
    }
}
