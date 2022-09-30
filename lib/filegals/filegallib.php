<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

use Tiki\FileGallery\Definition as GalleryDefinition;
use Tiki\FileGallery\FileWrapper\WrapperInterface as FileWrapper;
use Tiki\FileGallery\File as TikiFile;
use Tiki\FileGallery\FileDraft as TikiFileDraft;
use Tiki\FileGallery\ImageTransformer;

class FileGalLib extends TikiLib
{
    private $wikiupMoved = [];

    protected static $getGalleriesParentIdsCache = null;
    protected $loadedGalleryDefinitions = [];

    public function isPodCastGallery($galleryId, $gal_info = null)
    {
        if (empty($gal_info)) {
            $gal_info = $this->get_file_gallery_info((int)$galleryId);
        }
        if (isset($gal_info['type']) && in_array($gal_info['type'], ['podcast', 'vidcast'])) {
            return true;
        } else {
            return false;
        }
    }

    public function is_default_gallery_writable()
    {
        $definition = new GalleryDefinition($this->default_file_gallery());
        return $definition->isWritable();
    }

    public function get_attachment_gallery($objectId, $objectType, $create = false)
    {
        switch ($objectType) {
            case 'wiki page':
                return $this->get_wiki_attachment_gallery($objectId, $create);
        }

        return false;
    }

    public function get_wiki_attachment_gallery($pageName, $create = false)
    {
        global $prefs;

        $return = $this->getGalleryId($pageName, $prefs['fgal_root_wiki_attachments_id']);

        // Get the Wiki Attachment Gallery for this wiki page or create it if it does not exist
        if ($create && ! $return) {
            // Create the attachment gallery only if the wiki page really exists
            if ($this->get_page_id_from_name($pageName) > 0) {
                $return = $this->replace_file_gallery(
                    [
                        'name' => $pageName,
                        'user' => 'admin',
                        'type' => 'attachments',
                        'public' => 'y',
                        'visible' => 'y',
                        'parentId' => $prefs['fgal_root_wiki_attachments_id']
                    ]
                );
            }
        }

        return $return;
    }

    /**
     * Looks for and returns a user's file gallery, depending on the various prefs
     *
     * @return bool|int     false if none found, id of user's filegal otherwise
     */

    public function get_user_file_gallery($auser = '')
    {
        global $user, $prefs;
        $tikilib = TikiLib::lib('tiki');

        if (empty($auser)) {
            $auser = $user;
        }

        // Feature check + Anonymous don't have their own Users File Gallery
        if (empty($auser) || $prefs['feature_use_fgal_for_user_files'] == 'n' || $prefs['feature_userfiles'] == 'n' || ( $userId = $tikilib->get_user_id($auser) ) <= 0) {
            return false;
        }

        $conditions = [
            'type' => 'user',
            'name' => $userId,
            'user' => $auser,
            'parentId' => $prefs['fgal_root_user_id']
        ];

        if ($idGallery = $this->table('tiki_file_galleries')->fetchOne('galleryId', $conditions)) {
            // upgrades from very old tikis may have multiple user filegals per user, so merge them into one here
            unset($conditions['name']);
            $conditions['galleryId'] = $this->table('tiki_file_galleries')->not($idGallery);
            $rows = $this->table('tiki_file_galleries')->fetchAll(['galleryId'], $conditions);
            foreach ($rows as $row) {
                $this->table('tiki_files')->updateMultiple(
                    ['galleryId' => $idGallery],            // set gallery to the proper one (name eq userId)
                    ['galleryId' => $row['galleryId']]  // where gallery is this one
                );
                $this->remove_file_gallery($row['galleryId']);
            }

            return $idGallery;
        }

        $fgal_info = $conditions;
        $fgal_info['public'] = 'n';
        $fgal_info['visible'] = $prefs['userfiles_private'] === 'y' || $prefs['userfiles_hidden'] === 'y' ? 'n' : 'y';
        $fgal_info['quota'] = $prefs['userfiles_quota'];

        // Create the user gallery if it does not exist yet
        $idGallery = $this->replace_file_gallery($fgal_info);

        return $idGallery;
    }

    /**
     * Functionality to migrate files from image galleries to file galleries
     */
    final public function migrateFilesFromImageGalleries(): int
    {

        global $prefs;

        $attributelib = TikiLib::lib('attribute');

        // the tables should have been renamed in installer/schema/20211020_mark_image_gallery_tables_as_unused_tiki.sql but let's check
        $tables = TikiDb::get()->listTables();
        if (! in_array('tiki_galleries', $tables) && in_array('zzz_unused_tiki_galleries', $tables)) {
            $zzz = "zzz_unused_";
        } else {
            $zzz = '';
        }
        $tikiGalleries = TikiDb::get()->table("{$zzz}tiki_galleries");
        $tikiGalleriesScales = TikiDb::get()->table("{$zzz}tiki_galleries_scales");
        $tikiImages = TikiDb::get()->table("{$zzz}tiki_images");
        $tikiImagesData = TikiDb::get()->table("{$zzz}tiki_images_data");

        $galleryIdMap = [];
        $rootFileGalleryId = 0;

        if ($tikiImages->fetchCount([])) {
            $rootFileGalleryId = $this->replace_file_gallery([
                'name' => tra('Migrated Image Galleries'),
                'description' => tra('Converted from image gallery'),
            ]);

            foreach ($tikiGalleries->fetchAll() as $gallery) {
                $gallery['sort_mode'] = $gallery['sortorder'] . '_' . $gallery['sortdirection'];
                $oldGalleryId = $gallery['galleryId'];
                $gallery['galleryId'] = 0;      // we want a new one

                if ($gallery['parentgallery'] < 0 || empty($galleryIdMap[$gallery['parentgallery']])) {
                    $gallery['parentId'] = $rootFileGalleryId;
                } else {
                    $gallery['parentId'] = $galleryIdMap[$gallery['parentgallery']];
                }

                $gallery['show_name'] = $gallery['showname'];
                $gallery['show_id'] = $gallery['showimageid'];
                $gallery['show_description'] = $gallery['showdescription'];
                $gallery['show_author'] = $gallery['showuser'];    // TODO something about creator?
                $gallery['show_hits'] = $gallery['showhits'];

                if ($gallery['show_name'] === 'y' && $gallery['showfilename'] === 'y') {
                    $gallery['show_name'] = 'a';
                } elseif ($gallery['showfilename'] === 'y') {
                    $gallery['show_name'] = 'f';
                } else {
                    $gallery['show_name'] = 'n';
                }

                unset(
                    $gallery['geographic'],
                    $gallery['theme'],
                    $gallery['rowImages'],
                    $gallery['thumbSizeX'],
                    $gallery['thumbSizeY'],
                    $gallery['sortorder'],
                    $gallery['sortdirection'],
                    $gallery['galleryimage'],    // TODO something?
                    $gallery['parentgallery'],
                    $gallery['showname'],
                    $gallery['showimageid'],
                    $gallery['showdescription'],
                    $gallery['showcreated'],
                    $gallery['showuser'],
                    $gallery['showhits'],
                    $gallery['showxysize'],
                    $gallery['showfilesize'],
                    $gallery['showname'],
                    $gallery['showfilename'],
                    $gallery['defaultscale'],    // TODO something?
                    $gallery['showcategories']
                );

                $fileGalleryId = $this->replace_file_gallery($gallery);
                $gallery['galleryId'] = $fileGalleryId;
                $galleryIdMap[$oldGalleryId] = $fileGalleryId;

                $images = $tikiImages->fetchAll([], ['galleryId' => $oldGalleryId]);
                foreach ($images as $image) {
                    $imageData = $tikiImagesData->fetchAll([], [
                        'type' => 'o',                            // not thumbnails
                        'imageId' => $image['imageId'],
                    ]);
                    $image = array_merge($imageData[0], $image);

                    if (strlen($image['data']) < 3 && ! empty($image['path'])) { // read from disk
                        if (file_exists($prefs['gal_use_dir'] . $image["path"])) {
                            $image['data'] = file_get_contents($prefs['gal_use_dir'] . $image["path"]);
                        }
                        $image['path'] = '';
                    }

                    $image['galleryId'] = $fileGalleryId;

                    $file = new TikiFile([
                        'galleryId' => $image['galleryId'],
                        'description' => $image['description'],
                        'user' => $image['user'],
                        'author' => $image['user'],
                        'created' => $image['created'],
                    ]);
                    $fileId = $file->replace($image['data'], $image['filetype'], $image['name'], $image['filename'], $image['xsize'], $image['ysize']);

                    TikiLib::lib('geo')->set_coordinates(
                        'file',
                        $fileId,
                        [
                            'lon' => $image['lon'],
                            'lat' => $image['lat'],
                        ]
                    );

                    // add the old imageId as an attribute for future use in the img plugin
                    $attributelib->set_attribute('file', $fileId, 'tiki.file.imageid', $image['imageId']);
                }
            }
        }
        return $rootFileGalleryId;
    }

    /**
     * Calculate gallery name for user galleries
     *
     * @param array $gal_info   gallery info array
     * @param string $auser     optional user (global used if not supplied)
     * @return string           name of gallery modified if a "top level" user galley
     */
    public function get_user_gallery_name($gal_info, $auser = null)
    {
        global $user, $prefs;

        if ($auser === null) {
            $auser = $user;
        }
        $name = $gal_info['name'];

        if (! empty($auser) && $prefs['feature_use_fgal_for_user_files'] == 'y') {
            if ($gal_info['type'] === 'user' && $gal_info['parentId'] == $prefs['fgal_root_user_id']) {
                if ($gal_info['user'] === $auser) {
                    $name = tra('My Files');
                } else {
                    $name = tr('Files of %0', TikiLib::lib('user')->clean_user($gal_info['user']));
                }
            }
        }
        return $name;
    }

    /**
     * Checks if a galleryId is the user filegal root and converts it to the correct user gallery for that user
     * Otherwise just passes through
     *
     * @param $galleryId    gallery id to check and change if necessary
     * @return int          user's gallery id if applicable
     */
    public function check_user_file_gallery($galleryId)
    {
        global $prefs;

        if ($prefs['feature_use_fgal_for_user_files'] === 'y' && $galleryId == $prefs['fgal_root_user_id']) {
            $galleryId = $this->get_user_file_gallery();
        }

        return (int) $galleryId;
    }

    /**
     * @param $fileInfo
     * @param string $galInfo
     * @param bool $disable_notifications
     * @return bool|TikiDb_Adodb_Result|TikiDb_Pdo_Result
     * @throws Exception
     */
    public function remove_file($fileInfo, $galInfo = '', $disable_notifications = false)
    {
        global $prefs, $user;

        if (empty($fileInfo['fileId'])) {
            return false;
        }
        $fileId = $fileInfo['fileId'];

        if ($prefs['vimeo_upload'] === 'y' && $prefs['vimeo_delete'] === 'y' && $fileInfo['filetype'] === 'video/vimeo') {
            $attributes = TikiLib::lib('attribute')->get_attributes('file', $fileId);
            if ($url = $attributes['tiki.content.url']) {
                $video_id = substr($url, strrpos($url, '/') + 1);   // not ideal, but video_id not stored elsewhere (yet)
                TikiLib::lib('vimeo')->deleteVideo($video_id);
            }
        }

        $definition = $this->getGalleryDefinition($fileInfo['galleryId']);

        $this->deleteBacklinks(null, $fileId);
        $definition->delete(new TikiFile($fileInfo));

        $archives = $this->get_archives($fileId);
        foreach ($archives['data'] as $archive) {
            $definition->delete(new TikiFile($archive));
            $this->remove_object('file', $archive['fileId']);
        }

        $files = $this->table('tiki_files');
        $result = $files->delete(['fileId' => $fileId]);
        $files->deleteMultiple(['archiveId' => $fileId]);

        $this->remove_draft($fileId);
        $this->remove_object('file', $fileId);

        //Watches
        if (! $disable_notifications) {
            $this->notify($fileInfo['galleryId'], $fileInfo['name'], $fileInfo['filename'], '', 'remove file', $user);
        }

        if ($prefs['feature_actionlog'] == 'y') {
            $logslib = TikiLib::lib('logs');
            $logslib->add_action('Removed', $fileId . '/' . $fileInfo['filename'], 'file', '');
        }

        TikiLib::events()->trigger(
            'tiki.file.delete',
            [
                'type' => 'file',
                'object' => $fileId,
                'galleryId' => $fileInfo['galleryId'],
                'filetype' => $fileInfo['filetype'],
                'user' => $GLOBALS['user'],
            ]
        );

        return $result;
    }

    /**
     * Remove all drafts of a file
     *
     * @param int $fileId
     * @param string $user
     * @param bool $skip_actual
     * @return TikiDb_Adodb_Result|TikiDb_Pdo_Result
     */
    public function remove_draft($fileId, $user = null, $skip_actual = false)
    {
        $fileDraftsTable = $this->table('tiki_file_drafts');

        if (! $skip_actual) {
            $file = TikiFile::id($fileId);
            $def = $file->galleryDefinition();
            if (isset($user)) {
                $drafts = $fileDraftsTable->fetchAll(['path'], ['fileId' => (int) $fileId, 'user' => $user]);
            } else {
                $drafts = $fileDraftsTable->fetchAll(['path'], ['fileId' => (int) $fileId]);
            }
            foreach ($drafts as $draft) {
                $to_delete = new TikiFile(['path' => $draft['path']]);
                $def->delete($to_delete);
            }
        }

        if (isset($user)) {
            return $fileDraftsTable->delete(['fileId' => (int) $fileId, 'user' => $user]);
        } else {
            return $fileDraftsTable->deleteMultiple(['fileId' => (int) $fileId]);
        }
    }

    /**
     * Validate draft and replace real file
     *
     * @param int $fileId
     * @return bool|TikiDb_Adodb_Result|TikiDb_Pdo_Result
     * @throws Exception
     * @global string $user
     */
    public function validate_draft($fileId)
    {
        global $prefs, $user;

        $fileDraftsTable = $this->table('tiki_file_drafts');
        $galleriesTable = $this->table('tiki_file_galleries');
        $filesTable = $this->table('tiki_files');

        if ($prefs['feature_file_galleries_save_draft'] == 'y') {
            if (! $draft = $fileDraftsTable->fetchFullRow(['fileId' => (int) $fileId, 'user' => $user])) {
                return false;
            }

            $draft = TikiFileDraft::fromFileDraft($draft);
            $file = TikiFile::id($fileId);

            $file->validateDraft($draft);

            return $this->remove_draft($fileId, $user, true);
        }
    }

    /**
     * @param $file
     * @param $gallery
     * @return TikiDb_Adodb_Result|TikiDb_Pdo_Result
     */
    public function set_file_gallery($file, $gallery)
    {
        $files = $this->table('tiki_files');
        $result = $files->updateMultiple(
            ['galleryId' => $gallery],
            ['anyOf' => $files->expr('(`fileId` = ? OR `archiveId` = ?)', [$file, $file])]
        );

        require_once('lib/search/refresh-functions.php');
        refresh_index('files', $file);

        return $result;
    }

    /**
     * @param int  $id        ID of gallery to be removed or file in the gallery to be removed
     * @param int  $galleryId The parent gallery of the gallery to be removed
     * @param bool $recurse
     * @return bool|TikiDb_Adodb_Result|TikiDb_Pdo_Result
     * @throws Exception
     */
    public function remove_file_gallery($id, $galleryId = 0, $recurse = true)
    {
        global $prefs;
        $fileGalleries = $this->table('tiki_file_galleries');
        $id = (int)$id;

        if ($id == $prefs['fgal_root_id']) {
            return false;
        }
        if (empty($galleryId)) {
            $info = $this->get_file_info($id);
            $galleryId = $info['galleryId'];
        } else {
            $info = null;
        }

        $cachelib = TikiLib::lib('cache');
        $cachelib->empty_type_cache('fgals_perms_' . $id . "_");
        if (isset($info['galleryId'])) {
            $cachelib->empty_type_cache('fgals_perms_' . $info['galleryId'] . "_");
        }
        $cachelib->empty_type_cache($this->get_all_galleries_cache_type());

        $gal_info = $this->get_file_gallery_info($id);

        $result = $fileGalleries->delete(['galleryId' => $id]);

        $this->remove_object('file gallery', $id);

        if ($filesInfo = $this->get_files_info_from_gallery_id($id, false, false)) {
            foreach ($filesInfo as $fileInfo) {
                $this->remove_file($fileInfo, '', true);
            }
        }

        TikiLib::events()->trigger('tiki.filegallery.delete', [
            'type' => 'file gallery',
            'object' => $galleryId,
            'user' => $GLOBALS['user'],
            'info' => $gal_info,
        ]);

        // If $recurse, also recursively remove children galleries
        if ($recurse) {
            $galleries = $fileGalleries->fetchColumn(
                'galleryId',
                ['parentId' => $id, 'galleryId' => $fileGalleries->greaterThan(0)]
            );

            foreach ($galleries as $galleryId) {
                $this->remove_file_gallery($galleryId, $id, true);
            }
        }

        return $result;
    }

    /**
     * Fetch a complete set of file gallery information from the database
     * @param $id int Gallery Id to fetch information of.
     *
     * @return mixed
     */
    public function get_file_gallery_info($id)
    {
        return $this->table('tiki_file_galleries')->fetchFullRow(['galleryId' => (int) $id]);
    }

    /**
     * @param $galleryId
     * @param $new_parent_id
     * @return bool|TikiDb_Adodb_Result|TikiDb_Pdo_Result
     * @throws Exception
     */
    public function move_file_gallery($galleryId, $new_parent_id)
    {
        if ((int)$galleryId <= 0 || (int)$new_parent_id == 0 || $galleryId == $new_parent_id) {
            return false;
        }

        $cachelib = TikiLib::lib('cache');
        $cachelib->empty_type_cache($this->get_all_galleries_cache_type());

        return $this->table('tiki_file_galleries')->updateMultiple(
            ['parentId' => (int) $new_parent_id],
            ['galleryId' => (int) $galleryId]
        );
    }

    public function default_file_gallery()
    {
        global $prefs;
        return [
            'name' => '',
            'description' => '',
            'visible' => 'y',
            'public' => 'y',
            'type' => 'default',
            'direct' => null,
            'parentId' => $prefs['fgal_root_id'],
            'lockable' => 'n',
            'archives' => 0,
            'quota' => $prefs['fgal_quota_default'],
            'image_max_size_x' => $prefs['fgal_image_max_size_x'],
            'image_max_size_y' => $prefs['fgal_image_max_size_y'],
            'backlinkPerms' => 'n',
            'show_backlinks' => 'n',
            'show_deleteAfter' => $prefs['fgal_list_deleteAfter'],
            'show_lastDownload' => 'n',
            'sort_mode' => $prefs['fgal_sortField'] . '_' . $prefs['fgal_sortDirection'],
            'maxRows' => (int)$prefs['maxRowsGalleries'],
            'max_desc' => 0,
            'subgal_conf' => '',
            'show_id' => $prefs['fgal_list_id'],
            'show_icon' => $prefs['fgal_list_type'],
            'show_name' => $prefs['fgal_list_name'],
            'show_description' => $prefs['fgal_list_description'],
            'show_size' => $prefs['fgal_list_size'],
            'show_created' => $prefs['fgal_list_created'],
            'show_modified' => $prefs['fgal_list_lastModif'],
            'show_creator' => $prefs['fgal_list_creator'],
            'show_author' => $prefs['fgal_list_author'],
            'show_last_user' => $prefs['fgal_list_last_user'],
            'show_comment' => $prefs['fgal_list_comment'],
            'show_files' => $prefs['fgal_list_files'],
            'show_hits' => $prefs['fgal_list_hits'],
            'show_lockedby' => $prefs['fgal_list_lockedby'],
            'show_checked' => ! empty($prefs['fgal_checked']) ? $prefs['fgal_checked'] : 'y' ,
            'show_share' => $prefs['fgal_list_share'],
            'show_explorer' => $prefs['fgal_show_explorer'],
            'show_path' => $prefs['fgal_show_path'],
            'show_slideshow' => $prefs['fgal_show_slideshow'],
            'show_source' => 'o',
            'wiki_syntax' => '',
            'show_ocr_state' => $prefs['fgal_show_ocr_state'],
            'default_view' => $prefs['fgal_default_view'],
            'template' => null,
            'icon_fileId' => ! empty($prefs['fgal_icon_fileId']) ? $prefs['fgal_icon_fileId'] : null,
            'ocr_lang' => '',
        ];
    }
    public function replace_file_gallery($fgal_info)
    {

        global $prefs;
        $galleriesTable = $this->table('tiki_file_galleries');
        $objectsTable = $this->table('tiki_objects');
        $fgal_info = array_merge($this->default_file_gallery(), $fgal_info);

        // ensure gallery name is userId for root user gallery
        if (
            $prefs['feature_use_fgal_for_user_files'] === 'y' &&
                $fgal_info['type'] === 'user' &&
                $fgal_info['parentId'] == $prefs['fgal_root_user_id']
        ) {
            $userId = TikiLib::lib('user')->get_user_id($fgal_info['user']);

            if ($userId) {
                $fgal_info['name'] = $userId;
            }
        }

        // if the user is admin or the user is the same user and the gallery exists
        // then replace if not then create the gallary if the name is unused.
        $fgal_info['name'] = strip_tags($fgal_info['name']);

        $fgal_info['description'] = strip_tags($fgal_info['description']);
        if ($fgal_info['sort_mode'] == 'created_desc') {
            $fgal_info['sort_mode'] = null;
        }

        if (! empty($fgal_info['galleryId']) && $fgal_info['galleryId'] > 0) {
            $old_info = $this->get_file_gallery_info($fgal_info['galleryId']);

            $fgal_info['lastModif'] = $this->now;
            $galleryId = (int) $fgal_info['galleryId'];

            $galleriesTable->update($fgal_info, ['galleryId' => $galleryId]);

            $objectsTable->update(
                ['name' => $fgal_info['name'],  'description' => $fgal_info['description']],
                ['type' => 'file gallery', 'itemId' => $galleryId]
            );
            $finalEvent = 'tiki.filegallery.update';
        } else {
            $old_info = [];

            unset($fgal_info['galleryId']);
            $fgal_info['created'] = $this->now;
            $fgal_info['lastModif'] = $this->now;

            $galleryId = $galleriesTable->insert($fgal_info);

            $finalEvent = 'tiki.filegallery.create';
        }

        $cachelib = TikiLib::lib('cache');
        $cachelib->empty_type_cache($this->get_all_galleries_cache_type());

        TikiLib::events()->trigger($finalEvent, [
            'type' => 'file gallery',
            'object' => $galleryId,
            'user' => $GLOBALS['user'],
            'info' => $fgal_info,
            'old_info' => $old_info,
        ]);

        // event_handler($action,$object_type,$object_id,$options);
        return $galleryId;
    }

    public function get_all_galleries_cache_name($user)
    {
        $tikilib = TikiLib::lib('tiki');
        $categlib = TikiLib::lib('categ');

        $gs = $tikilib->get_user_groups($user);
        $tmp = "";
        if (is_array($gs)) {
            $tmp .= implode("\n", $gs);
        }
        $tmp .= '----';
        if ($jail = $categlib->get_jail()) {
            $tmp .= implode("\n", $jail);
        }
        return md5($tmp);
    }

    public function get_all_galleries_cache_type()
    {
        return 'fgals_';
    }

    public function process_batch_file_upload($galleryId, $file, $user, $description, &$errors)
    {
        $extract_dir = 'temp/' . basename($file) . '/';
        mkdir($extract_dir);
        $archive = new PclZip($file);
        $archive->extract(PCLZIP_OPT_PATH, $extract_dir, PCLZIP_OPT_REMOVE_ALL_PATH);
        unlink($file);
        $h = opendir($extract_dir);

        // check filters
        $upl = 1;
        $errors = [];
        while (($file = readdir($h)) !== false) {
            if ($file != '.' && $file != '..' && is_file($extract_dir . '/' . $file)) {
                if (! $this->is_filename_valid($file)) {
                    $errors[] = tra('Invalid filename (using filters for filenames)') . ': ' . $file;
                    $upl = 0;
                }

                try {
                    $this->assertUploadedFileIsSafe($file);
                } catch (Exception $e) {
                    $errors[] = $e->getMessage();
                    $upl = 0;
                }

                if (! $this->checkQuota(filesize($extract_dir . $file), $galleryId, $error)) {
                    $errors[] = $error;
                    $upl = 0;
                }
            }
        }
        if (! $upl) {
            return false;
        }
        rewinddir($h);
        while (($file = readdir($h)) !== false) {
            if ($file != '.' && $file != '..' && is_file($extract_dir . '/' . $file)) {
                if (false === $data = @file_get_contents($extract_dir . $file)) {
                    $errors[] = tra('Cannot open this file:') . "temp/$file";
                    return false;
                }
                $tikiFile = new TikiFile([
                    'galleryId' => $galleryId,
                    'description' => $description,
                    'user' => $user,
                ]);
                $type = TikiLib::lib('mime')->from_path($file, $extract_dir . $file);
                $fileId = $tikiFile->replace($data, $type, $file, $file);
                unlink($extract_dir . $file);
            }
        }

        closedir($h);
        rmdir($extract_dir);
        return true;
    }

    public function get_file_info($fileId, $include_search_data = true, $include_data = true, $use_draft = false)
    {
        global $prefs, $user;

        $return = $this->get_files_info(null, (int)$fileId, $include_search_data, $include_data, 1);

        if (! $return) {
            return false;
        }

        $file = $return[0];

        if ($use_draft && $prefs['feature_file_galleries_save_draft'] == 'y') {
            $draft = $this->table('tiki_file_drafts')->fetchRow(
                ['filename', 'filesize', 'filetype', 'data', 'user', 'path', 'hash', 'lastModif', 'lockedby'],
                ['fileId' => (int) $fileId, 'user' => $user]
            );

            if ($draft) {
                $file = array_merge($file, $draft);
            }
        }

        return $file;
    }

    public function get_file_label($fileId)
    {
        $info = $this->get_file_info($fileId, false, false, false);

        $arr = array_filter([$info['name'], $info['filename']]);

        return reset($arr);
    }

    public function get_files_info_from_gallery_id($galleryId, $include_search_data = false, $include_data = false)
    {
        return $this->get_files_info((int)$galleryId, null, $include_search_data, $include_data);
    }

    public function get_files_info($galleryIds = null, $fileIds = null, $include_search_data = false, $include_data = false, $numrows = -1)
    {
        $files = $this->table('tiki_files');


        if ($include_search_data && $include_data) {
            $fields = $files->all();
        } else {
            $fields = ['fileId', 'galleryId', 'name', 'description', 'created', 'filename', 'filesize', 'filetype', 'user', 'author', 'hits', 'votes', 'points', 'path', 'reference_url', 'is_reference', 'hash', 'lastModif', 'lastModifUser', 'lockedby', 'comment', 'archiveId','ocr_state'];
            if ($include_search_data) {
                $fields[] = 'search_data';
                $fields[] = 'ocr_data';
            }
            if ($include_data) {
                $fields[] = 'data';
            }
        }

        $conditions = [];

        if (! empty($fileIds)) {
            $conditions['fileId'] = $files->in((array) $fileIds);
        }

        if (! empty($galleryIds)) {
            $conditions['galleryId'] = $files->in((array) $galleryIds);
        }

        return $files->fetchAll($fields, $conditions, $numrows);
    }

    /**
     * @param $id
     * @param $params
     * @return TikiDb_Adodb_Result|TikiDb_Pdo_Result
     */
    public function update_file($id, $params)
    {
        if (isset($params['name'])) {
            $params['name'] = strip_tags($params['name']);
        }
        if (isset($params['description'])) {
            $params['name'] = strip_tags($params['description']);
        }
        $params['lastModif'] = $this->now;

        $files = $this->table('tiki_files');

        $result = $files->update($params, ['fileId' => $id]);

        $galleryId = $files->fetchOne('galleryId', ['fileId' => $id]);

        if ($galleryId >= 0) {
            $this->table('tiki_file_galleries')->update(['lastModif' => $this->now], ['galleryId' => $galleryId]);
        }

        require_once('lib/search/refresh-functions.php');
        refresh_index('files', $id);

        return $result;
    }

    public function duplicate_file($id, $galleryId = null, $newName = false)
    {
        global $user;

        $origFile = TikiFile::id($id);

        $file = $origFile->clone();
        $file->setParam('user', $user);
        if (! empty($galleryId)) {
            $file->setParam('galleryId', $galleryId);
        }

        $newName = ($newName ? $newName : $origFile->name . tra(' copy'));
        $id = $file->replace($origFile->getContents(), $origFile->filetype, $newName, $file->filename);

        $attributes = TikiLib::lib('attribute')->get_attributes('file', $origFile->fileId);
        if ($url = $attributes['tiki.content.url']) {
            $this->attach_file_source($id, $url, $file->getParams(), true);
        }

        return $id;
    }

    public function change_file_handler($mime_type, $cmd)
    {
        $handlers = $this->table('tiki_file_handlers');

        $mime_type = trim($mime_type);

        $handlers->delete(['mime_type' => $mime_type]);
        $handlers->insert(['mime_type' => $mime_type, 'cmd' => $cmd]);

        return true;
    }

    public function delete_file_handler($mime_type)
    {
        $handlers = $this->table('tiki_file_handlers');
        return (bool) $handlers->delete(['mime_type' => $mime_type]);
    }

    public function get_native_handler($type)
    {
        switch ($type) {
            case 'text/plain':
                return function (FileWrapper $wrapper) {
                    return $wrapper->getContents();
                };
            case 'application/pdf':
                return function (FileWrapper $wrapper) {
                    include_once "vendor_bundled/vendor/christian-vigh-phpclasses/PdfToText/PdfToText.phpclass";
                    ob_start();
                    $pdf = new PdfToText($wrapper->getReadableFile());
                    ob_end_clean();
                    return $pdf->Text;
                };
        }
    }

    public function get_file_handlers($for_execution = false)
    {
        $cachelib = TikiLib::lib('cache');

        if ($for_execution && ! $default = $cachelib->getSerialized('file_handlers')) {
            // n.b. this array is partially duplicated in tiki-check.php for standalone mode checks
            $possibilities = [
                'application/ms-excel' => ['xls2csv %1'],
                'application/msexcel' => ['xls2csv %1'],
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => ['xlsx2csv.py %1'],
                'application/ms-powerpoint' => ['catppt %1'],
                'application/mspowerpoint' => ['catppt %1'],
                'application/vnd.openxmlformats-officedocument.presentationml.presentation' => ['pptx2txt.pl %1 -'],
                'application/msword' => ['catdoc %1', 'strings %1'],
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => ['docx2txt.pl %1 -'],
                'application/pdf' => ['pstotext %1', 'pdftotext %1 -'],
                'application/postscript' => ['pstotext %1'],
                'application/ps' => ['pstotext %1'],
                'application/rtf' => ['catdoc %1'],
                'application/sgml' => ['col -b %1', 'strings %1'],
                'application/vnd.ms-excel' => ['xls2csv %1'],
                'application/vnd.ms-powerpoint' => ['catppt %1'],
                'application/x-msexcel' => ['xls2csv %1'],
                'application/x-pdf' => ['pstotext %1', 'pdftotext %1 -'],
                'application/x-troff-man' => ['man -l %1'],
                'application/zip' => ['unzip -l %1'],
                'text/enriched' => ['col -b %1', 'strings %1'],
                'text/html' => ['elinks -dump -no-home %1'],
                'text/richtext' => ['col -b %1', 'strings %1'],
                'text/sgml' => ['col -b %1', 'strings %1'],
                'text/tab-separated-values' => ['col -b %1', 'strings %1'],
            ];

            $default = [];
            $executables = [];
            foreach ($possibilities as $type => $options) {
                foreach ($options as $opt) {
                    $optArray = explode(' ', $opt, 2);
                    $exec = reset($optArray);

                    if (! isset($executables[$exec])) {
                        $executables[$exec] = (bool) `which $exec`;
                    }

                    if ($executables[$exec]) {
                        $default[$type] = $opt;
                        break;
                    }
                }
            }

            $cachelib->cacheItem('file_handlers', serialize($default));
        } elseif (! $for_execution) {
            $default = [];
        }

        $handlers = $this->table('tiki_file_handlers');
        $database = $handlers->fetchMap('mime_type', 'cmd', []);

        return array_merge($default, $database);
    }

    public function reindex_all_files_for_search_text()
    {
        @ini_set('memory_limit', -1);
        $files = $this->table('tiki_files');
        $reindexFilesCount = 0;

        for ($offset = 0, $maxRecords = 10;; $offset += $maxRecords) {
            $rows = $files->fetchAll(['fileId', 'filename', 'filesize', 'filetype', 'data', 'path', 'galleryId'], ['archiveId' => 0], $maxRecords, $offset);
            if (empty($rows)) {
                break;
            }

            foreach ($rows as $row) {
                $file = new TikiFile($row);
                $search_text = $this->get_search_text_for_data($file);
                if ($search_text !== false) {
                    if ($files->update(['search_data' => $search_text], ['fileId' => $row['fileId']])) {
                        $reindexFilesCount++;
                    }
                }
            }
        }
        include_once("lib/search/refresh-functions.php");
        refresh_index('files');

        return $reindexFilesCount;
    }

    public function get_parse_app($type, $skipDefault = true)
    {
        static $fileParseApps;

        $partial = $type;

        if (false !== $p = strpos($partial, ';')) {
            $partial = substr($partial, 0, $p);
        }

        if ($handler = $this->get_native_handler($type)) {
            return $handler;
        }

        if ($handler = $this->get_native_handler($partial)) {
            return $handler;
        }

        if (! $fileParseApps) {
            $fileParseApps = $this->get_file_handlers(true);
        }

        if (isset($fileParseApps[$type])) {
            return $this->shellExecuteCallback($fileParseApps[$type]);
        } elseif (isset($fileParseApps[$partial])) {
            return $this->shellExecuteCallback($fileParseApps[$partial]);
        } elseif (! $skipDefault && isset($fileParseApps['default'])) {
            return $this->shellExecuteCallback($fileParseApps['default']);
        }
    }

    private function shellExecuteCallback($command)
    {
        if (! $command) {
            return function () {
                return '';
            };
        }

        return function (FileWrapper $wrapper) use ($command) {
            $tmpfname = $wrapper->getReadableFile();

            $cmd = str_replace('%1', escapeshellarg($tmpfname), $command);
            $handle = popen($cmd, "r");

            if ($handle !== false) {
                $contents = stream_get_contents($handle);
                fclose($handle);

                return $contents;
            }

            return false;
        };
    }

    public function get_search_text_for_data($file)
    {
        $parseApp = $this->get_parse_app($file->filetype);

        if (empty($parseApp)) {
            return '';
        }

        $wrapper = $file->getWrapper();
        try {
            $content = $parseApp($wrapper);
            // clean out any chars not suitable for storing in tiki_file.search_data which is a LONGTEXT column
            $content = filter_var($content, FILTER_UNSAFE_RAW, FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_STRIP_LOW);
        } catch (Exception $e) {
            Feedback::error(tr('Processing search text from a "%0" file in gallery #%1', $file->filetype, $file->galleryId) . '<br>'
                . $e->getMessage());
            $content = '';
        }
        return $content;
    }

    public function fix_vnd_ms_files()
    {
        $files = $this->table('tiki_files');
        $files->update(
            ['filetype' => $files->expr("REPLACE(`filetype`, 'application/vnd.ms-', 'application/ms')")],
            ['filetype' => $files->like('application/vnd.ms-%')]
        );
    }

    public function getGalleryDefinition($galleryId)
    {
        if (! isset($this->loadedGalleryDefinitions[$galleryId])) {
            $info = $this->get_file_gallery_info($galleryId);
            $this->loadedGalleryDefinitions[$galleryId] = new GalleryDefinition($info);
        }

        return $this->loadedGalleryDefinitions[$galleryId];
    }

    public function clearLoadedGalleryDefinitions()
    {
        $this->loadedGalleryDefinitions = [];
    }

    public function notify($galleryId, $name, $filename, $description, $action, $user, $fileId = false)
    {
        global $prefs;
        if ($prefs['feature_user_watches'] == 'y') {
                        //  Deal with mail notifications.
            include_once(__DIR__ . '/../notifications/notificationemaillib.php');
            $galleryName = $this->table('tiki_file_galleries')->fetchOne('name', ['galleryId' => $galleryId]);

            sendFileGalleryEmailNotification('file_gallery_changed', $galleryId, $galleryName, $name, $filename, $description, $action, $user, $fileId);
        }
    }
    /**
     * Lock a file
     *
     * @param $fileId
     * @param $user
     * @return TikiDb_Adodb_Result|TikiDb_Pdo_Result
     */
    public function lock_file($fileId, $user)
    {
        $result = $this->table('tiki_files')->update(['lockedby' => $user], ['fileId' => $fileId]);
        return $result;
    }
    /**
     * Unlock a file
     *
     * @param $fileId
     * @return TikiDb_Adodb_Result|TikiDb_Pdo_Result
     */
    public function unlock_file($fileId)
    {
        return $this->lock_file($fileId, null);
    }
    /* get archives of a file */
    public function get_archives($fileId, $offset = 0, $maxRecords = -1, $sort_mode = 'created_desc', $find = '')
    {
        return $this->get_files($offset, $maxRecords, $sort_mode, $find, $fileId, true, false, false, true, false, false, false, false, '', false, true);
    }
    public function duplicate_file_gallery($galleryId, $name, $description = '')
    {
        global $user;
        $info = $this->get_file_gallery_info($galleryId);
        $info['user'] = $user;
        $info['galleryId'] = 0;
        $info['description'] = $description;
        $info['name'] = $name;
        $newGalleryId = $this->replace_file_gallery($info);
        return $newGalleryId;
    }

    public function get_download_limit($fileId)
    {
        return $this->table('tiki_files')->fetchOne('maxhits', ['fileId' => $fileId]);
    }

    public function set_download_limit($fileId, $limit)
    {
        $this->table('tiki_files')->update(['maxhits' => (int) $limit], ['fileId' => (int) $fileId]);
    }
    // not the best optimisation as using a library using files and not content
    public function zip($fileIds, &$error, $zipName = '')
    {
        global $tiki_p_admin_file_galleries, $prefs, $user;
        $userlib = TikiLib::lib('user');
        $list = [];
        $temp = '/' . md5(\Laminas\Math\Rand::getBytes(10)) . '/';
        if (! mkdir(sys_get_temp_dir() . $temp)) {
            $temp = sys_get_temp_dir() . $temp;
        } elseif (mkdir('temp' . $temp)) {
            $temp = 'temp' . $temp;
        } else {
            $error = "Can not create directory $temp";
            return false;
        }
        $fileIds = array_unique($fileIds);
        Perms::bulk(['type' => 'file'], 'object', $fileIds);
        $filenames = [];
        $padding = strlen(count($fileIds)) - 1;
        foreach ($fileIds as $fileId) {
            $file = TikiFile::id($fileId);
            if ($tiki_p_admin_file_galleries == 'y' || $userlib->user_has_perm_on_object($user, $file->fileId, 'file', 'tiki_p_download_files')) {
                if (empty($zipName)) {
                    $zipName = $file->galleryId;
                }
                $filename = $file->filename;
                $counter = 1;
                while (in_array($filename, $filenames)) {
                    $filename = $file->filename . '_' . str_pad($counter, $padding, '0', STR_PAD_LEFT);
                    $counter++;
                }
                $filenames[] = $filename;
                $tmp = $temp . $filename;
                if (! copy($file->getWrapper()->getReadableFile(), $tmp)) {
                    $error = "Can not copy to $tmp";
                    return false;
                }
                $list[] = $tmp;
                $info = $file->getParams();
            }
        }
        if (empty($list)) {
            $error = "No permission";
            return null;
        }
        $info['filename'] = "$zipName.zip";
        $zip = $temp . $info['filename'];
        define(PCZLIB_SEPARATOR, '\001');
        if (! $archive = new PclZip($zip)) {
            $error = $archive->errorInfo(true);
            return false;
        }
        if (! ($v_list = $archive->create($list, PCLZIP_OPT_REMOVE_PATH, $temp))) {
            $error = $archive->errorInfo(true);
            return false;
        }
        $info['data'] = file_get_contents($zip);
        $info['path'] = '';
        $info['filetype'] = 'application/x-zip-compressed';
        $info['filesize'] = strlen($info['data']);
        foreach ($list as $tmp) {
            unlink($tmp);
        }
        unlink($zip);
        rmdir($temp);
        return $info;
    }

    /**
     * Return a flat list with the gallery id and the parent id, keeping a cached version
     *
     * @return array
     */
    public function getGalleriesParentIds($skip_direct = false)
    {
        if (self::$getGalleriesParentIdsCache === null) {
            self::$getGalleriesParentIdsCache = [null, null];
        }
        if (self::$getGalleriesParentIdsCache[intval($skip_direct)] === null) {
            $conditions = [];
            if ($skip_direct) {
                $conditions['type'] = $this->table('tiki_file_galleries')->expr('$ != "direct"');
            }
            self::$getGalleriesParentIdsCache[intval($skip_direct)] = $this->table('tiki_file_galleries')->fetchAll(['galleryId', 'parentId'], []);
        }

        return self::$getGalleriesParentIdsCache[intval($skip_direct)];
    }

    /**
     * Enables to clear the cache for the gallery parent ids
     *
     * @return void
     */
    public function cleanGalleriesParentIdsCache()
    {
        self::$getGalleriesParentIdsCache = null;
    }

    /**
     * Recursively returns all ids of the children of the specifified parent gallery
     * as a linear array (list).
     *
     * @param Array $allIds All ids of the Gallery
     * @param Array &$subtree Output - The children Ids are appended
     * @param int $parentId The parent whichs children are to be listed
     */
    protected function getGalleryChildrenIdsList($allIds, &$subtree, $parentId)
    {
        if (empty($allIds[$parentId])) {
            return;
        }

        foreach ($allIds[$parentId] as $child) {
            $galleryId = $child;
            $subtree[] = (int)$galleryId;
            $this->getGalleryChildrenIdsList($allIds, $subtree, $galleryId);
        }
    }

    /**
     * Recursively returns all Ids of the Children of the specifified parent gallery
     * as a tree-array (sub-galleries are array as an element of the parent array).
     * Thus the structure of the child galleries are preserved.
     *
     * @param Array $allIds All ids of the Gallery
     * @param Array &$subtree Output - The children Ids are appended
     * @param int $parentId The parent whichs children are to be listed
     */
    protected function getGalleryChildrenIdsTree($allIds, &$subtree, $parentId)
    {
        if (empty($allIds[$parentId])) {
            return;
        }

        foreach ($allIds[$parentId] as $child) {
            $galleryId = $child;
            $subtree[ (int)$galleryId ] = [];
            $this->getGalleryChildrenIdsTree($allIds, $subtree[$galleryId], $galleryId);
        }
    }
    // Get a tree or a list of a gallery children ids, optionnally under a specific parentId
    // To avoid a query to the database for each node, this function retrieves all gallery ids and recursively build the tree using this info
    public function getGalleryChildrenIds(&$subtree, $parentId = -1, $format = 'tree', $skip_direct = false)
    {
        $allIds = $this->getGalleriesParentIds($skip_direct);

        $allChildIds = [];
        foreach ($allIds as $v) {
            $allChildIds[$v['parentId']][] = $v['galleryId'];
        }

        switch ($format) {
            case 'list':
                $this->getGalleryChildrenIdsList($allChildIds, $subtree, $parentId);
                break;
            case 'tree':
            default:
                $this->getGalleryChildrenIdsTree($allChildIds, $subtree, $parentId);
        }
    }

    // Get a tree or a list of ids of the specified gallery and its children
    public function getGalleryIds(&$subtree, $parentId = -1, $format = 'tree', $skip_direct = false)
    {

        switch ($format) {
            case 'list':
                $subtree[] = $parentId;
                $childSubtree =& $subtree;
                break;
            case 'tree':
            default:
                $subtree[$parentId] = [];
                $childSubtree =& $subtree[$parentId];
        }

        return $this->getGalleryChildrenIds($childSubtree, $parentId, $format, $skip_direct);
    }

    /* Get the subgalleries of a gallery, the one identified by $parentId if $wholeSpecialGallery is false, or the special gallery containing the gallery identified by $parentId if $wholeSpecialGallery is true.
     *
     * @param int $parentId Identifier of a gallery
     * @param bool $wholeSpecialGallery If true, will return the subgalleries of the special gallery (User File Galleries, Wiki Attachment Galleries, File Galleries, ...) that contains the $parentId gallery
     * @param string $permission If set, will limit the list of subgalleries to those having this permission for the current user
     */
    public function getSubGalleries($parentId = 0, $wholeSpecialGallery = true, $permission = 'view_file_gallery', $skipDirect = false)
    {

        // Use the special File Galleries root if no other special gallery root id is specified
        if ($parentId == 0) {
            global $prefs;
            $parentId = $prefs['fgal_root_id'];
        }

        // If needed, get the id of the special gallery that contains the $parentId gallery
        if ($wholeSpecialGallery) {
            $parentId = $this->getGallerySpecialRoot($parentId);
            $useCache = true;
        }

        global $user;
        $cachelib = TikiLib::lib('cache');

        if ($useCache) {
            $cacheName = 'pid' . $parentId . '_' . $this->get_all_galleries_cache_name($user) . '_' . intval($skipDirect);
            $cacheType = $this->get_all_galleries_cache_type();
        }
        if (! $useCache || ! $return = $cachelib->getSerialized($cacheName, $cacheType)) {
            $return = $this->list_file_galleries(0, -1, 'name_asc', $user, '', $parentId, false, true, false, false, false, true, false, true, $skipDirect);
            if (is_array($return)) {
                $return['parentId'] = $parentId;
            }
            if ($useCache) {
                $cachelib->cacheItem($cacheName, serialize($return), $cacheType);
            }
        }

        if ($permission != '') {
            if (! is_array($return['data'])) {
                $return['data'] = [];
            }
            $return['data'] = Perms::filter(['type' => 'file gallery'], 'object', $return['data'], ['object' => 'id'], $permission);
        }

        return $return;
    }

    /**
     * Get the Id of the gallery special root, which will be a gallery of type 'special' with the parentId '-1'
     *    (i.e. 'File Galleries', 'Users File Galleries', ...)
     *
     * @param int $galleryId The id of the gallery
     * @return The special root gallery Id
     */
    // WARNING: Semi-private function. "Public callers" should only pass the galleryId parameter.
    public function getGallerySpecialRoot($galleryId, $treeParentId = null, &$tree = null /* Pass by reference for performance */)
    {
        global $prefs;

        if (( $treeParentId === null xor $tree === null ) || $galleryId <= 0) {
            // If parameters are not valid, return false (they should be null at first call and not empty when recursively called)
            return false;
        } elseif ($treeParentId === null) {
            // Initialize the full tree and the top root of all galleries
            $tree = [];
            $treeParentId = -1;
            $this->getGalleryChildrenIds($tree, $treeParentId, 'tree');
        } elseif ($treeParentId == $galleryId) {
            // If the searched gallery is the same as the current tree parent id, then return tree (we found the right branch of the tree)
            return true;
        }

        if (! empty($tree)) {
            foreach ($tree as $subGalleryId => $childs) {
                if ($result = $this->getGallerySpecialRoot($galleryId, $subGalleryId, $childs)) {
                    if (is_integer($result)) {
                        return $result;
                    } elseif ($treeParentId == -1) {
                        //
                        // If the parent is :
                        //   - either the User File Gallery, stop here to keep only the user gallery instead of all users galleries
                        //   - or already the top root of all galleries, it means that the gallery is a special gallery root
                        //
                        return (int)$subGalleryId;
                    } else {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    // Get the tree of 'Wiki Attachment File Galleries' filegal of the specified wiki page
    public function getWikiAttachmentFilegalsIdsTree($pageName)
    {
        $return = [];
        $this->getGalleryIds($return, $this->get_wiki_attachment_gallery($pageName), 'tree');
        return $return;
    }

    // Get the tree of 'Users File Galleries' filegal of the current user
    public function getUserFilegalsIdsTree()
    {
        $return = [];
        $this->getGalleryIds($return, $this->get_user_file_gallery(), 'tree');
        return $return;
    }

    // Get the tree of 'File Galleries' filegal
    public function getFilegalsIdsTree()
    {
        global $prefs;
        $return = [];
        $this->getGalleryIds($return, $prefs['fgal_root_id'], 'tree');
        return $return;
    }

    // Return HTML code to display the complete file galleries tree for the special root containing the given gallery.
    // If $galleryIdentifier is not given, default to the "default" / normal / "File Galleries" file galleries.
    public function getTreeHTML($galleryIdentifier = null)
    {
        global $prefs;
        $smarty = TikiLib::lib('smarty');
        require_once('lib/tree/BrowseTreeMaker.php');
        $galleryIdentifier = is_null($galleryIdentifier) ? $prefs['fgal_root_id'] : $galleryIdentifier;
        $subGalleries = $this->getSubGalleries($galleryIdentifier, true, 'view_file_gallery', true);

        $smarty->loadPlugin('smarty_function_icon');
        $icon = '&nbsp;' . smarty_function_icon(['name' => 'file-archive-open'], $smarty->getEmptyInternalTemplate()) . '&nbsp;';

        $smarty->loadPlugin('smarty_block_self_link');
        $linkParameters = ['_script' => 'tiki-list_file_gallery.php', '_class' => 'fgalname'];
        if (! empty($_REQUEST['filegals_manager'])) {
            $linkParameters['filegals_manager'] = $_REQUEST['filegals_manager'];
        }
        $nodes = [];
        foreach ($subGalleries['data'] as $subGallery) {
            $linkParameters['galleryId'] = $subGallery['id'];
            $nodes[] = [
                'id' => $subGallery['id'],
                'parent' => $subGallery['parentId'],
                'data' => smarty_block_self_link($linkParameters, $icon . htmlspecialchars($subGallery['name']), $smarty),
            ];
        }
        $browseTreeMaker = new BrowseTreeMaker('Galleries');
        return $browseTreeMaker->make_tree($this->getGallerySpecialRoot($galleryIdentifier), $nodes);
    }

    // Return the given gallery's path relative to its special root. The path starts with a constant component, File Galleries for default galleries.
    // It would be File Galleries > Foo for a root default file gallery named "Foo". Other constant components are "User File Galleries" and "Wiki Attachment File Galleries".
    // Returns an array with 2 elements, "Array" and "HTML".
    // Array is a numerically-indexed array with one element per path component. Each value is the name of the component (usually a file gallery name). Keys are file gallery OIDs.
    // HTML is a string of HTML code to display the path.
    public function getPath($galleryIdentifier)
    {
        global $prefs, $user;
        $rootIdentifier = $this->getGallerySpecialRoot($galleryIdentifier);
        $root = $this->get_file_gallery_info($galleryIdentifier);
        if ($user != '' && $prefs['feature_use_fgal_for_user_files'] == 'y') {
            $userGallery = $this->get_user_file_gallery();
            if ($userGallery == $prefs['fgal_root_user_id']) {
                $rootIdentifier = $userGallery;
            }
        }
        $path = [];
        for ($node = $this->get_file_gallery_info($galleryIdentifier); $node && $node['galleryId'] != $rootIdentifier; $node = $this->get_file_gallery_info($node['parentId'])) {
            $path[$node['galleryId']] = $node['name'];
        }
        if (isset($userGallery) && $rootIdentifier == $prefs['fgal_root_user_id']) {
            $path[$rootIdentifier] = tra('User File Galleries');
        } elseif ($rootIdentifier == $prefs['fgal_root_wiki_attachments_id']) {
            $path[$rootIdentifier] = tra('Wiki Attachment File Galleries');
        } else {
            $path[$rootIdentifier] = tra('File Galleries');
        }
        $path = array_reverse($path, true);

        $pathHtml = '';
        foreach ($path as $identifier => $name) {
            if ($pathHtml != '') {
                $pathHtml .= ' &nbsp;&gt;&nbsp;';
            }
            $pathHtml .= '<a href="tiki-list_file_gallery.php?galleryId=' . $identifier . (! empty($_REQUEST['filegals_manager']) ? '&amp;filegals_manager=' . urlencode($_REQUEST['filegals_manager']) : '') . '">' . htmlspecialchars($name) . '</a>';
        }

        return [
            'HTML' => $pathHtml,
            'Array' => $path
        ];
    }

    // get the size in k used in a fgal and its children
    public function getUsedSize($galleryId = 0)
    {
        $files = $this->table('tiki_files');

        $conditions = [];
        if (! empty($galleryId)) {
            $galleryIds = [];
            $this->getGalleryIds($galleryIds, $galleryId, 'list');

            $conditions['galleryId'] = $files->in($galleryIds);
        }

        return $files->fetchOne($files->sum('filesize'), $conditions);
    }

    // get the min quota in M of a fgal and its parents
    public function getQuota($galleryId = 0)
    {
        global $prefs;
        if (empty($galleryId) || $prefs['fgal_quota_per_fgal'] == 'n') {
            return $prefs['fgal_quota'];
        }
        $list = $this->getGalleryParentsColumns($galleryId, ['galleryId', 'quota']);
        $quota = $prefs['fgal_quota'];
        foreach ($list as $fgal) {
            if (empty($fgal['quota'])) {
                continue;
            }
            $quota = min($quota, $fgal['quota']);
        }
        return $quota;
    }

    /**
     * get the max quota in MB of the children of a fgal,
     * or total contents size where no quota is set
     *
     * @param int $galleryId
     * @return float
     */

    public function getMaxQuotaDescendants($galleryId = 0)
    {
        if (empty($galleryId)) {
            return 0;
        }
        $this->getGalleryChildrenIds($subtree, $galleryId, 'list');
        if (is_array($subtree) && ! empty($subtree)) {
            $files = $this->table('tiki_files');
            $gals = $this->table('tiki_file_galleries');
            $size = 0;
            foreach ($subtree as $sub