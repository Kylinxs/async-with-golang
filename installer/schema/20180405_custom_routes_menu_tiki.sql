
DELETE FROM tiki_menu_options WHERE `menuId`=42 AND `name` = 'Custom Routes' AND `url` = 'tiki-admin_routes.php';
INSERT INTO tiki_menu_options (`menuId`,`type`,`name`,`url`,`position`,`section`,`perm`,`groupname`) VALUES (42,'o','Custom Routes','tiki-admin_routes.php',1290,'feature_sefurl_routes','tiki_p_admin','');