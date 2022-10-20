ALTER TABLE `tiki_file_galleries` ADD COLUMN `ocr_lang` VARCHAR(255) default NULL;
ALTER TABLE `tiki_files` MODIFY COLUMN `ocr_lang` VARCHAR(255) default NULL;
