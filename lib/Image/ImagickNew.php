
<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

namespace Tiki\Lib\Image;

use Imagick;
use ImagickException;

/**
 *
 */
class ImagickNew extends ImageAbstract
{
    /**
     * @param $image
     * @param bool $isfile
     * @param string $format
     */
    public function __construct($image, $isfile = false, $format = 'jpeg')
    {
        if ($isfile) {
            $this->filename = $image;
            parent::__construct($image, $isfile);
        } else {
            parent::__construct($image, $isfile);
        }
        $this->format = $format;
    }

    protected function loadData()
    {
        if (! $this->loaded) {
            if (! empty($this->filename)) {
                $this->data = new Imagick();
                try {
                    $this->data->readImage($this->filename);
                    $this->loaded = true;
                    $this->filename = null;
                } catch (ImagickException $e) {
                    $this->loaded = true;
                    $this->data = null;
                }
            } elseif (! empty($this->data)) {
                $tmp = new Imagick();
                try {
                    $tmp->readImageBlob($this->data);
                    $this->data =& $tmp;
                    $this->loaded = true;
                } catch (ImagickException $e) {
                    $this->data = null;
                }
            }
            if ($this->data) {
                $this->data->setImageFormat($this->format);
            }
        }
    }

    /**
     * @param $x
     * @param $y
     * @return mixed
     */
    protected function resizeImage($x, $y)
    {
        if ($this->data) {
            return $this->data->scaleImage($x, $y);
        }
    }

    public function resizeThumb()
    {
        if ($this->thumb !== null) {
            $this->data = new Imagick();
            try {
                $this->data->readImageBlob($this->thumb);
                $this->loaded = true;
            } catch (ImagickException $e) {
                $this->loaded = true;
                $this->data = null;
            }
        } else {
            $this->loadData();
        }
        if ($this->data) {
            parent::resizeThumb();
        }
    }

    /**
     * @param $format
     */
    public function setFormat($format)
    {
        $this->loadData();
        if ($this->data) {
            $this->format = $format;
            $this->data->setFormat($format);
        }
    }

    /**
     * @return string
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * @return mixed
     */
    public function display()
    {
        $this->loadData();
        if ($this->data) {
            if ($this->quality !== 75) {
                if ($this->format === 'jpeg') {
                    $this->data->setImageCompression(Imagick::COMPRESSION_JPEG);
                    $this->data->setImageCompressionQuality($this->quality);
                } else {
                    if ($this->format === 'png') {
                        $this->data->setImageCompression(Imagick::COMPRESSION_ZIP);
                        $this->data->setImageCompressionQuality($this->quality / 10);
                    }
                }
            }
            return $this->data->getImageBlob();
        }
    }

    /**
     * @param $angle
     * @return bool
     */
    public function rotate($angle)
    {
        $this->loadData();
        if ($this->data) {
            $this->data->rotateImage(-$angle);
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param $format
     * @return bool
     */
    public function isSupported($format)
    {
        $image = new Imagick();
        $format = strtoupper(trim($format));

        // Theses formats have pb if multipage document
        switch ($format) {
            case 'PDF':
            case 'PS':
            case 'HTML':
                return false;
        }
        return in_array($format, $image->queryFormats());
    }

    /**
     * @return mixed
     */
    public function getHeight()
    {
        $this->loadData();
        if ($this->data) {
            return $this->data->getImageHeight();
        }
    }

    /**
     * @return mixed
     */
    public function getWidth()
    {
        $this->loadData();
        if ($this->data) {
            return $this->data->getImageWidth();
        }
    }

    /**
     * Allow adding text as overlay to a image
     * @param $text
     * @return string
     */
    public function addTextToImage($text)
    {
        $this->loadData();

        if (! $this->data) {
            return false;
        }

        $font = dirname(dirname(__DIR__)) . '/lib/captcha/DejaVuSansMono.ttf';

        $padLeft = 20;
        $padBottom = 20;

        $image = new Imagick();
        $image->readImageBlob($this->data);
        $height = $image->getimageheight();

        $draw = new ImagickDraw();
        $draw->setFillColor('#000000');
        $draw->setStrokeColor(new ImagickPixel('#000000'));
        $draw->setStrokeWidth(3);
        $draw->setFont($font);
        $draw->setFontSize(12);
        $image->annotateImage($draw, $padLeft, $height - $padBottom, 0, $text);

        $draw = new ImagickDraw();
        $draw->setFillColor('#ffff00');
        $draw->setFont($font);
        $draw->setFontSize(12);
        $image->annotateImage($draw, $padLeft, $height - $padBottom, 0, $text);

        $this->data = $image;
        return $image->getImageBlob();
    }
}