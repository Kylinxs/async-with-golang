
<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

namespace Tiki\Installer;

use DomainException;

/**
 * A procedure to adapt a Tiki database to a code change, implemented by SQL, Yaml or PHP files
 *
 * @see Installer
 */
class Patch
{
    public static $list = null;
    public const NOT_APPLIED = 0;
    public const ALREADY_APPLIED = 1;
    public const NEWLY_APPLIED = 2;

    private $name;
    private $status = null;
    public $optional = false;

    public function __construct($name, $status)
    {
        $this->name = $name;
        $this->status = $status;
    }

    /**
     * Defines the state
     *
     * @param int $status One of the constants of this class
     */
    public function setStatus($status)
    {
        if (! in_array($status, [self::NOT_APPLIED, self::ALREADY_APPLIED, self::NEWLY_APPLIED])) {
            throw new DomainException();
        }
        $this->status = $status;
    }


    /**
     * Get the patches matching the specified statuses
     *
     * @param int[] $statuses Allowed statuses
     * @param bool true to obtain optional patches, false for required only
     *
     * @return Patch[] Matching patches
     */
    public static function getPatches($statuses, $optional = false)
    {
        $matches = [];
        foreach (self::$list as $name => $patch) {
            if (in_array($patch->status, $statuses) && ($optional || ! $patch->optional)) {
                $matches[$name] = $patch;
            }
        }
        return $matches;
    }

    /**
     * Indicates if the patch is applied
     *
     * @return bool true is the patch is applied, false otherwise
     */
    public function isApplied()
    {
        return $this->status != self::NOT_APPLIED;
    }

    /**
     * Mark as installed
     */
    public function record()
    {
        Installer::getInstance()->query("INSERT INTO tiki_schema (patch_name, install_date) VALUES(?, NOW())", [$this->name]);
        $this->pushPatchNameToBrowser('build_patch', $this->name);
        self::$list[$this->name]->setStatus(self::NEWLY_APPLIED);
    }

    public function pushPatchNameToBrowser($targetElement, $content)
    {
        $scripts = <<<HTML
        <script class="progress_bar_script">
            var element = parent.document.getElementById("{$targetElement}");
            if (element) {
                element.innerHTML = "{$content}";
            }
        </script>
HTML;
        $working_env = php_sapi_name();
        if ($working_env != 'cli') {
            echo $scripts;
            ob_flush();
        }
    }
}