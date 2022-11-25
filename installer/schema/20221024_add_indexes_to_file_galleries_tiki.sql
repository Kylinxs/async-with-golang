ALTER TABLE `tiki_file_galleries` ADD INDEX `parentIdAndName` (`parentId`, `name`);
ALTER TABLE `tiki_files` DROP INDEX `galleryId`;
ALTER TABLE `tiki_files` ADD INDEX `galleryIdAndPath` (`galleryId`, `path`(188));
