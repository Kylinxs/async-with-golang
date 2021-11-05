
<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

namespace Tiki\Package;

/**
 * Class VendorHelper
 * Contains logic associated with Vendor related operations
 */
class VendorHelper
{
    /**
     * Keyword to add to vendor path prefixes in order to replace it with the package name
     */
    public const REPLACEABLE_PACKAGE_NAME_KEYWORD = '{{package}}';

    /**
     * Available vendor paths to check
     */
    public const AVAILABLE_VENDOR_PATHS = [
        'vendor_custom' => 'vendor_custom/tiki-pkg-' . self::REPLACEABLE_PACKAGE_NAME_KEYWORD . '/',
        'vendor'        => 'vendor/'
    ];

    /**
     * Returns the vendor path of a package file given a specific order to check. Returns false if file wasn't found in any paths to check.
     * @param $packageName
     * @param $path
     * @param bool $fullPath
     * @return bool|string
     */
    public static function getAvailableVendorPath($packageName, $path, $fullPath = true)
    {
        foreach (self::AVAILABLE_VENDOR_PATHS as $pathPrefix) {
            $pathPrefix = str_replace(self::REPLACEABLE_PACKAGE_NAME_KEYWORD, $packageName, $pathPrefix);
            $filePath = $pathPrefix . $path;

            if (file_exists($filePath)) {
                return $fullPath ? $filePath : $pathPrefix;
            }
        }

        return false;
    }
}