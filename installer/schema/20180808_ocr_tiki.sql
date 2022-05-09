
ALTER TABLE `tiki_files` ADD `ocr_state` TINYINT(1) AFTER `deleteAfter`, ADD `ocr_lang` VARCHAR(11) NULL, ADD `ocr_data` MEDIUMTEXT NULL AFTER `ocr_state`;