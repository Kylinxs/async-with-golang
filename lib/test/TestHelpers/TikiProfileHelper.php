
<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

namespace Tiki\Test\TestHelpers;

use Exception;
use Tiki_Profile;
use Tiki_Profile_Installer;

class TikiProfileHelper
{
    /**
     * @param string $profileDomain
     * @param string $profileName
     *
     * @throws Exception
     */
    public static function applyProfile($profileDomain, $profileName): void
    {
        $profile = Tiki_Profile::fromNames($profileDomain, $profileName);

        if (! $profile->validateNamedObjectsReferences()) { // sanity check on the Named Objects references
            throw new Exception('Some of the named object references in the profile are invalid');
        }

        $installer = new Tiki_Profile_Installer();
        $result = $installer->install($profile, 'all', false);

        if (! $result) {
            $errorString = '';
            foreach ($installer->getFeedback() as $error) {
                $errorString .= $error . "\n";
            }
            throw new Exception($errorString);
        }
    }

    /**
     * @param string $sourceDirectory folder container the profile
     * @param string $profileName the profile name (will append .yml to get the file)
     * @param array $search array of strings to replace in the profile
     * @param array $replace array of the values to use in replace
     * @return string the folder (profileDomain) that was generated
     * @throws Exception if the temporary folder of tiki can't be found
     */
    public static function applyTemplateProfile($sourceDirectory, $profileName, $search = [], $replace = []): string
    {
        $folder = TikiProfileHelper::createTemporaryDomainFromTemplate($sourceDirectory, $profileName, $search, $replace);
        TikiProfileHelper::applyProfile($folder, $profileName);

        return $folder;
    }

    /**
     * @param string $sourceDirectory folder container the profile
     * @param string $profileName the profile name (will append .yml to get the file)
     * @param array $search array of strings to replace in the profile
     * @param array $replace array of the values to use in replace
     * @return string the folder (profileDomain) that was generated
     * @throws Exception if the temporary folder of tiki can't be found
     */
    public static function createTemporaryDomainFromTemplate($sourceDirectory, $profileName, $search = [], $replace = []): string
    {
        // use the temp folder to drop the new profile
        $destinationFolder = realpath(
            __DIR__ . DIRECTORY_SEPARATOR .
            '..' . DIRECTORY_SEPARATOR .
            '..' . DIRECTORY_SEPARATOR .
            '..' . DIRECTORY_SEPARATOR .
            'temp'
        );

        if (! $destinationFolder) {
            throw new Exception('Tiki temp folder could not be found');
        }

        $destinationFolder .= DIRECTORY_SEPARATOR . uniqid('profile-', true);

        mkdir($destinationFolder);

        if (is_array($search) && count($search)) {
            $profileContent = file_get_contents($sourceDirectory . DIRECTORY_SEPARATOR . $profileName . '.yml');
            $profileContent = str_replace($search, $replace, $profileContent);
            file_put_contents($destinationFolder . DIRECTORY_SEPARATOR . $profileName . '.yml', $profileContent);
        } else {
            copy(
                $sourceDirectory . DIRECTORY_SEPARATOR . $profileName . '.yml',
                $destinationFolder . DIRECTORY_SEPARATOR . $profileName . '.yml'
            );
        }

        return $destinationFolder;
    }
}