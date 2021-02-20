
<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

namespace Tiki\Lib\Alchemy;

use MediaAlchemyst\Alchemyst;
use MediaAlchemyst\DriversContainer;
use MediaAlchemyst\Specification\Animation;
use MediaAlchemyst\Specification\Image;
use MediaVorus\Media\MediaInterface;
use MediaVorus\MediaVorus;
use Neutron\TemporaryFilesystem\Manager as FsManager;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesser;
use TikiLib;

/**
 * Wrapper of the library Media Alchemy, for media processing
 */
class AlchemyLib
{
    const TYPE_IMAGE = 'image/png';
    const TYPE_IMAGE_ANIMATION = 'image/gif';

    /** @var Alchemyst */
    protected $alchemyst;

    /** @var MediaVorus */
    protected $mediavorus;

    /** @var Guesser */
    private static $mimeTypeGuesserInstance = null;

    /**
     * AlchemyLib constructor.
     */
    public function __construct()
    {
        global $prefs;

        if (! self::isLibraryAvailable()) {
            \Feedback::error(tr('To use AlchemyLib Tiki needs the media-alchemyst/media-alchemyst package. If you do not have permission to install this package, ask the site administrator.'), true);
        }

        $drivers = new DriversContainer();
        $drivers['configuration'] = [
            'ffmpeg.threads' => 4,
            'ffmpeg.ffmpeg.timeout' => 3600,
            'ffmpeg.ffprobe.timeout' => 60,
            'ffmpeg.ffmpeg.binaries' => $prefs['alchemy_ffmpeg_path'],
            'ffmpeg.ffprobe.binaries' => $prefs['alchemy_ffprobe_path'],
            'imagine.driver' => $prefs['alchemy_imagine_driver'],
            'unoconv.binaries' => $prefs['alchemy_unoconv_path'],
            'unoconv.timeout' => $prefs['alchemy_unoconv_timeout'] ?: 60,
            'unoconv.port' => $prefs['alchemy_unoconv_port'],
            'gs.binaries' => $prefs['alchemy_gs_path'],
            'gs.timeout' => 60,
        ];

        $this->alchemyst = new Alchemyst($drivers, FsManager::create());

        $this->mediavorus = $drivers['mediavorus'];
    }

    /**
     * Check if Alchemy is available
     *
     * @return bool true if the base library is available
     */
    public static function isLibraryAvailable()
    {
        return class_exists('MediaAlchemyst\Alchemyst');
    }

    /**
     * Check if we have the required rights related to the AlchemyLib
     * @return bool
     */
    public static function hasReadWritePolicies()
    {
        $commandOutput = `identify -list policy`;
        preg_match("/Path: .*ImageMagick.*policy.xml.*rights:(.+)pattern: PDF/s", $commandOutput, $matches);

        if (! isset($matches[1])) {
            return false;
        }

        $rights = explode(' ', strtolower($matches[1]));

        foreach (['write', 'read'] as $requiredRight) {
            if (! in_array($requiredRight, $rights)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Convert a source file into a image, static or animated gif
     *
     * @param string $sourcePath the patch to read the file
     * @param string $destinationPath the path to store the result
     * @param int|null $width image width, use null to keep the source width
     * @param int|null $height image height, use null to keep the source height
     * @param bool $animated true for animated gif
     * @param int $page the page number of the document to convert
     * @return null|string the media type of the file, null on error
     */
    public function convertToImage($sourcePath, $destinationPath, $width = null, $height = null, $animated = false, $page = 1)
    {
        global $tiki_p_admin;
        try {
            $guess = $this->mediavorus->guess($sourcePath);

            $guessedType = $guess->getType();

            if ($guessedType == MediaInterface::TYPE_VIDEO && $animated) {
                $targetType = new Animation();
                $targetImageType = self::TYPE_IMAGE_ANIMATION;
            } else {
                $targetType = new Image();
                $targetImageType = self::TYPE_IMAGE;
            }

            if ($width > 0 && $height > 0) {
                $targetType->setDimensions($width, $height);
            }

            if ($targetType instanceof Image && $page != 1) {
                $targetType->fromPage($page);
            }

            $this->alchemyst->turnInto($sourcePath, $destinationPath, $targetType);

            return $targetImageType;
        } catch (\Exception $e) {
            $logsLib = TikiLib::lib('logs');
            $logsLib->add_log('Alchemy', $e->getMessage());
            $previous = $e->getPrevious();

            while ($previous) {
                $logsLib->add_log('Alchemy', $previous->getMessage());
                $previous = $previous->getPrevious();
            };
            $append = '.';
            if ($tiki_p_admin == 'y') {
                $append = ': ' . $e->getMessage() . ". ";
            }

            \Feedback::error(tr('Failed to convert document into image') . $append . tr('Please check Tiki Action Log for more information.'));
        }

        return null;
    }

    /**
     * Allow to instruct the MimeTypeGuesser to take a given mime type as the type for a given file
     *
     * @param string $filePath
     * @param string $mimeType
     */
    public static function hintMimeTypeByFilePath($filePath, $mimeType)
    {
        if (! self::isLibraryAvailable()) {
            return;
        }

        self::getMimeTypeGuesserInstance()->add($filePath, $mimeType);
    }


    /**
     * Return the Guesser Instance registered by AlchemyLib in Symfony MimeTypeGuesser
     *
     * @return Guesser
     */
    public static function getMimeTypeGuesserInstance()
    {
        if (null === self::$mimeTypeGuesserInstance) {
            self::$mimeTypeGuesserInstance = new Guesser();
            MimeTypeGuesser::getInstance()->register(self::$mimeTypeGuesserInstance);
        }

        return self::$mimeTypeGuesserInstance;
    }
}