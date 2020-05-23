
#!/usr/bin/php
<?php
// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

if ($argc < 3) {
    $helpMsg = "\nUsage: php doc/devtools/convert_tracker_attachments.php trackerId fieldId [fileGalId] ['remove|copy']\n";
    $helpMsg .= "\nRuns in preview mode unless remove or copy are specified (n.b. remove does copy as well)\n";
    $helpMsg .= "\nExamples: \n\t\tphp doc/devtools/convert_tracker_attachments.php 1 2";
    $helpMsg .= "\n\t\tphp doc/devtools/convert_tracker_attachments.php 1 2 2\n\n";
    $helpMsg .= "\n\t\tphp doc/devtools/convert_tracker_attachments.php 42 7 0 remove\n\n";
    exit($helpMsg);
}

// run even (especially) if the site is closed
$bypass_siteclose_check = true;

require_once('tiki-setup.php');
ob_end_flush();
ob_implicit_flush();

$context = new Perms_Context("admin");

$trackerId = $argv[1];
$fieldId = $argv[2];

if (! isset($trackerId)) {
    echo "Error: Missing trackerId\n";
    exit(1);
}

if (! isset($fieldId)) {
    echo "Error: Missing fieldId\n";
    exit(1);
}

if (isset($argv[3])) {
    $galleryId = $argv[3];
} else {
    $galleryId = 0;
}

// default mode is "preview" with no changes
$remove = false;    // remove the attachment files afterwards
$copy = false;      // copy the attachments to the filegal

if (isset($argv[4]) && $argv[4] === 'remove') {
    $remove = true;
    $copy = true;
} elseif (isset($argv[4]) && $argv[4] === 'copy') {
    $copy = true;
}

/**
 * @param $trackerId
 * @param $fieldId
 * @param int $galleryId
 * @param bool $remove
 * @param bool $copy
 * @throws Services_Exception
 */
function convertAttachments($trackerId, $fieldId, $galleryId = 0, $remove = false, $copy = false)
{
    global $prefs;

    $trklib = TikiLib::lib('trk');
    $trackerUtilities = new Services_Tracker_Utilities();
    $definition = Tracker_Definition::get($trackerId);

    // Check if tracker and fieldId are valid
    $fgField = $trackerUtilities->getFieldsFromIds($definition, [$fieldId]);

    if (! $fgField || $fgField[0]['type'] !== 'FG') {
        echo "Error: Invalid fieldId {$fieldId} for trackerId {$trackerId}\n";
        exit(1);
    }
    $fgField = $fgField[0];

    if (! $galleryId && isset($fgField['options_map']['galleryId'])) {
        $galleryId = $fgField['options_map']['galleryId'];
    }

    if (! $galleryId) {
        $galleryId = $prefs['fgal_root_id'];
    }

// Check if its a valid file gallery
    try {
        $fileUtilities = new Services_File_Utilities();
        $galInfo = $fileUtilities->checkTargetGallery($galleryId);
    } catch (Services_Exception $e) {
        echo "Error: {$e->getMessage()}\n";
        exit(1);
    }

    $items = $trackerUtilities->getItems(['trackerId' => $trackerId]);
    $failedAttIds = [];
    $itemsFailed = 0;
    $itemsProcessed = 0;
    $attachmentsProcessed = 0;

    $mode = $remove ? "Moving" : ($copy ? "Copying" : "Previewing");
    echo "{$mode} attachment files from field {$fgField['permName']} tracker {$trackerId} to filegal {$galleryId}\n\n";

    foreach ($items as $item) {
        $itemId = $item['itemId'];


        $itemObject = Tracker_Item::fromId($itemId);

        if (! $itemObject || $itemObject->getDefinition() !== $definition) {
            continue;
        }

        $atts = $trklib->list_item_attachments($itemId, 0, -1, 'comment_asc', '');
        $fileIdList = [];

        $numAttachments = sizeof($atts['data']);

        if ($numAttachments === 0) {
            echo "[{$mode}] Tracker Item {$itemId} skipped (no attachments)\n";
            continue;
        } else {
            $ess = $numAttachments > 1 ? 's' : '';
            echo "[{$mode}] Updating tracker item {$itemId}: - {$numAttachments} attachment$ess\n";
        }

        foreach ($atts['data'] as $attachment) {
            $attachment = $trklib->get_item_attachment($attachment['attId']);

            if (! $attachment) {
                echo "\t- Warning: Unable to get item attachment with attId {$attachment['attId']}\n";
                continue;
            }

            $name = $attachment['filename'];
            $size = $attachment['filesize'];
            $type = $attachment['filetype'];
            $created = $attachment['created'];
            $auser = $attachment['user'];
            $description = $attachment['longdesc'];
            if ($attachment['comment']) {
                $description .= "\nComment\n" . $attachment['comment'];
            }
            if ($attachment['version']) {
                $description .= "\nVersion\n" . $attachment['version'];
            }
            if (file_exists($prefs['t_use_dir'] . $attachment['path'])) {
                $data = file_get_contents($prefs['t_use_dir'] . $attachment['path']);
            } else {
                $data = $attachment['data'];
            }

            $actualSize = strlen($data);
            if ((int) $size !== $actualSize) {
                echo "\t- Warning, size difference: {$size} !== {$actualSize}\n";
            }

            if ($copy) {
                try {
                    $fileId = $fileUtilities->uploadFile($galInfo, $name, $size, $type, $data, $auser, null, null, $description, $created);
                } catch (Exception $e) {
                    $fileId = false;
                    echo "\tError: File  {$attachment['filename']} on item  {$itemId} could not be saved\n";
                    echo "{$e->getMessage()}\n";
                }
                if ($fileId !== false) {
                    $fileIdList[] = $fileId;
                    echo "\t- Attachment {$attachment['filename']} uploaded to file gallery ({$actualSize} bytes)\n";
                } else {
                    echo "\t- Failed to upload attachment {$attachment['filename']} to file gallery\n";
                    $failedAttIds[] = $attachment['attId'];
                }
            } else {
                echo "\t{$attachment['filename']} uploaded to file gallery ({$actualSize} bytes)\n";
            }
        }

        if (empty($fileIdList) && $copy) {
            echo "[{$mode}] No files were uploaded to the file gallery (Item {$itemId} skipped)\n";
            continue;
        }

        $input = new JitFilter([
            'trackerId' => $trackerId,
            'itemId' => $itemId,
        ]);

        $processedFields = $itemObject->prepareInput($input);

        $fields = [];

        foreach ($processedFields as $key => $field) {
            $permName = $field['permName'];
            $fields[$permName] = isset($field['value']) ? $field['value'] : '';

            if ($field['fieldId'] == $fieldId && $field['type'] == 'FG') {
                $fields[$permName] = empty($fields[$permName]) ? implode(',', $fileIdList) : $fields[$permName] . ',' . implode(',', $fileIdList);
            }
        }

        $result = $trackerUtilities->updateItem(
            $definition,
            [
                'itemId' => $itemId,
                'status' => '',
                'fields' => $fields,
            ]
        );

        if ($result !== false) {
            $itemsProcessed++;
            if ($remove) {
                foreach ($atts['data'] as $attachment) {
                    if (! in_array($attachment['attId'], $failedAttIds)) {
                        $trklib->remove_item_attachment($attachment['attId'], $itemId);
                        $attachmentsProcessed++;
                    } else {
                        echo "(Attachment {$attachment['attId']} {$attachment['filename']} not removed)\n";
                        $numAttachments--;
                    }
                }
                echo "\tTracker item {$itemId} updated successfully and $numAttachments attachment$ess removed\n";
            } else {
                $attachmentsProcessed += $numAttachments;
                echo "\t[tracker item {$itemId} updated successfully]\n";
            }
        } else {
            echo "\tTracker item {$itemId} update failed\n";
            $itemsFailed++;
        }
        echo "\n";
    }

    $failCount = count($failedAttIds);
    $op = $remove ? "moved" : "copied";

    echo "\nConvert completed:\n\t{$itemsProcessed} item processed ({$itemsFailed} failed) and\n\t{$attachmentsProcessed} attachments {$op} ({$failCount} failed)\n\n";
}

convertAttachments($trackerId, $fieldId, $galleryId, $remove, $copy);

exit(0);