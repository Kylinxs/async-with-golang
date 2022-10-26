-- --------------------------------------------------------
-- Database : Tiki
-- --------------------------------------------------------

ALTER DATABASE DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
SET FOREIGN_KEY_CHECKS = 0;  /* tiki doesn't officially use foreign keys but sometimes they "appear", leading to table dropping errors */

DROP TABLE IF EXISTS `messu_messages`;
CREATE TABLE `messu_messages` (
  `msgId` int(14) NOT NULL auto_increment,
  `user` varchar(200) NOT NULL default '',
  `user_from` varchar(200) NOT NULL default '',
  `user_to` text,
  `user_cc` text,
  `user_bcc` text,
  `subject` varchar(255) default NULL,
  `body` text,
  `hash` varchar(32) default NULL,
  `replyto_hash` varchar(32) default NULL,
  `date` int(14) default NULL,
  `isRead` char(1) default NULL,
  `isReplied` char(1) default NULL,
  `isFlagged` char(1) default NULL,
  `priority` int(2) default NULL,
  PRIMARY KEY (`msgId`),
  KEY `userIsRead` (user(190), `isRead`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `messu_archive`;
CREATE TABLE `messu_archive` (
  `msgId` int(14) NOT NULL auto_increment,
  `user` varchar(40) NOT NULL default '',
  `user_from` varchar(40) NOT NULL default '',
  `user_to` text,
  `user_cc` text,
  `user_bcc` text,
  `subject` varchar(255) default NULL,
  `body` text,
  `hash` varchar(32) default NULL,
  `replyto_hash` varchar(32) default NULL,
  `date` int(14) default NULL,
  `isRead` char(1) default NULL,
  `isReplied` char(1) default NULL,
  `isFlagged` char(1) default NULL,
  `priority` int(2) default NULL,
  PRIMARY KEY (`msgId`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `messu_sent`;
CREATE TABLE `messu_sent` (
  `msgId` int(14) NOT NULL auto_increment,
  `user` varchar(40) NOT NULL default '',
  `user_from` varchar(40) NOT NULL default '',
  `user_to` text,
  `user_cc` text,
  `user_bcc` text,
  `subject` varchar(255) default NULL,
  `body` text,
  `hash` varchar(32) default NULL,
  `replyto_hash` varchar(32) default NULL,
  `date` int(14) default NULL,
  `isRead` char(1) default NULL,
  `isReplied` char(1) default NULL,
  `isFlagged` char(1) default NULL,
  `priority` int(2) default NULL,
  PRIMARY KEY (`msgId`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `sessions`;
CREATE TABLE `sessions` (
  `sesskey` char(32) NOT NULL,
  `expiry` int(11) unsigned NOT NULL,
  `expireref` varchar(64),
  `data` longblob NOT NULL,
  PRIMARY KEY (`sesskey`),
  KEY `expiry` (expiry)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `tiki_actionlog`;
CREATE TABLE `tiki_actionlog` (
  `actionId` int(8) NOT NULL auto_increment,
  `action` varchar(255) NOT NULL default '',
  `lastModif` int(14) default NULL,
  `object` varchar(255) default NULL,
  `objectType` varchar(32) NOT NULL default '',
  `user` varchar(200) default '',
  `ip` varchar(39) default NULL,
  `comment` text default NULL,
  `categId` int(12) NOT NULL default '0',
  `client` VARCHAR( 200 ) NULL DEFAULT NULL,
  `log` LONGTEXT NULL DEFAULT NULL,
  PRIMARY KEY (`actionId`),
  KEY `lastModif` (`lastModif`),
  KEY `object` (`object`(100), `objectType`, `action`(100)),
  KEY `actionforuser` (`user` (100), `objectType`, `action` (100))
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `tiki_actionlog_params`;
CREATE TABLE `tiki_actionlog_params` (
  `actionId` int(8) NOT NULL,
  `name` varchar(40) NOT NULL,
  `value` text,
  KEY `actionId` (`actionId`),
  KEY `nameValue` (`name`, `value`(151))
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `tiki_activity_stream`;
CREATE TABLE `tiki_activity_stream` (
  `activityId` int(8) NOT NULL auto_increment,
  `eventType` varchar(100) NOT NULL,
  `eventDate` int NOT NULL,
  `arguments` MEDIUMBLOB,
  PRIMARY KEY(`activityId`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `tiki_activity_stream_mapping`;
CREATE TABLE `tiki_activity_stream_mapping` (
  `field_name` varchar(50) NOT NULL,
  `field_type` varchar(15) NOT NULL,
  PRIMARY KEY(`field_name`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `tiki_activity_stream_rules`;
CREATE TABLE `tiki_activity_stream_rules` (
  `ruleId` int(8) NOT NULL auto_increment,
  `eventType` varchar(100) NOT NULL,
  `ruleType` varchar(20) NOT NULL,
  `rule` TEXT,
  `notes` TEXT,
  PRIMARY KEY(`ruleId`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `tiki_address_books`;
CREATE TABLE `tiki_address_books` (
    `addressBookId` INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `user` VARCHAR(200),
    `name` VARCHAR(255),
    `uri` VARBINARY(200),
    `description` TEXT,
    UNIQUE(`user`(141), `uri`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `tiki_address_cards`;
CREATE TABLE `tiki_address_cards` (
    `addressCardId` INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `addressBookId` INT UNSIGNED NOT NULL,
    `carddata` MEDIUMBLOB,
    `uri` VARBINARY(200),
    `lastmodified` INT(11) UNSIGNED,
    `etag` VARBINARY(32),
    `size` INT(11) UNSIGNED NOT NULL,
    INDEX(`addressBookId`, `uri`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `tiki_api_tokens`;
CREATE TABLE `tiki_api_tokens` (
  `tokenId` int(11) NOT NULL AUTO_INCREMENT,
  `type` VARCHAR(100) NOT NULL DEFAULT 'manual',
  `user` varchar(200) NULL DEFAULT NULL,
  `token` varchar(100) NOT NULL,
  `label` VARCHAR(191) NULL DEFAULT NULL,
  `parameters` TEXT NULL DEFAULT NULL,
  `created` int NOT NULL,
  `lastModif` int NOT NULL,
  `expireAfter` int NULL,
  `hits` int NOT NULL default 0,
  PRIMARY KEY (`tokenId`),
  KEY `token` (`token`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `tiki_articles`;
CREATE TABLE `tiki_articles` (
  `articleId` int(8) NOT NULL auto_increment,
  `topline` varchar(255) default NULL,
  `title` varchar(255) default NULL,
  `subtitle` varchar(255) default NULL,
  `linkto` varchar(255) default NULL,
  `lang` varchar(16) default NULL,
  `state` char(1) default 's',
  `authorName` varchar(60) default NULL,
  `topicId` int(14) default NULL,
  `topicName` varchar(40) default NULL,
  `size` int(12) default NULL,
  `useImage` char(1) default NULL,
  `image_name` varchar(80) default NULL,
  `image_caption` text default NULL,
  `image_type` varchar(80) default NULL,
  `image_size` int(14) default NULL,
  `image_x` int(4) default NULL,
  `image_y` int(4) default NULL,
  `list_image_x` int(4) default NULL,
  `list_image_y` int(4) default NULL,
  `image_data` longblob,
  `publishDate` int(14) default NULL,
  `expireDate` int(14) default NULL,
  `created` int(14) default NULL,
  `heading` text,
  `body` text,
  `author` varchar(200) default NULL,
  `nbreads` int(14) default NULL,
  `votes` int(8) default NULL,
  `points` int(14) default NULL,
  `type` varchar(50) default NULL,
  `rating` decimal(3,2) default NULL,
  `isfloat` char(1) default NULL,
  `ispublished` char(1) NOT NULL DEFAULT 'y',
  PRIMARY KEY (`articleId`),
  KEY `title` (`title` (191)),
  KEY `heading` (`heading`(191)),
  KEY `body` (`body`(191)),
  KEY `nbreads` (`nbreads`),
  KEY `author` (`author`(32)),
  KEY `topicId` (`topicId`),
  KEY `publishDate` (`publishDate`),
  KEY `expireDate` (`expireDate`),
  KEY `type` (`type`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `tiki_article_types`;
CREATE TABLE `tiki_article_types` (
  `type` varchar(50) NOT NULL,
  `use_ratings` varchar(1) default NULL,
  `show_pre_publ` varchar(1) default NULL,
  `show_post_expire` varchar(1) default 'y',
  `heading_only` varchar(1) default NULL,
  `allow_comments` varchar(1) default 'y',
  `show_image` varchar(1) default 'y',
  `show_avatar` varchar(1) default NULL,
  `show_author` varchar(1) default 'y',
  `show_pubdate` varchar(1) default 'y',
  `show_expdate` varchar(1) default NULL,
  `show_reads` varchar(1) default 'y',
  `show_size` varchar(1) default 'n',
  `show_topline` varchar(1) default 'n',
  `show_subtitle` varchar(1) default 'n',
  `show_linkto` varchar(1) default 'n',
  `show_image_caption` varchar(1) default 'n',
  `creator_edit` varchar(1) default NULL,
  `comment_can_rate_article` char(1) default NULL,
  PRIMARY KEY (`type`),
  KEY `show_pre_publ` (`show_pre_publ`),
  KEY `show_post_expire` (`show_post_expire`)
) ENGINE=MyISAM ;

INSERT IGNORE INTO tiki_article_types(type) VALUES ('Article');
INSERT IGNORE INTO tiki_article_types(type,use_ratings) VALUES ('Review','y');
INSERT IGNORE INTO tiki_article_types(type,show_post_expire) VALUES ('Event','n');
INSERT IGNORE INTO tiki_article_types(type,show_post_expire,heading_only,allow_comments) VALUES ('Classified','n','y','n');

DROP TABLE IF EXISTS `tiki_banners`;
CREATE TABLE `tiki_banners` (
  `bannerId` int(12) NOT NULL auto_increment,
  `client` varchar(200) NOT NULL default '',
  `url` varchar(255) default NULL,
  `title` varchar(255) default NULL,
  `alt` varchar(250) default NULL,
  `which` varchar(50) default NULL,
  `imageData` longblob,
  `imageType` varchar(200) default NULL,
  `imageName` varchar(100) default NULL,
  `HTMLData` text,
  `fixedURLData` varchar(255) default NULL,
  `textData` text,
  `fromDate` int(14) default NULL,
  `toDate` int(14) default NULL,
  `useDates` char(1) default NULL,
  `mon` char(1) default NULL,
  `tue` char(1) default NULL,
  `wed` char(1) default NULL,
  `thu` char(1) default NULL,
  `fri` char(1) default NULL,
  `sat` char(1) default NULL,
  `sun` char(1) default NULL,
  `hourFrom` varchar(4) default NULL,
  `hourTo` varchar(4) default NULL,
  `created` int(14) default NULL,
  `maxImpressions` int(8) default NULL,
  `impressions` int(8) default NULL,
  `maxUserImpressions` int(8) default -1,
  `maxClicks` int(8) default NULL,
  `clicks` int(8) default NULL,
  `zone` varchar(40) default NULL,
  `onlyInURIs` text,
  `exceptInURIs` text,
  PRIMARY KEY (`bannerId`),
  INDEX ban1(zone,`useDates`,impressions,`maxImpressions`,`hourFrom`,`hourTo`,`fromDate`,`toDate`,mon,tue,wed,thu,fri,sat,sun)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `tiki_banning`;
CREATE TABLE `tiki_banning` (
  `banId` int(12) NOT NULL auto_increment,
  `mode` enum('user','ip') default NULL,
  `title` varchar(200) default NULL,
  `ip1` char(3) default NULL,
  `ip2` char(3) default NULL,
  `ip3` char(3) default NULL,
  `ip4` char(3) default NULL,
  `user` varchar(200) default '',
  `date_from` timestamp NULL,
  `date_to` timestamp NULL,
  `use_dates` char(1) default NULL,
  `created` int(14) default NULL,
  `message` text,
  PRIMARY KEY (`banId`),
  INDEX ban(`use_dates`, `date_from`, `date_to`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `tiki_banning_sections`;
CREATE TABLE `tiki_banning_sections` (
  `banId` int(12) NOT NULL default '0',
  `section` varchar(100) NOT NULL default '',
  PRIMARY KEY (`banId`,`section`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `tiki_blog_activity`;
CREATE TABLE `tiki_blog_activity` (
  `blogId` int(8) NOT NULL default '0',
  `day` int(14) NOT NULL default '0',
  `posts` int(8) default NULL,
  PRIMARY KEY (`blogId`,`day`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `tiki_blog_posts`;
CREATE TABLE `tiki_blog_posts` (
  `postId` int(8) NOT NULL auto_increment,
  `blogId` int(8) NOT NULL default '0',
  `data` text,
  `data_size` int(11) unsigned NOT NULL default '0',
  `excerpt` text default NULL,
  `created` int(14) default NULL,
  `user` varchar(200) default '',
  `hits` bigint NULL default '0',
  `trackbacks_to` text,
  `trackbacks_from` text,
  `title` varchar(255) default NULL,
  `priv` varchar(1) default 'n',
  `wysiwyg` varchar(1) default NULL,
  PRIMARY KEY (`postId`),
  KEY `data` (`data`(191)),
  KEY `blogId` (`blogId`),
  KEY `created` (`created`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `tiki_blog_posts_images`;
CREATE TABLE `tiki_blog_posts_images` (
  `imgId` int(14) NOT NULL auto_increment,
  `postId` int(14) NOT NULL default '0',
  `filename` varchar(80) default NULL,
  `filetype` varchar(80) default NULL,
  `filesize` int(14) default NULL,
  `data` longblob,
  PRIMARY KEY (`imgId`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `tiki_blogs`;
CREATE TABLE `tiki_blogs` (
  `blogId` int(8) NOT NULL auto_increment,
  `created` int(14) default NULL,
  `lastModif` int(14) default NULL,
  `title` varchar(200) default NULL,
  `description` text,
  `user` varchar(200) default '',
  `public` char(1) default NULL,
  `posts` int(8) default NULL,
  `maxPosts` int(8) default NULL,
  `hits` int(8) default NULL,
  `activity` decimal(4,2) default NULL,
  `heading` text,
  `post_heading` text,
  `use_find` char(1) default NULL,
  `use_title` char(1) default 'y',
  `use_title_in_post` char(1) default 'y',
  `use_description` char(1) default 'y',
  `use_breadcrumbs` char(1) default 'n',
  `use_author` char(1) default NULL,
  `use_excerpt` char(1) default NULL,
  `add_date` char(1) default NULL,
  `add_poster` char(1) default NULL,
  `allow_comments` char(1) default NULL,
  `show_avatar` char(1) default NULL,
  `always_owner` char(1) default NULL,
  `show_related` char(1) default NULL,
  `related_max` int(4) default 5,
  PRIMARY KEY (`blogId`),
  KEY `title` (`title`(191)),
  KEY `description` (`description`(191)),
  KEY `hits` (`hits`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `tiki_calendar_categories`;
CREATE TABLE `tiki_calendar_categories` (
  `calcatId` int(11) NOT NULL auto_increment,
  `calendarId` int(14) NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  PRIMARY KEY (`calcatId`),
  UNIQUE KEY `catname` (`calendarId`, `name`(16))
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `tiki_calendar_recurrence`;
CREATE TABLE `tiki_calendar_recurrence` (
  `recurrenceId` int(14) NOT NULL auto_increment,
  `calendarId` int(14) NOT NULL default '0',
  `start` int(4) NOT NULL default '0',
  `end` int(4) NOT NULL default '2359',
  `allday` tinyint(1) NOT NULL default '0',
  `locationId` int(14) default NULL,
  `categoryId` int(14) default NULL,
  `nlId` int(12) NOT NULL default '0',
  `priority` enum('1','2','3','4','5','6','7','8','9') NOT NULL default '1',
  `status` enum('0','1','2') NOT NULL default '0',
  `url` varchar(255) default NULL,
  `lang` char(16) NOT NULL default 'en',
  `name` varchar(255) NOT NULL default '',
  `description` blob,
  `weekly` tinyint(1) default '0',
  `weekdays` VARCHAR(20) DEFAULT NULL,
  `monthly` tinyint(1) default '0',
  `dayOfMonth` int(2),
  `yearly` tinyint(1) default '0',
  `dateOfYear` int(4),
  `nbRecurrences` int(8),
  `startPeriod` int(14),
  `endPeriod` int(14),
  `user` varchar(200) default '',
  `created` int(14) NOT NULL default '0',
  `lastmodif` int(14) NOT NULL default '0',
  `uid` varchar(200),
  `uri` varchar(200),
  PRIMARY KEY (`recurrenceId`),
  KEY `calendarId` (`calendarId`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `tiki_calendar_items`;
CREATE TABLE `tiki_calendar_items` (
  `calitemId` int(14) NOT NULL auto_increment,
  `calendarId` int(14) NOT NULL default '0',
  `start` int(14) NOT NULL default '0',
  `end` int(14) NOT NULL default '0',
  `locationId` int(14) default NULL,
  `categoryId` int(14) default NULL,
  `nlId` int(12) NOT NULL default '0',
  `priority` enum('0', '1','2','3','4','5','6','7','8','9') default '0',
  `status` enum('0','1','2') NOT NULL default '0',
  `url` varchar(255) default NULL,
  `lang` char(16) NOT NULL default 'en',
  `name` varchar(255) NOT NULL default '',
  `description` text,
  `recurrenceId` int(14),
  `changed` tinyint(1) DEFAULT '0',
  `recurrenceStart` int(14) default NULL,
  `user` varchar(200) default '',
  `created` int(14) NOT NULL default '0',
  `lastmodif` int(14) NOT NULL default '0',
  `allday` tinyint(1) NOT NULL default '0',
  `uid` varchar(200),
  `uri` varchar(200),
  PRIMARY KEY (`calitemId`),
  KEY `calendarId` (`calendarId`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `tiki_calendar_locations`;
CREATE TABLE `tiki_calendar_locations` (
  `callocId` int(14) NOT NULL auto_increment,
  `calendarId` int(14) NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `description` blob,
  PRIMARY KEY (`callocId`),
  UNIQUE KEY `locname` (`calendarId`, `name`(16))
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `tiki_calendar_roles`;
CREATE TABLE `tiki_calendar_roles` (
  `calitemId` int(14) NOT NULL default '0',
  `username` varchar(200) NOT NULL default '',
  `role` enum('0','1','2','3','6') NOT NULL default '0',
  `partstat` VARCHAR(30) NULL DEFAULT NULL,
  PRIMARY KEY (`calitemId`,`username`(16),`role`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `tiki_calendars`;
CREATE TABLE `tiki_calendars` (
  `calendarId` int(14) NOT NULL auto_increment,
  `name` varchar(80) NOT NULL default '',
  `description` varchar(255) default NULL,
  `user` varchar(200) NOT NULL default '',
  `customlocations` enum('n','y') NOT NULL default 'n',
  `customcategories` enum('n','y') NOT NULL default 'n',
  `customlanguages` enum('n','y') NOT NULL default 'n',
  `custompriorities` enum('n','y') NOT NULL default 'n',
  `customparticipants` enum('n','y') NOT NULL default 'n',
  `customsubscription` enum('n','y') NOT NULL default 'n',
  `customstatus` enum('n','y') NOT NULL default 'y',
  `created` int(14) NOT NULL default '0',
  `lastmodif` int(14) NOT NULL default '0',
  `personal` enum ('n', 'y') NOT NULL default 'n',
  PRIMARY KEY (`calendarId`)
) ENGINE=MyISAM ;

DROP TABLE IF EXISTS `tiki_calendar_changes`;
CREATE TABLE `tiki_calendar_changes` (
    changeId INT(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    calitemId INT(11) UNSIGNED NOT NULL,
    synctoken INT(11) UNSIGNED NOT NULL,
    calendarId INT(11) UNSIGNED NOT NULL,
    operation TINYINT(1) NOT NULL,
    INDEX (calendarId, synctoken),
    INDEX (calitemId)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `tiki_calendar_instances`;
CREATE TABLE `tiki_calendar_instances` (
    calendarInstanceId INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    calendarId INT UNSIGNED NOT NULL,
    user VARCHAR(200),
    access TINYINT(1) NOT NULL DEFAULT '1' COMMENT '1 = owner, 2 = read, 3 = readwrite',
    name VARCHAR(100),
    uri VARBINARY(200),
    description TEXT,
    `order` INT(11) UNSIGNED NOT NULL DEFAULT '0',
    color VARBINARY(10),
    timezone TEXT,
    transparent TINYINT(1) NOT NULL DEFAULT '0',
    share_href VARBINARY(100),
    share_name VARCHAR(100),
    share_invite_status TINYINT(1) NOT NULL DEFAULT '2' COMMENT '1 = noresponse, 2 = accepted, 3 = declined, 4 = invalid',
    UNIQUE(user(141), uri),
    UNIQUE(calendarid, user(189)),
    UNIQUE(calendarid, share_href)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `tiki_calendar_options`;
CREATE TABLE `tiki_calendar_options` (
    `calendarId` int(14) NOT NULL default 0,
    `optionName` varchar(120) NOT NULL default '',
    `value` varchar(255),
    PRIMARY KEY (`calendarId`,`optionName`)
) ENGINE=MyISAM ;

DROP TABLE IF EXISTS `tiki_calendar_scheduling_objects`;
CREATE TABLE `tiki_calendar_scheduling_objects` (
    schedulingObjectId INT(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    user VARCHAR(200),
    calendardata MEDIUMBLOB,
    uri VARBINARY(200),
    lastmodif INT(11) UNSIGNED,
    etag VARBINARY(32),
    size INT(11) UNSIGNED NOT NULL
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `tiki_calendar_subscriptions`;
CREATE TABLE `tiki_calendar_subscriptions` (
    subscriptionId INT(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    calendarId INT(11) UNSIGNED NOT NULL,
    user VARCHAR(200) NOT NULL,
    source TEXT,
    name VARCHAR(100),
    refresh_rate VARCHAR(10),
    `order` INT(11) UNSIGNED NOT NULL DEFAULT '0',
    color VARBINARY(10),
    strip_todos TINYINT(1) NULL,
    strip_alarms TINYINT(1) NULL,
    strip_attachments TINYINT(1) NULL,
    lastmodif INT(11) UNSIGNED,
    UNIQUE(user(189), calendarId)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `tiki_categories`;
CREATE TABLE `tiki_categories` (
  `categId` int(12) NOT NULL auto_increment,
  `name` varchar(200) default NULL,
  `description` varchar(500) default NULL,
  `parentId` int(12) default NULL,
  `rootId` int NOT NULL DEFAULT 0,
  `hits` int(8) default NULL,
  `tplGroupContainerId` int(12) default NULL,
  `tplGroupPattern` varchar(200) default NULL,
  PRIMARY KEY (`categId`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `tiki_categories_roles`;
CREATE TABLE `tiki_categories_roles` (
    `categId` int(12) NOT NULL,
    `categRoleId` int(12) NOT NULL,
    `groupRoleId` int(12) NOT NULL,
    `groupId` int(12) NOT NULL,
    PRIMARY KEY (`categId`,`categRoleId`,`groupRoleId`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `tiki_categories_roles_available`;
CREATE TABLE `tiki_categories_roles_available` (
    `categId` int(12) NOT NULL,
    `categRoleId` int(12) NOT NULL,
    PRIMARY KEY (