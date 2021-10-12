
<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

namespace Tiki\Lib\Image;

/**
 * Factory class to select the right library for images manipulation.
 * We handle gd, imagemagick 1.x and 2.x.
 */
class Image
{
    /**
     * Auto-detect php extension to use as image lib.
     * This assumes imagick is better than gd, so try to find it first.
     *
     * @param $image
     * @param bool $isFile
     * @param string $format
     * @return ImagickNew|ImagickOld|Gd|null
     */
    public static function create($image, $isFile = false, $format = 'jpeg')
    {
        $libEntity = null;
        if (class_exists('Imagick')) {
            $libEntity = new ImagickNew($image, $isFile, $format); // create Imagick 2.x entity
        } elseif (function_exists('imagick_rotate')) {
            $libEntity = new ImagickOld($image, $isFile, $format); // create Imagick 1.x entity
        } elseif (function_exists('gd_info')) {
            $libEntity = new Gd($image, $isFile, $format); // create GD entity
        }

        return $libEntity;
    }

    /**
     * Check if the one of the libraries required is available.
     *
     * @return bool
     */
    public static function isAvailable()
    {
        if (class_exists('Imagick')) {
            return true;
        } elseif (function_exists('imagick_rotate')) {
            return true;
        } elseif (function_exists('gd_info')) {
            return true;
        }
        return false;
    }
}