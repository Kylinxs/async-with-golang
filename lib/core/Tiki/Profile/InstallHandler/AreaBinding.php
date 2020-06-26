
<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

class Tiki_Profile_InstallHandler_AreaBinding extends Tiki_Profile_InstallHandler
{
    public function getData()
    {
        if ($this->data) {
            return $this->data;
        }

        $defaults = [];
        $data = array_merge($defaults, $this->obj->getData());

        $data = Tiki_Profile::convertYesNo($data);

        return $this->data = $data;
    }

    public function canInstall()
    {
        $data = $this->getData();
        if (! isset($data['category'], $data['perspective'])) {
            return false;
        }

        return true;
    }

    public function doInstall()
    {
        $areaslib = TikiLib::lib('areas');

        $data = $this->getData();

        $this->replaceReferences($data);

        $extraData = [];
        foreach (['exclusive', 'share_common', 'enabled'] as $key) {
            if (! empty($data[$key])) {
                $extraData[$key] = $data[$key];
            }
        }

        $areaslib->bind_area($data['category'], $data['perspective'], $extraData);

        return "{$data['category']}-{$data['perspective']}";
    }
}