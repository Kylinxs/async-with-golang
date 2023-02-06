<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

class Tracker_Field_EmailFolder extends Tracker_Field_Files implements Tracker_Field_Exportable
{
    public static function getTypes()
    {
        global $prefs;

        $options = [
            'EF' => [
                'name' => tr('Email Folder'),
                'description' => tr('Associate email messages with tracker items.'),
                'prefs' => ['trackerfield_email_folder', 'feature_file_galleries'],
                'tags' => ['advanced'],
                'help' => 'Email-Folder-Tracker-Field',
                'default' => 'y',
                'params' => [
                    'galleryId' => [
                        'name' => tr('Gallery ID'),
                        'description' => tr('File gallery to upload new emails into.'),
                        'filter' => 'int',
                        'legacy_index' => 0,
                        'profile_reference' => 'file_gallery',
                    ],
                    'useFolders' => [
                        'name' => tr('Use Folders'),
                        'description' => tr('Use separate folders like Inbox, Sent, Trash, Archive.'),
                        'filter' => 'int',
                        'options' => [
                            0 => tr('No'),
                            1 => tr('Yes'),
                        ],
                    ],
                    'inboxName' => [
                        'name' => tr('Inbox Name'),
                        'description' => tr('Name of the Inbox folder.'),
                        'filter' => 'text',
                        'default' => 'Inbox',
                        'depends' => [
                            'field' => 'useFolders',
                            'value' => '1'
                        ],
                    ],
                    'sentName' => [
                        'name' => tr('Sent Name'),
                        'description' => tr('Name of the Sent folder.'),
                        'filter' => 'text',
                        'default' => 'Sent',
                        'depends' => [
                            'field' => 'useFolders',
                            'value' => '1'
                        ],
                    ],
                    'trashName' => [
                        'name' => tr('Trash Name'),
                        'description' => tr('Name of the Trash folder.'),
                        'filter' => 'text',
                        'default' => 'Trash',
                        'depends' => [
                            'field' => 'useFolders',
                            'value' => '1'
                        ],
                    ],
                    'archiveName' => [
                        'name' => tr('Archive Name'),
                        'description' => tr('Name of the Archive folder.'),
                        'filter' => 'text',
                        'default' => 'Archive',
                        'depends' => [
                            'field' => 'useFolders',
                            'value' => '1'
                        ],
                    ],
                    'draftName' => [
                        'name' => tr('Draft Name'),
                        'description' => tr('Name of the Draft folder.'),
                        'filter' => 'text',
                        'default' => 'Draft',
                        'depends' => [
                            'field' => 'useFolders',
                            'value' => '1'
                        ],
                    ],
                    'customFolders' => [
                        'name' => tr('Custom Folders'),
                        'description' => tr('Comma separated list of additional folders to use.'),
                        'filter' => 'text',
                        'default' => '',
                        'depends' => [
                            'field' => 'useFolders',
                            'value' => '1'
                        ],
                    ],
                    'openedFolders' => [
                        'name' => tr('Opened Folders'),
                        'description' => tr('Comma separated list of folders to show opened by default.'),
                        'filter' => 'text',
                        'default' => '',
                        'depends' => [
                            'field' => 'useFolders',
                            'value' => '1'
                        ],
                    ],
                    'composePage' => [
                        'name' => tr('Compose Page'),
                        'description' => tr('Name of the wiki page where compose button will direct to. Leave empty for default Webmail page.'),
                        'filter' => 'text',
                        'default' => '',
                    ],
                ],
            ],
        ];
        return $options;
    }

    function getFieldData(array $requestData = [])
    {
        global $prefs;
        $filegallib = TikiLib::lib('filegal');

        $galleryId = (int) $this->getOption('galleryId');
        $galinfo = $filegallib->get_file_gallery($galleryId, false);
        if (! $galinfo || empty($galinfo['galleryId'])) {
            Feedback::error(tr('%0 field: Gallery #%1 not found', $this->getConfiguration('name'), $galleryId));
            return [];
        }

        $value = $this->getValue();
        $decoded = json_decode($value, true);
        if ($decoded !== null) {
            $fileIds = $decoded;
        } else {
            $fileIds = [
                'inbox' =>