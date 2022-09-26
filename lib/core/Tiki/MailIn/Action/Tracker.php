
<?php

namespace Tiki\MailIn\Action;

use Services_Tracker_Utilities;
use Tiki\MailIn\Account;
use Tiki\MailIn\Source\Message;
use TikiLib;
use Tracker_Definition;

class Tracker implements ActionInterface
{
    private $tracker;
    private $attachments;

    /**
     * Tracker constructor.
     * @param array $params
     */
    public function __construct(array $params)
    {
        $this->tracker = isset($params['trackerId']) ? intval($params['trackerId']) : 0;
        $this->attachments = $params['attachments'];
    }

    public function getName()
    {
        return tr('Store mail in Tracker');
    }

    public function isEnabled()
    {
        global $prefs;

        return $prefs['feature_trackers'] == 'y';
    }

    public function isAllowed(Account $account, Message $message)
    {
        $user = $message->getAssociatedUser();
        $perms = TikiLib::lib('tiki')->get_user_permission_accessor($user, 'tracker', $this->tracker);
        if (! $perms->tiki_p_view_trackers || ! $perms->tiki_p_create_tracker_items) {
            return false;
        }
        return true;
    }

    public function createTracker($from)
    {
        $trackerUtilities = new Services_Tracker_Utilities();
        $trackerData = [
            'name' => $from,
            'description' => '',
            'descriptionIsParsed' => 'n'
        ];
        $trackerId = $trackerUtilities->createTracker($trackerData);

        return $trackerId;
    }

    public function execute(Account $account, Message $message)
    {
        global $prefs;
        $tikilib = TikiLib::lib('tiki');
        $filegallib = TikiLib::lib('filegal');
        $trackerUtilities = new Services_Tracker_Utilities();

        $fieldSubject = [
            'name' => 'subject',
            'type' => 't',
            'isMandatory' => false,
            'description' => '',
            'descriptionIsParsed' => '',
            'permName' => null,
            'options' => null,
        ];

        $fieldUser = [
            'name' => 'user',
            'type' => 'u',
            'isMandatory' => false,
            'description' => '',
            'descriptionIsParsed' => '',
            'permName' => null,
            'options' => null,
        ];

        $from = $message->getFromAddress();
        $fieldFrom = [
            'name' => 'from',
            'type' => 'm',
            'isMandatory' => true,
            'description' => '',
            'descriptionIsParsed' => '',
            'permName' => null,
            'options' => null,
        ];

        $fieldTo = [
            'name' => 'to',
            'type' => 'm',
            'isMandatory' => true,
            'description' => '',
            'descriptionIsParsed' => '',
            'permName' => null,
            'options' => null,
        ];

        $description = "Created from " . $message->getFromAddress();
        $fieldDescription = [
            'name' => 'description',
            'type' => 'a',
            'isMandatory' => false,
            'description' => '',
            'descriptionIsParsed' => '',
            'permName' => null,
            'options' => null,
        ];


        $fieldDate = [
            'name' => 'date',
            'type' => 'f',
            'isMandatory' => true,
            'description' => '',
            'descriptionIsParsed' => '',
            'permName' => null,
            'options' => null,
        ];

        $fieldBody = [
            'name' => 'body',
            'type' => 'a',
            'isMandatory' => false,
            'description' => '',
            'descriptionIsParsed' => '',
            'permName' => null,
            'options' => null,
        ];

        $fieldAttachments = [
            'name' => 'attachments',
            'type' => 'FG',
            'isMandatory' => false,
            'description' => '',
            'descriptionIsParsed' => '',
            'permName' => null,
            'options' => null,
        ];

        $data = [
            $fieldSubject,
            $fieldUser,
            $fieldFrom,
            $fieldTo,
            $fieldDescription,
            $fieldDate,
            $fieldBody,
            $fieldAttachments
        ];

        $datasItem = [
            $message->getSubject(),
            $message->getAssociatedUser(),
            $message->getFromAddress(),
            $message->getRecipientAddress(),
            $description,
            $tikilib->now,
            $message->getBody(),
        ];

        // predefined elements of the incoming mail
        $preFields = [
            'Subject',
            'User',
            'From',
            'To',
            'Description',
            'Date',
            'Body',
            'Attachments'
        ];


        if ($this->tracker == 0) {
            // create new tracker
            $trackerId = $this->createTracker($from);

            // create tracker fields
            $permNames = [];
            foreach ($data as $fieldData) {
                $fieldData['trackerId'] = $trackerId;
                $fieldId = $trackerUtilities->createField($fieldData);
                // build permNames table
                $permNames[] = 'f_' . $fieldId;
            }

            $definition = $this->getDefinition($trackerId);

            // upload attachments
            $itemFiles = $this->uploadAttachments($account, $message);

            if ($this->canAttach() && $account->hasAutoAttach()) {
                $i = 0;
                foreach ($message->getAttachments() as $att) {
                    // upload each attachment
                    $id = $this->attachFile($gal_info, $att, $message->getAssociatedUser());
                    if ($i == 0) {
                        $itemFiles .= $id;
                    } else {
                        $itemFiles .= ',' . $id;
                    }
                    $i++;
                }
            }

            $datasItem[] = $itemFiles;
            $toItem = [];
            // construct final table
            for ($i = 0; $i < sizeof($permNames); $i++) {
                $toItem[$permNames[$i]] = $datasItem[$i];
            }
            $itemId = $trackerUtilities->insertItem(
                $definition,
                [
                    'status' => null,
                    'fields' => $toItem,
                ]
            );
        } else {
            $trackerId = $account->getTrackerId();
            $definition = Tracker_Definition::get($trackerId);
            $preferences = $account->getPreferences();
            $preferences = json_decode($preferences, true);
            $preferences = $preferences['links'];

            // upload attachments
            $itemFiles = $this->uploadAttachments($account, $message);
            $datasItem[] = $itemFiles;

            $toItem = [];
            // construct final table
            for ($i = 0; $i < sizeof($preFields); $i++) {
                for ($j = 0; $j < sizeof($preferences); $j++) {
                    if ($preFields[$i] == $preferences[$j]['from']) {
                        $toItem[$preferences[$j]['to']] = $datasItem[$i];
                    }
                }
            }
            $itemId = $trackerUtilities->insertItem(
                $definition,
                [
                    'status' => null,
                    'fields' => $toItem,
                ]
            );
        }
        //return true;
    }

    public function canAttach()
    {
        global $prefs;
        if ($prefs['trackerfield_files'] != 'y') {
            return false;
        }

        return true;
    }

    private function attachFile($gal_info, $att, $user)
    {
        $filegallib = TikiLib::lib('filegal');
        $result = $filegallib->upload_single_file($gal_info, $att['name'], $att['size'], $att['type'], $att['data'], $user, null, null, null);
        return $result;
    }

    private function getDefinition($trackerId)
    {
        $trklib = TikiLib::lib('trk');
        $fields = $trklib->list_tracker_fields($trackerId, 0, -1, 'position_asc', '', false /* Translation must be done from the views to avoid translating the sources on edit. */);

        $definition = Tracker_Definition::get($trackerId);

        $definition->setFields($fields['data']);
        return $definition;
    }

    // upload incoming mail files
    private function uploadAttachments($account, $message)
    {
        global $prefs;
        $filegallib = TikiLib::lib('filegal');
        $galleryId = $prefs['fgal_root_id']; // defalut file gallery
            $gal_info = $filegallib->get_file_gallery($galleryId);
        if (! $gal_info) {
            $galInfo = [
                'galleryId' => '',
                'parentId' => $galleryId,
                'name' => 'MailInTrackerGal' . time(),
                'description' => '',
                'user' => $message->getAssociatedUser(),
                'public' => 'y',
                'visible' => 'y',
            ];
            $galleryId = $filegallib->replace_file_gallery($galInfo);
            $gal_info = $filegallib->get_file_gallery($galleryId);
        }

            $itemFiles = '';
        if ($this->canAttach() && $account->hasAutoAttach()) {
            $i = 0;
            foreach ($message->getAttachments() as $att) {
                // upload each attachment
                $id = $this->attachFile($gal_info, $att, $message->getAssociatedUser());
                if ($i == 0) {
                    $itemFiles .= $id;
                } else {
                    $itemFiles .= ',' . $id;
                }
                $i++;
            }
        }
            return $itemFiles;
    }
}