<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

namespace Tiki\FileGallery\Manipulator;

use Feedback;

class ImageTransformer extends Manipulator
{
    /**
     * Resize an image to specific dimensions or use gallery default dimensions.
     *
     * @param array args with target width and height
     */
    public function run($args = [])
    {
        global $prefs;

        $imageReader = $this->getImageReader($this->file->filetype);
        $imageWriter = $this->getImageWriter($this->file->filetype);

        if (! $imageReader || ! $imageWriter) {
            return;
        }

        $image_size_x = $args['width'] ?? null;
        $image_size_y = $args['height'] ?? null;

        $gal_info = $this->file->galleryDefinition()->getInfo();

        $work_file = tempnam($prefs['tmpDir'], "imgresize");
        file_put_contents($work_file, $this->file->getContents());
        $image = null;

        $exif = [];

        if (
            extension_loaded('exif') && extension_loaded('gd') &&
                ($this->file->filetype === 'image/jpeg' || $this->file->filetype === 'image/tiff')
        ) {
            $exif = exif_read_data($work_file);
        }

        if (! empty($exif['Orientation'])) {
            // Rotate the image if the exif 'Orientation' shows it is needed
            $imageResource = $imageReader($work_file);
            switch ($exif['Orientation']) {
                case 3:
                    $image = imagerotate($imageResource, 180, 0);
                    break;
                case 6:
                    $image = imagerotate($imageResource, -90, 0);
                    break;
                case 8:
                    $image = imagerotate($imageResource, 90, 0);
                    break;
                default:
                    $image = $imageResource;
            }
            $imageWriter($image, $work_file);
        }

        // If it's an image format we can handle and gallery has limits on image sizes
        if (! ($gal_info["image_max_size_x"] && ! $gal_info["image_max_size_y"]) && (! $image_size_x && ! $image_size_y)) {
            unlink($work_file);
            return;
        }

        if (is_null($image_size_x)) {
            $image_size_x = $gal_info["image_max_size_x"];
        }
        if (is_null($image_size_y)) {
            $image_size_y = $gal_info["image_max_size_y"];
        }

        $image_size_info = getimagesize($work_file);
        $image_x = $image_size_info[0];
        $image_y = $image_size_info[1];
        if ($image_size_x) {
            $rx = $image_x / $image_size_x;
        } else {
            $rx = 0;
        }
        if ($image_size_y) {
            $ry = $image_y / $image_size_y;
        } else {
            $ry = 0;
        }

        $ratio = max($rx, $ry);
        if ($ratio > 1) { // Resizing will occur
            $image_new_x = (int) ($image_x / $ratio);
            $image_new_y = (int) ($image_y / $ratio);
            $image_resized_p = imagecreatetruecolor($image_new_x, $image_new_y);

            if ($image) {
                $image_p = $image;
            } else {
                $image_p = $imageReader($work_file);
            }

            if (! imagecopyresampled($image_resized_p, $image_p, 0, 0, 0, 0, $image_new_x, $image_new_y, $image_x, $image_y)) {
                Feedback::error(tra('Cannot resize the file:') . ' ' . $work_file);
            }

            imagedestroy($image_p);

            if (! $imageWriter($image_resized_p, $work_file)) {
                Feedback::error(tra('Cannot write the file:') . ' ' . $work_file);
            } else {
                Feedback::success(tr('Image was reduced: %s x %s -> %s x %s', $image_x, $image_y, (int)$image_new_x, (int)$image_new_y));
            }
        }

        $data = file_get_contents($work_file);
        unlink($work_file);

        $this->file->replaceContents($data);
    }

    private function getImageReader($type)
    {
        switch ($type) {
            case "image/gif":
                return 'imagecreatefromgif';
            case "image/png":
                return 'imagecreatefrompng';
            case "image/bmp":
            case "image/wbmp":
                return 'imagecreatefromwbmp';
            case "image/jpg":
            case "image/jpeg":
            case "image/pjpeg":
                return 'imagecreatefromjpeg';
        }
    }

    private function getImageWriter($type)
    {
        switch ($type) {
            case "image/gif":
                return 'imagegif';
            case "image/png":
                return 'imagepng';
            case "image/bmp":
            case "image/wbmp":
                return 'imagewbmp';
            case "image/jpg":
            case "image/jpeg":
            case "image/pjpeg":
                return 'imagejpeg';
        }
    }
}
