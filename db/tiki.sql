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
    PRIMARY KEY (`categId`,`categRoleId`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `tiki_objects`;
CREATE TABLE `tiki_objects` (
  `objectId` int(12) NOT NULL auto_increment,
  `type` varchar(50) default NULL,
  `itemId` varchar(255) default NULL,
  `description` text,
  `created` int(14) default NULL,
  `name` varchar(200) default NULL,
  `href` varchar(256) default NULL,
  `hits` int(8) default NULL,
  `comments_locked` char(1) NOT NULL default 'n',
  PRIMARY KEY (`objectId`),
  KEY (`type`, `objectId`),
  KEY (`itemId`(141), `type`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `tiki_categorized_objects`;
CREATE TABLE `tiki_categorized_objects` (
  `catObjectId` int(11) NOT NULL default '0',
  PRIMARY KEY (`catObjectId`)
) ENGINE=MyISAM ;

DROP TABLE IF EXISTS `tiki_category_objects`;
CREATE TABLE `tiki_category_objects` (
  `catObjectId` int(12) NOT NULL default '0',
  `categId` int(12) NOT NULL default '0',
  PRIMARY KEY (`catObjectId`,`categId`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `tiki_object_ratings`;
CREATE TABLE `tiki_object_ratings` (
  `catObjectId` int(12) NOT NULL default '0',
  `pollId` int(12) NOT NULL default '0',
  PRIMARY KEY (`catObjectId`,`pollId`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `tiki_category_sites`;
CREATE TABLE `tiki_category_sites` (
  `categId` int(10) NOT NULL default '0',
  `siteId` int(14) NOT NULL default '0',
  PRIMARY KEY (`categId`,`siteId`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `tiki_chat_channels`;
CREATE TABLE `tiki_chat_channels` (
  `channelId` int(8) NOT NULL auto_increment,
  `name` varchar(30) default NULL,
  `description` varchar(250) default NULL,
  `max_users` int(8) default NULL,
  `mode` char(1) default NULL,
  `moderator` varchar(200) default NULL,
  `active` char(1) default NULL,
  `refresh` int(6) default NULL,
  PRIMARY KEY (`channelId`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `tiki_chat_messages`;
CREATE TABLE `tiki_chat_messages` (
  `messageId` int(8) NOT NULL auto_increment,
  `channelId` int(8) NOT NULL default '0',
  `data` varchar(255) default NULL,
  `poster` varchar(200) NOT NULL default 'anonymous',
  `timestamp` int(14) default NULL,
  PRIMARY KEY (`messageId`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `tiki_chat_users`;
CREATE TABLE `tiki_chat_users` (
  `nickname` varchar(200) NOT NULL default '',
  `channelId` int(8) NOT NULL default '0',
  `timestamp` int(14) default NULL,
  PRIMARY KEY (`nickname`(183),`channelId`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `tiki_comments`;
CREATE TABLE `tiki_comments` (
  `threadId` int(14) NOT NULL auto_increment,
  `object` varchar(255) NOT NULL default '',
  `objectType` varchar(32) NOT NULL default '',
  `parentId` int(14) default NULL,
  `userName` varchar(200) default '',
  `commentDate` int(14) default NULL,
  `hits` int(8) default NULL,
  `type` char(1) default NULL,
  `points` decimal(8,2) default NULL,
  `votes` int(8) default NULL,
  `average` decimal(8,4) default NULL,
  `title` varchar(255) default NULL,
  `data` text,
  `email` varchar(200) default NULL,
  `website` varchar(200) default NULL,
  `user_ip` varchar(39) default NULL,
  `summary` varchar(240) default NULL,
  `smiley` varchar(80) default NULL,
  `message_id` varchar(128) default NULL,
  `in_reply_to` varchar(128) default NULL,
  `comment_rating` tinyint(2) default NULL,
  `archived` char(1) default NULL,
  `approved` char(1) NOT NULL default 'y',
  `locked` char(1) NOT NULL default 'n',
  PRIMARY KEY (`threadId`),
  UNIQUE KEY `no_repeats` (`parentId`, `userName`(40), `title`(43), `commentDate`, `message_id`(40), `in_reply_to`(40)),
  KEY `title` (`title`(191)),
  KEY `data` (`data`(191)),
  KEY `hits` (hits),
  KEY `tc_pi` (`parentId`),
  KEY `objectType` (object(160), `objectType`),
  KEY `commentDate` (`commentDate`),
  KEY `threaded` (message_id(89), in_reply_to(88), `parentId`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `tiki_content`;
CREATE TABLE `tiki_content` (
  `contentId` int(8) NOT NULL auto_increment,
  `description` text,
  `contentLabel` varchar(255) NOT NULL default '',
  PRIMARY KEY (`contentId`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `tiki_content_templates`;
CREATE TABLE `tiki_content_templates` (
  `templateId` int(10) NOT NULL auto_increment,
  `template_type` VARCHAR( 20 ) NOT NULL DEFAULT 'static',
  `content` longblob,
  `name` varchar(200) default NULL,
  `created` int(14) default NULL,
  PRIMARY KEY (`templateId`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `tiki_content_templates_sections`;
CREATE TABLE `tiki_content_templates_sections` (
  `templateId` int(10) NOT NULL default '0',
  `section` varchar(250) NOT NULL default '',
  PRIMARY KEY (`templateId`,`section`(181))
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `tiki_cookies`;
CREATE TABLE `tiki_cookies` (
  `cookieId` int(10) NOT NULL auto_increment,
  `cookie` text,
  PRIMARY KEY (`cookieId`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `tiki_copyrights`;
CREATE TABLE `tiki_copyrights` (
  `copyrightId` int(12) NOT NULL auto_increment,
  `page` varchar(200) default NULL,
  `title` varchar(200) default NULL,
  `year` int(11) default NULL,
  `authors` varchar(200) default NULL,
  `holder` varchar(200) default NULL,
  `copyright_order` int(11) default NULL,
  `userName` varchar(200) default '',
  PRIMARY KEY (`copyrightId`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `tiki_custom_route`;
CREATE TABLE `tiki_custom_route` (
  `id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `description` varchar(255) NULL,
  `type` varchar(255) NOT NULL,
  `from` varchar(255) NOT NULL,
  `redirect` text NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `short_url` tinyint(1) DEFAULT '0'
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `tiki_directory_categories`;
CREATE TABLE `tiki_directory_categories` (
  `categId` int(10) NOT NULL auto_increment,
  `parent` int(10) default NULL,
  `name` varchar(240) default NULL,
  `description` text,
  `childrenType` char(1) default NULL,
  `sites` int(10) default NULL,
  `viewableChildren` int(4) default NULL,
  `allowSites` char(1) default NULL,
  `showCount` char(1) default NULL,
  `editorGroup` varchar(200) default NULL,
  `hits` int(12) default NULL,
  PRIMARY KEY (`categId`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `tiki_directory_search`;
CREATE TABLE `tiki_directory_search` (
  `term` varchar(250) NOT NULL default '',
  `hits` int(14) default NULL,
  PRIMARY KEY (`term`(191))
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `tiki_directory_sites`;
CREATE TABLE `tiki_directory_sites` (
  `siteId` int(14) NOT NULL auto_increment,
  `name` varchar(240) default NULL,
  `description` text,
  `url` varchar(255) default NULL,
  `country` varchar(255) default NULL,
  `hits` int(12) default NULL,
  `isValid` char(1) default NULL,
  `created` int(14) default NULL,
  `lastModif` int(14) default NULL,
  `cache` longblob,
  `cache_timestamp` int(14) default NULL,
  PRIMARY KEY (`siteId`),
  KEY (`isValid`),
  KEY (url(191))
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `tiki_dsn`;
CREATE TABLE `tiki_dsn` (
  `dsnId` int(12) NOT NULL auto_increment,
  `name` varchar(200) NOT NULL default '',
  `dsn` varchar(255) default NULL,
  PRIMARY KEY (`dsnId`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `tiki_dynamic_variables`;
CREATE TABLE `tiki_dynamic_variables` (
  `name` varchar(40) NOT NULL,
  `data` text,
  `lang` VARCHAR(16) NULL
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `tiki_encryption_keys`;
CREATE TABLE `tiki_encryption_keys` (
  `keyId` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL,
  `description` text NULL,
  `algo` varchar(50) NULL,
  `shares` int(11) NOT NULL,
  `users` text NULL,
  `secret` varchar(191) NOT NULL,
  PRIMARY KEY  (`keyId`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `tiki_extwiki`;
CREATE TABLE `tiki_extwiki` (
  `extwikiId` int(12) NOT NULL auto_increment,
  `name` varchar(200) NOT NULL default '',
  `extwiki` varchar(255) default NULL,
  `indexname` varchar(255) default NULL,
  `groups` varchar(1024) default NULL,
  PRIMARY KEY (`extwikiId`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `tiki_faq_questions`;
CREATE TABLE `tiki_faq_questions` (
  `questionId` int(10) NOT NULL auto_increment,
  `faqId` int(10) default NULL,
  `position` int(4) default NULL,
  `question` text,
  `answer` text,
  `created` int(14) default NULL,
  PRIMARY KEY (`questionId`),
  KEY `faqId` (`faqId`),
  KEY `question` (question(191)),
  KEY `answer` (answer(191)),
  KEY `created` (`created`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `tiki_faqs`;
CREATE TABLE `tiki_faqs` (
  `faqId` int(10) NOT NULL auto_increment,
  `title` varchar(200) default NULL,
  `description` text,
  `created` int(14) default NULL,
  `questions` int(5) default NULL,
  `hits` int(8) default NULL,
  `canSuggest` char(1) default NULL,
  PRIMARY KEY (`faqId`),
  KEY `title` (title(191)),
  KEY `description` (description(191)),
  KEY `hits` (hits)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `tiki_featured_links`;
CREATE TABLE `tiki_featured_links` (
  `url` varchar(200) NOT NULL default '',
  `title` varchar(200) default NULL,
  `description` text,
  `hits` int(8) default NULL,
  `position` int(6) default NULL,
  `type` char(1) default NULL,
  PRIMARY KEY (`url`(191))
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `tiki_file_galleries`;
CREATE TABLE `tiki_file_galleries` (
  `galleryId` int(14) NOT NULL auto_increment,
  `name` varchar(80) NOT NULL default '',
  `type` varchar(20) NOT NULL default 'default',
  `direct` text,
  `template` int(10) default NULL,
  `description` text,
  `created` int(14) default NULL,
  `visible` char(1) default NULL,
  `lastModif` int(14) default NULL,
  `user` varchar(200) default '',
  `hits` int(14) default NULL,
  `votes` int(8) default NULL,
  `points` decimal(8,2) default NULL,
  `maxRows` int(10) default NULL,
  `public` char(1) default NULL,
  `show_id` char(1) default NULL,
  `show_icon` char(1) default NULL,
  `show_name` char(1) default NULL,
  `show_size` char(1) default NULL,
  `show_description` char(1) default NULL,
  `max_desc` int(8) default NULL,
  `show_created` char(1) default NULL,
  `show_hits` char(1) default NULL,
  `show_lastDownload` char(1) default NULL,
  `parentId` int(14) NOT NULL default -1,
  `lockable` char(1) default 'n',
  `show_lockedby` char(1) default NULL,
  `archives` int(4) default 0,
  `sort_mode` char(20) default NULL,
  `show_modified` char(1) default NULL,
  `show_author` char(1) default NULL,
  `show_creator` char(1) default NULL,
  `subgal_conf` varchar(200) default NULL,
  `show_last_user` char(1) default NULL,
  `show_comment` char(1) default NULL,
  `show_files` char(1) default NULL,
  `show_explorer` char(1) default NULL,
  `show_path` char(1) default NULL,
  `show_slideshow` char(1) default NULL,
  `show_ocr_state` char(1) default NULL,
  `default_view` varchar(20) default NULL,
  `quota` int(8) default 0,
  `size` int(14) default NULL,
  `wiki_syntax` varchar(200) default NULL,
  `backlinkPerms` char(1) default 'n',
  `show_backlinks` char(1) default NULL,
  `show_deleteAfter` char(1) default NULL,
  `show_checked` char(1) default NULL,
  `show_share` char(1) default NULL,
  `image_max_size_x` int(8) NOT NULL default '0',
  `image_max_size_y` int(8) NOT NULL default '0',
  `show_source` char(1) NOT NULL DEFAULT 'o',
  `icon_fileId` int(14) UNSIGNED NULL DEFAULT NULL,
  `ocr_lang` VARCHAR(255) default NULL,
  PRIMARY KEY (`galleryId`),
  KEY `parentIdAndName` (`parentId`, name)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

INSERT INTO `tiki_file_galleries` (`galleryId`, `name`, `type`, `description`, `visible`, `user`, `public`, `parentId`) VALUES ('1','File Galleries', 'system', '', 'y', 'admin', 'y', -1);
INSERT INTO `tiki_file_galleries` (`galleryId`, `name`, `type`, `description`, `visible`, `user`, `public`, `parentId`) VALUES ('2','Users File Galleries', 'system', '', 'y', 'admin', 'y', -1);
INSERT INTO `tiki_file_galleries` (`galleryId`, `name`, `type`, `description`, `visible`, `user`, `public`, `parentId`) VALUES ('3','Wiki Attachments', 'system', '', 'y', 'admin', 'y', -1);


DROP TABLE IF EXISTS `tiki_files`;
CREATE TABLE `tiki_files` (
  `fileId` int(14) NOT NULL auto_increment,
  `galleryId` int(14) NOT NULL default '0',
  `name` varchar(200) NOT NULL default '',
  `description` text,
  `created` int(14) default NULL,
  `filename` varchar(80) default NULL,
  `filesize` int(14) default NULL,
  `filetype` varchar(250) default NULL,
  `data` longblob,
  `user` varchar(200) default '',
  `author` varchar(40) default NULL,
  `hits` int(14) default NULL,
  `maxhits` INT( 14 ) default NULL,
  `lastDownload` int(14) default NULL,
  `votes` int(8) default NULL,
  `points` decimal(8,2) default NULL,
  `path` varchar(255) default NULL,
  `reference_url` varchar(250) default NULL,
  `is_reference` char(1) default NULL,
  `hash` varchar(32) default NULL,
  `search_data` longtext,
  `metadata` longtext,
  `lastModif` integer(14) DEFAULT NULL,
  `lastModifUser` varchar(200) DEFAULT NULL,
  `lockedby` varchar(200) default '',
  `comment` varchar(200) default NULL,
  `archiveId` int(14) default 0,
  `deleteAfter` int(14) default NULL,
  `ocr_state` TINYINT(1) default NULL,
  `ocr_lang` VARCHAR(255) default NULL,
  `ocr_data` MEDIUMTEXT default NULL,
  PRIMARY KEY (`fileId`),
  KEY `name` (name(191)),
  KEY `description` (description(191)),
  KEY `created` (created),
  KEY `archiveId` (`archiveId`),
  KEY `galleryIdAndPath` (`galleryId`, `path`(188)),
  KEY `hits` (hits)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `tiki_file_drafts`;
CREATE TABLE `tiki_file_drafts` (
  `fileId` int(14) NOT NULL,
  `filename` varchar(80) default NULL,
  `filesize` int(14) default NULL,
  `filetype` varchar(250) default NULL,
  `data` longblob,
  `user` varchar(200) default '',
  `path` varchar(255) default NULL,
  `hash` varchar(32) default NULL,
  `metadata` longtext,
  `lastModif` integer(14) DEFAULT NULL,
  `lockedby` varchar(200) default '',
  PRIMARY KEY (`fileId`, `user`(177))
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `tiki_forum_attachments`;
CREATE TABLE `tiki_forum_attachments` (
  `attId` int(14) NOT NULL auto_increment,
  `threadId` int(14) NOT NULL default '0',
  `qId` int(14) NOT NULL default '0',
  `forumId` int(14) default NULL,
  `filename` varchar(250) default NULL,
  `filetype` varchar(250) default NULL,
  `filesize` int(12) default NULL,
  `data` longblob,
  `dir` varchar(200) default NULL,
  `created` int(14) default NULL,
  `path` varchar(250) default NULL,
  PRIMARY KEY (`attId`),
  KEY `threadId` (`threadId`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `tiki_forum_reads`;
CREATE TABLE `tiki_forum_reads` (
  `user` varchar(200) NOT NULL default '',
  `threadId` int(14) NOT NULL default '0',
  `forumId` int(14) default NULL,
  `timestamp` int(14) default NULL,
  PRIMARY KEY (`user`(177),`threadId`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `tiki_forums`;
CREATE TABLE `tiki_forums` (
  `forumId` int(8) NOT NULL auto_increment,
  `parentId` int(8) NOT NULL default 0,
  `forumOrder` int(8) NOT NULL default 0,
  `name` varchar(255) default NULL,
  `description` text,
  `created` int(14) default NULL,
  `lastPost` int(14) default NULL,
  `threads` int(8) default NULL,
  `comments` int(8) default NULL,
  `controlFlood` char(1) default NULL,
  `floodInterval` int(8) default NULL,
  `moderator` varchar(200) default NULL,
  `hits` int(8) default NULL,
  `mail` varchar(200) default NULL,
  `useMail` char(1) default NULL,
  `section` varchar(200) default NULL,
  `usePruneUnreplied` char(1) default NULL,
  `pruneUnrepliedAge` int(8) default NULL,
  `usePruneOld` char(1) default NULL,
  `pruneMaxAge` int(8) default NULL,
  `topicsPerPage` int(6) default NULL,
  `topicOrdering` varchar(100) default NULL,
  `threadOrdering` varchar(100) default NULL,
  `att` varchar(80) default NULL,
  `att_store` varchar(4) default NULL,
  `att_store_dir` varchar(250) default NULL,
  `att_max_size` int(12) default NULL,
  `att_list_nb` char(1) default NULL,
  `ui_level` char(1) default NULL,
  `forum_password` varchar(32) default NULL,
  `forum_use_password` char(1) default NULL,
  `moderator_group` varchar(200) default NULL,
  `approval_type` varchar(20) default NULL,
  `outbound_address` varchar(250) default NULL,
  `outbound_mails_for_inbound_mails` char(1) default NULL,
  `outbound_mails_reply_link` char(1) default NULL,
  `outbound_from` varchar(250) default NULL,
  `inbound_pop_server` varchar(250) default NULL,
  `inbound_pop_port` int(4) default NULL,
  `inbound_pop_user` varchar(200) default NULL,
  `inbound_pop_password` varchar(80) default NULL,
  `topic_smileys` char(1) default NULL,
  `ui_avatar` char(1) default NULL,
  `ui_rating_choice_topic` char(1) DEFAULT NULL,
  `ui_flag` char(1) default NULL,
  `ui_posts` char(1) default NULL,
  `ui_email` char(1) default NULL,
  `ui_online` char(1) default NULL,
  `topic_summary` char(1) default NULL,
  `show_description` char(1) default NULL,
  `topics_list_replies` char(1) default NULL,
  `topics_list_reads` char(1) default NULL,
  `topics_list_pts` char(1) default NULL,
  `topics_list_lastpost` char(1) default NULL,
  `topics_list_lastpost_title` char(1) default NULL,
  `topics_list_lastpost_avatar` char(1) default NULL,
  `topics_list_author` char(1) default NULL,
  `topics_list_author_avatar` char(1) default NULL,
  `vote_threads` char(1) default NULL,
  `forum_last_n` int(2) default 0,
  `threadStyle` varchar(100) default NULL,
  `commentsPerPage` varchar(100) default NULL,
  `is_flat` char(1) default NULL,
  `mandatory_contribution` char(1) default NULL,
  `forumLanguage` varchar(255) default NULL,
  PRIMARY KEY (`forumId`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `tiki_forums_queue`;
CREATE TABLE `tiki_forums_queue` (
  `qId` int(14) NOT NULL auto_increment,
  `object` varchar(32) default NULL,
  `parentId` int(14) default NULL,
  `forumId` int(14) default NULL,
  `timestamp` int(14) default NULL,
  `user` varchar(200) default '',
  `title` varchar(240) default NULL,
  `data` text,
  `type` varchar(60) default NULL,
  `hash` varchar(32) default NULL,
  `topic_smiley` varchar(80) default NULL,
  `topic_title` varchar(240) default NULL,
  `summary` varchar(240) default NULL,
  `in_reply_to` varchar(128) default NULL,
  `tags` varchar(255) default NULL,
  `email` varchar(255) default NULL,
  PRIMARY KEY (`qId`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `tiki_forums_reported`;
CREATE TABLE `tiki_forums_reported` (
  `threadId` int(12) NOT NULL default '0',
  `forumId` int(12) NOT NULL default '0',
  `parentId` int(12) NOT NULL default '0',
  `user` varchar(200) default '',
  `timestamp` int(14) default NULL,
  `reason` varchar(250) default NULL,
  PRIMARY KEY (`threadId`, `forumId`, `parentId`, `user`(182))
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `tiki_group_inclusion`;
CREATE TABLE `tiki_group_inclusion` (
  `groupName` varchar(255) NOT NULL default '',
  `includeGroup` varchar(255) NOT NULL default '',
  PRIMARY KEY (`groupName`(120),`includeGroup`(120))
) ENGINE=MyISAM;
INSERT INTO  `tiki_group_inclusion` (`groupName` ,`includeGroup`) VALUES ('Registered','Anonymous');

DROP TABLE IF EXISTS `tiki_group_watches`;
CREATE TABLE `tiki_group_watches` (
  `watchId` int(12) NOT NULL auto_increment,
  `group` varchar(200) NOT NULL default '',
  `event` varchar(40) NOT NULL default '',
  `object` varchar(200) NOT NULL default '',
  `title` varchar(250) default NULL,
  `type` varchar(200) default NULL,
  `url` varchar(250) default NULL,
  PRIMARY KEY (`watchId`),
  INDEX `event-object-group` ( `event` , `object` ( 100 ) , `group` ( 50 ) )
) ENGINE=MyISAM;

# Keep track of h5p content entities > Pending in Tiki: Add FileId
DROP TABLE IF EXISTS `tiki_h5p_contents`;
CREATE TABLE tiki_h5p_contents (
    id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    file_id             INT UNSIGNED NOT NULL,    # reference to the file gallery object in tiki_files table
    created_at   TIMESTAMP    NULL,
    updated_at   TIMESTAMP    NULL,
    user_id      INT UNSIGNED NOT NULL,
    title        VARCHAR(255) NOT NULL,
    library_id   INT UNSIGNED NOT NULL,
    parameters   LONGTEXT     NOT NULL,
    filtered     LONGTEXT     NULL,
    slug         VARCHAR(127) NOT NULL,
    embed_type   VARCHAR(127) NOT NULL,
    disable      INT UNSIGNED NOT NULL DEFAULT 0,
    content_type VARCHAR(127) NULL,
    authors      MEDIUMTEXT   NULL,
    license      VARCHAR(32)  NULL DEFAULT NULL,
    keywords     TEXT         NULL,
    description  TEXT         NULL,
    source       VARCHAR(2083) NULL,
    year_from    INT UNSIGNED NULL,
    year_to      INT UNSIGNED NULL,
    license_version VARCHAR(10) NULL,
    license_extras  LONGTEXT NULL,
    author_comments LONGTEXT NULL,
    changes      MEDIUMTEXT NULL,
    default_language VARCHAR(32) NULL,
    a11y_title VARCHAR(255) NULL,
    PRIMARY KEY (id),
    UNIQUE KEY `fileId` (`file_id`)
)    ENGINE = MyISAM;

# Keep track of content dependencies
DROP TABLE IF EXISTS `tiki_h5p_contents_libraries`;
CREATE TABLE tiki_h5p_contents_libraries (
    content_id      INT UNSIGNED      NOT NULL,
    library_id      INT UNSIGNED      NOT NULL,
    dependency_type VARCHAR(31)       NOT NULL,
    weight          SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    drop_css        TINYINT UNSIGNED  NOT NULL,
    PRIMARY KEY (content_id, library_id, dependency_type)
)    ENGINE = MyISAM;

# Keep track of h5p libraries
DROP TABLE IF EXISTS `tiki_h5p_libraries`;
CREATE TABLE tiki_h5p_libraries (
    id               INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    created_at       TIMESTAMP     NULL,
    updated_at       TIMESTAMP     NULL,
    name             VARCHAR(127)  NOT NULL,
    title            VARCHAR(255)  NOT NULL,
    major_version    INT UNSIGNED  NOT NULL,
    minor_version    INT UNSIGNED  NOT NULL,
    patch_version    INT UNSIGNED  NOT NULL,
    runnable         INT UNSIGNED  NOT NULL,
    restricted       INT UNSIGNED  NOT NULL DEFAULT 0,
    fullscreen       INT UNSIGNED  NOT NULL,
    embed_types      VARCHAR(255)  NOT NULL,
    preloaded_js     TEXT          NULL,
    preloaded_css    TEXT          NULL,
    drop_library_css TEXT          NULL,
    semantics        TEXT          NOT NULL,
    tutorial_url     VARCHAR(1023) NOT NULL,
    has_icon         INT  UNSIGNED  NOT NULL  DEFAULT '0',
    metadata_settings TEXT NULL,
    add_to           TEXT DEFAULT NULL,
    PRIMARY KEY (id),
    KEY name_version (name, major_version, minor_version, patch_version),
    KEY runnable (runnable)
)    ENGINE = MyISAM;

DROP TABLE IF EXISTS `tiki_h5p_libraries_hub_cache`;
CREATE TABLE tiki_h5p_libraries_hub_cache (
  id                INT UNSIGNED NOT NULL AUTO_INCREMENT,
  machine_name      VARCHAR(127) NOT NULL,
  major_version     INT UNSIGNED NOT NULL,
  minor_version     INT UNSIGNED NOT NULL,
  patch_version     INT UNSIGNED NOT NULL,
  h5p_major_version INT UNSIGNED,
  h5p_minor_version INT UNSIGNED,
  title             VARCHAR(255) NOT NULL,
  summary           TEXT         NOT NULL,
  description       TEXT         NOT NULL,
  icon              VARCHAR(511) NOT NULL,
  created_at        INT UNSIGNED NOT NULL,
  updated_at        INT UNSIGNED NOT NULL,
  is_recommended    INT UNSIGNED NOT NULL,
  popularity        INT UNSIGNED NOT NULL,
  screenshots       TEXT,
  license           TEXT,
  example           VARCHAR(511) NOT NULL,
  tutorial          VARCHAR(511),
  keywords          TEXT,
  categories        TEXT,
  owner             VARCHAR(511),
  PRIMARY KEY (id),
  KEY name_version (machine_name, major_version, minor_version, patch_version)
) ENGINE = MyISAM;

# Keep track of h5p library dependencies
DROP TABLE IF EXISTS `tiki_h5p_libraries_libraries`;
CREATE TABLE tiki_h5p_libraries_libraries (
    library_id          INT UNSIGNED NOT NULL,
    required_library_id INT UNSIGNED NOT NULL,
    dependency_type     VARCHAR(31)  NOT NULL,
    PRIMARY KEY (library_id, required_library_id)
)    ENGINE = MyISAM;

# Keep track of h5p library translations
DROP TABLE IF EXISTS `tiki_h5p_libraries_languages`;
CREATE TABLE tiki_h5p_libraries_languages (
    library_id    INT UNSIGNED NOT NULL,
    language_code VARCHAR(31)  NOT NULL,
    translation   TEXT         NOT NULL,
    PRIMARY KEY (library_id, language_code)
)    ENGINE = MyISAM;

# Keep track of temporary files uploaded in editor before saving content
DROP TABLE IF EXISTS `tiki_h5p_tmpfiles`;
CREATE TABLE tiki_h5p_tmpfiles (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    path       VARCHAR(255) NOT NULL,
    created_at INT UNSIGNED NOT NULL,
    PRIMARY KEY (id),
    KEY created_at (created_at),
    KEY path (path(191))
) ENGINE = MyISAM;

# Keep track of results (contents >-< users)
DROP TABLE IF EXISTS `tiki_h5p_results`;
CREATE TABLE tiki_h5p_results (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    content_id INT UNSIGNED NOT NULL,
    user_id    INT UNSIGNED NOT NULL,
    score      INT UNSIGNED NOT NULL,
    max_score  INT UNSIGNED NOT NULL,
    opened     INT UNSIGNED NOT NULL,
    finished   INT UNSIGNED NOT NULL,
    time       INT UNSIGNED NOT NULL,
    PRIMARY KEY (id),
    KEY content_user (content_id, user_id)
)    ENGINE = MyISAM;

# Cache table for h5p libraries so we can reuse the existing h5p code for caching
DROP TABLE IF EXISTS `tiki_h5p_libraries_cachedassets`;
CREATE TABLE tiki_h5p_libraries_cachedassets (
    library_id INT UNSIGNED NOT NULL,
    hash       VARCHAR(64)  NOT NULL,
    PRIMARY KEY (library_id, hash)
) ENGINE = MyISAM;

DROP TABLE IF EXISTS `tiki_history`;
CREATE TABLE `tiki_history` (
  `historyId` int(12) NOT NULL auto_increment,
  `pageName` varchar(160) NOT NULL default '',
  `version` int(8) NOT NULL default '0',
  `version_minor` int(8) NOT NULL default '0',
  `lastModif` int(14) default NULL,
  `description` varchar(200) default NULL,
  `user` varchar(200) not null default '',
  `ip` varchar(39) default NULL,
  `comment` varchar(255) default NULL,
  `data` longblob,
  `type` varchar(50) default NULL,
  `is_html` TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`pageName`,`version`),
  KEY `user` (`user`(191)),
  KEY (`historyId`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `tiki_hotwords`;
CREATE TABLE `tiki_hotwords` (
  `word` varchar(255) NOT NULL default '',
  `url` varchar(255) NOT NULL default '',
  PRIMARY KEY (`word`(191))
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `tiki_html_pages`;
CREATE TABLE `tiki_html_pages` (
  `pageName` varchar(200) NOT NULL default '',
  `content` longblob,
  `refresh` int(10) default NULL,
  `type` char(1) default NULL,
  `created` int(14) default NULL,
  PRIMARY KEY (`pageName`(191))
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `tiki_html_pages_dynamic_zones`;
CREATE TABLE `tiki_html_pages_dynamic_zones` (
  `pageName` varchar(40) NOT NULL default '',
  `zone` varchar(80) NOT NULL default '',
  `type` char(2) default NULL,
  `content` text,
  PRIMARY KEY (`pageName`,`zone`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `tiki_language`;
CREATE TABLE `tiki_language` (
  `id` int(14) NOT NULL auto_increment,
  `source` text NOT NULL,
  `lang` char(16) NOT NULL default '',
  `tran` text,
  `changed` bool,
  `general` bool DEFAULT NULL COMMENT 'true if this translation is general and can be contributed to the Tiki community, false if it is specific to this instance',
  `userId` int(8),
  `lastModif` int(14) NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `tiki_link_cache`;
CREATE TABLE `tiki_link_cache` (
  `cacheId` int(14) NOT NULL auto_increment,
  `url` varchar(250) default NULL,
  `data` longblob,
  `refresh` int(14) default NULL,
  PRIMARY KEY (`cacheId`),
  KEY `url` (url(191))
) ENGINE=MyISAM AUTO_INCREMENT=1 ;
CREATE INDEX urlindex ON tiki_link_cache (url(191));

DROP TABLE IF EXISTS `tiki_links`;
CREATE TABLE `tiki_links` (
  `fromPage` varchar(160) NOT NULL default '',
  `toPage` varchar(160) NOT NULL default '',
  `lastModif` int(14) NOT NULL,
  PRIMARY KEY (`fromPage`(96),`toPage`(95)),
  KEY `toPage` (`toPage`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `tiki_live_support_events`;
CREATE TABLE `tiki_live_support_events` (
  `eventId` int(14) NOT NULL auto_increment,
  `reqId` varchar(32) NOT NULL default '',
  `type` varchar(40) default NULL,
  `seqId` int(14) default NULL,
  `senderId` varchar(32) default NULL,
  `data` text,
  `timestamp` int(14) default NULL,
  PRIMARY KEY (`eventId`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `tiki_live_support_message_comments`;
CREATE TABLE `tiki_live_support_message_comments` (
  `cId` int(12) NOT NULL auto_increment,
  `msgId` int(12) default NULL,
  `data` text,
  `timestamp` int(14) default NULL,
  PRIMARY KEY (`cId`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `tiki_live_support_messages`;
CREATE TABLE `tiki_live_support_messages` (
  `msgId` int(12) NOT NULL auto_increment,
  `data` text,
  `timestamp` int(14) default NULL,
  `user` varchar(200) not null default '',
  `username` varchar(200) default NULL,
  `priority` int(2) default NULL,
  `status` char(1) default NULL,
  `assigned_to` varchar(200) default NULL,
  `resolution` varchar(100) default NULL,
  `title` varchar(200) default NULL,
  `module` int(4) default NULL,
  `email` varchar(250) default NULL,
  PRIMARY KEY (`msgId`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `tiki_live_support_modules`;
CREATE TABLE `tiki_live_support_modules` (
  `modId` int(4) NOT NULL auto_increment,
  `name` varchar(90) default NULL,
  PRIMARY KEY (`modId`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

INSERT INTO tiki_live_support_modules(name) VALUES('wiki');
INSERT INTO tiki_live_support_modules(name) VALUES('forums');
INSERT INTO tiki_live_support_modules(name) VALUES('file galleries');
INSERT INTO tiki_live_support_modules(name) VALUES('directory');

DROP TABLE IF EXISTS `tiki_live_support_operators`;
CREATE TABLE `tiki_live_support_operators` (
  `user` varchar(200) NOT NULL default '',
  `accepted_requests` int(10) default NULL,
  `status` varchar(20) default NULL,
  `longest_chat` int(10) default NULL,
  `shortest_chat` int(10) default NULL,
  `average_chat` int(10) default NULL,
  `last_chat` int(14) default NULL,
  `time_online` int(10) default NULL,
  `votes` int(10) default NULL,
  `points` int(10) default NULL,
  `status_since` int(14) default NULL,
  PRIMARY KEY (`user`(191))
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `tiki_live_support_requests`;
CREATE TABLE `tiki_live_support_requests` (
  `reqId` varchar(32) NOT NULL default '',
  `user` varchar(200) NOT NULL default '',
  `tiki_user` varchar(200) default NULL,
  `email` varchar(200) default NULL,
  `operator` varchar(200) default NULL,
  `operator_id` varchar(32) default NULL,
  `user_id` varchar(32) default NULL,
  `reason` text,
  `req_timestamp` int(14) default NULL,
  `timestamp` int(14) default NULL,
  `status` varchar(40) default NULL,
  `resolution` varchar(40) default NULL,
  `chat_started` int(14) default NULL,
  `chat_ended` int(14) default NULL,
  PRIMARY KEY (`reqId`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `tiki_logs`;
CREATE TABLE `tiki_logs` (
  `logId` int(8) NOT NULL auto_increment,
  `logtype` varchar(20) NOT NULL,
  `logmessage` text NOT NULL,
  `loguser` varchar(40) NOT NULL,
  `logip` varchar(200),
  `logclient` text NOT NULL,
  `logtime` int(14) NOT NULL,
  PRIMARY KEY (`logId`),
  KEY `logtype` (logtype)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `tiki_machine_learning_models`;
CREATE TABLE `tiki_machine_learning_models` (
  `mlmId` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL,
  `description` text NULL,
  `sourceTrackerId` int(11) NOT NULL,
  `trackerFields` text NULL,
  `labelField` varchar(191) NULL,
  `ignoreEmpty` tinyint(1) NULL,
  `payload` text NULL,
  PRIMARY KEY  (`mlmId`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `tiki_mail_events`;
CREATE TABLE `tiki_mail_events` (
  `event` varchar(200) default NULL,
  `object` varchar(200) default NULL,
  `email` varchar(200) default NULL
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `tiki_mailin_accounts`;
CREATE TABLE `tiki_mailin_accounts` (
  `accountId` int(12) NOT NULL auto_increment,
  `user` varchar(200) NOT NULL default '',
  `account` varchar(50) NOT NULL default '',
  `protocol` varchar(10) NOT NULL DEFAULT 'pop',
  `host` varchar(255) default NULL,
  `port` int(4) default NULL,
  `username` varchar(100) default NULL,
  `pass` varchar(100) default NULL,
  `active` char(1) default NULL,
  `type` varchar(40) default NULL,
  `anonymous` char(1) NOT NULL default 'y',
  `admin` char(1) NOT NULL default 'y',
  `attachments` char(1) NOT NULL default 'n',
  `routing` char(1) NOT NULL default 'y',
  `article_topicId` int(4) default NULL,
  `article_type` varchar(50) default NULL,
  `discard_after` varchar(255) default NULL,
  `show_inlineImages` char(1) NULL,
  `save_html` char(1) NULL default 'y',
  `categoryId` int(12) NULL,
  `namespace` varchar(20) default NULL,
  `respond_email` char(1) NOT NULL default 'y',
  `leave_email` char(1) NOT NULL default 'n',
  `galleryId` int(11) NULL DEFAULT NULL,
  `trackerId` int(11) NULL DEFAULT NULL,
  `preferences` TEXT NULL DEFAULT NULL,
  PRIMARY KEY (`accountId`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `tiki_menu_languages`;
CREATE TABLE `tiki_menu_languages` (
  `menuId` int(8) NOT NULL auto_increment,
  `language` char(16) NOT NULL default '',
  PRIMARY KEY (`menuId`,`language`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `tiki_menu_options`;
CREATE TABLE `tiki_menu_options` (
  `optionId` int(8) NOT NULL auto_increment,
  `menuId` int(8) default NULL,
  `type` char(1) default NULL,
  `name` varchar(200) default NULL,
  `url` varchar(255) default NULL,
  `position` int(4) default NULL,
  `section` text default NULL,
  `perm` text default NULL,
  `groupname` text default NULL,
  `userlevel` int(4) default 0,
  `icon` varchar(200),
  `class` text default NULL,
  PRIMARY KEY (`optionId`),
  UNIQUE KEY `uniq_menu` (`menuId`,`name`(30),`url`(50),`position`,`section`(60),`perm`(50),`groupname`(50))
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

-- when adding new inserts, order commands by position
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','Home','./',10,'','','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','Search','tiki-searchresults.php',13,'feature_search_fulltext','tiki_p_search','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','Search','tiki-searchindex.php',13,'feature_search','tiki_p_search','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','Contact Us','tiki-contact.php',20,'feature_contact,feature_messages','','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','Stats','tiki-stats.php',23,'feature_stats','tiki_p_view_stats','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','Categories','tiki-browse_categories.php',25,'feature_categories','tiki_p_view_category','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','Tags','tiki-browse_freetags.php',27,'feature_freetags','tiki_p_view_freetags','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','Calendar','tiki-calendar.php',35,'feature_calendar','tiki_p_view_calendar','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','Tiki Calendar','tiki-action_calendar.php',37,'feature_action_calendar','tiki_p_view_tiki_calendar','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','Payments','tiki-payment.php',39,'payment_feature','tiki_p_payment_view','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','Payments','tiki-payment.php',39,'payment_feature','tiki_p_payment_request','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','(debug)','javascript:toggle(\'debugconsole\')',40,'feature_debug_console','tiki_p_admin','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','User Wizard','tiki-wizard_user.php',45,'feature_wizard_user','','Registered',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'s','My Account','tiki-my_tiki.php',50,'feature_mytiki','','Registered',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','My Account Home','tiki-my_tiki.php',51,'feature_mytiki','','Registered',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','Preferences','tiki-user_preferences.php',55,'feature_mytiki,feature_userPreferences','','Registered',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','Messages','messu-mailbox.php',60,'feature_mytiki,feature_messages','tiki_p_messages','Registered',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','Tasks','tiki-user_tasks.php',65,'feature_mytiki,feature_tasks','tiki_p_tasks','Registered',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','Bookmarks','tiki-user_bookmarks.php',70,'feature_mytiki,feature_user_bookmarks','tiki_p_create_bookmarks','Registered',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','Modules','tiki-user_assigned_modules.php',75,'feature_mytiki,user_assigned_modules','tiki_p_configure_modules','Registered',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','Webmail','tiki-webmail.php',85,'feature_mytiki,feature_webmail','tiki_p_use_webmail','Registered',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','Contacts','tiki-contacts.php',87,'feature_mytiki,feature_contacts','','Registered',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','Mail-in','tiki-user_mailin.php',88,'feature_mytiki,feature_mailin','','Registered',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','Notepad','tiki-notepad_list.php',90,'feature_mytiki,feature_notepad','tiki_p_notepad','Registered',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','My Files','tiki-userfiles.php',95,'feature_mytiki,feature_userfiles','tiki_p_userfiles','Registered',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','User Menu','tiki-usermenu.php',100,'feature_mytiki,feature_usermenu','tiki_p_usermenu','Registered',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','Mini Calendar','tiki-minical.php',105,'feature_mytiki,feature_minical','tiki_p_minical','Registered',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','My Watches','tiki-user_watches.php',110,'feature_mytiki,feature_user_watches','','Registered',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'s','Community','tiki-list_users.php',187,'feature_friends','tiki_p_list_users','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','User List','tiki-list_users.php',188,'feature_friends','tiki_p_list_users','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','Friendship Network','tiki-friends.php',189,'feature_friends','','Registered',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'s','Wiki','tiki-index.php',200,'feature_wiki','tiki_p_view','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','Wiki Home','tiki-index.php',202,'feature_wiki','tiki_p_view','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','Last Changes','tiki-lastchanges.php',205,'feature_wiki,feature_lastChanges','tiki_p_view','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','Rankings','tiki-wiki_rankings.php',215,'feature_wiki,feature_wiki_rankings','tiki_p_view','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','List Pages','tiki-listpages.php?cookietab=1#tab1',220,'feature_wiki,feature_listPages','tiki_p_view','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','Create a Wiki Page','tiki-listpages.php?cookietab=2#tab2',222,'feature_wiki,feature_listPages','tiki_p_view,tiki_p_edit','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','Orphan Pages','tiki-orphan_pages.php',225,'feature_wiki,feature_listorphanPages','tiki_p_view','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','Sandbox','tiki-editpage.php?page=sandbox',230,'feature_wiki,feature_sandbox','tiki_p_view','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','Multiple Print','tiki-print_pages.php',235,'feature_wiki,feature_wiki_multiprint','tiki_p_view','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','Send Pages','tiki-send_objects.php',240,'feature_wiki,feature_comm','tiki_p_view,tiki_p_send_pages','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','Received Pages','tiki-received_pages.php',245,'feature_wiki,feature_comm','tiki_p_view,tiki_p_admin_received_pages','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','Structures','tiki-admin_structures.php',250,'feature_wiki,feature_wiki_structure','tiki_p_view','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'s','Articles','tiki-view_articles.php',350,'feature_articles','tiki_p_read_article','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'s','Articles','tiki-view_articles.php',350,'feature_articles','tiki_p_articles_read_heading','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','Articles Home','tiki-view_articles.php',355,'feature_articles','tiki_p_read_article','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','Articles Home','tiki-view_articles.php',355,'feature_articles','tiki_p_articles_read_heading','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','List Articles','tiki-list_articles.php',360,'feature_articles','tiki_p_read_article','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','List Articles','tiki-list_articles.php',360,'feature_articles','tiki_p_articles_read_heading','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','Rankings','tiki-cms_rankings.php',365,'feature_articles,feature_cms_rankings','tiki_p_read_article','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','Submit Article','tiki-edit_submission.php',370,'feature_articles,feature_submissions','tiki_p_read_article,tiki_p_submit_article','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','View submissions','tiki-list_submissions.php',375,'feature_articles,feature_submissions','tiki_p_read_article,tiki_p_submit_article','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','View submissions','tiki-list_submissions.php',375,'feature_articles,feature_submissions','tiki_p_read_article,tiki_p_approve_submission','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','View Submissions','tiki-list_submissions.php',375,'feature_articles,feature_submissions','tiki_p_read_article,tiki_p_remove_submission','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','New Article','tiki-edit_article.php',380,'feature_articles','tiki_p_read_article,tiki_p_edit_article','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','Send Articles','tiki-send_objects.php',385,'feature_articles,feature_comm','tiki_p_read_article,tiki_p_send_articles','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','Received Articles','tiki-received_articles.php',385,'feature_articles,feature_comm','tiki_p_read_article,tiki_p_admin_received_articles','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','Admin Types','tiki-article_types.php',395,'feature_articles','tiki_p_articles_admin_types','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','Admin Topics','tiki-admin_topics.php',390,'feature_articles','tiki_p_articles_admin_topics','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'s','Blogs','tiki-list_blogs.php',450,'feature_blogs','tiki_p_read_blog','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','List Blogs','tiki-list_blogs.php',455,'feature_blogs','tiki_p_read_blog','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','Rankings','tiki-blog_rankings.php',460,'feature_blogs,feature_blog_rankings','tiki_p_read_blog','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','Create Blog','tiki-edit_blog.php',465,'feature_blogs','tiki_p_read_blog,tiki_p_create_blogs','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','New Blog Post','tiki-blog_post.php',470,'feature_blogs','tiki_p_read_blog,tiki_p_blog_post','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','List Blog Posts','tiki-list_posts.php',475,'feature_blogs','tiki_p_read_blog,tiki_p_blog_admin','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'s','Forums','tiki-forums.php',500,'feature_forums','tiki_p_forum_read','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','List Forums','tiki-forums.php',505,'feature_forums','tiki_p_forum_read','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','Rankings','tiki-forum_rankings.php',510,'feature_forums,feature_forum_rankings','tiki_p_forum_read','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','Admin Forums','tiki-admin_forums.php',515,'feature_forums','tiki_p_forum_read,tiki_p_admin_forum','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'s','Directory','tiki-directory_browse.php',550,'feature_directory','tiki_p_view_directory','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','Submit a new link','tiki-directory_add_site.php',555,'feature_directory','tiki_p_submit_link','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','Browse Directory','tiki-directory_browse.php',560,'feature_directory','tiki_p_view_directory','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','Admin Directory','tiki-directory_admin.php',565,'feature_directory','tiki_p_view_directory,tiki_p_admin_directory_cats','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','Admin Directory','tiki-directory_admin.php',565,'feature_directory','tiki_p_view_directory,tiki_p_admin_directory_sites','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','Admin Directory','tiki-directory_admin.php',565,'feature_directory','tiki_p_view_directory,tiki_p_validate_links','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'s','File Galleries','tiki-list_file_gallery.php',600,'feature_file_galleries','tiki_p_list_file_galleries|tiki_p_view_file_gallery|tiki_p_upload_files','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','List Galleries','tiki-list_file_gallery.php',605,'feature_file_galleries','tiki_p_list_file_galleries','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','Rankings','tiki-file_galleries_rankings.php',610,'feature_file_galleries,feature_file_galleries_rankings','tiki_p_list_file_galleries','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','Upload File','tiki-upload_file.php',615,'feature_file_galleries','tiki_p_upload_files','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','Directory batch','tiki-batch_upload_files.php',617,'feature_file_galleries_batch','tiki_p_batch_upload_file_dir','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'s','FAQs','tiki-list_faqs.php',650,'feature_faqs','tiki_p_view_faqs','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','List FAQs','tiki-list_faqs.php',665,'feature_faqs','tiki_p_view_faqs','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','Admin FAQs','tiki-list_faqs.php',660,'feature_faqs','tiki_p_admin_faqs','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'s','Quizzes','tiki-list_quizzes.php',750,'feature_quizzes','tiki_p_take_quiz','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','List Quizzes','tiki-list_quizzes.php',755,'feature_quizzes','tiki_p_take_quiz','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','Quiz Stats','tiki-quiz_stats.php',760,'feature_quizzes','tiki_p_view_quiz_stats','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','Admin Quizzes','tiki-edit_quiz.php',765,'feature_quizzes','tiki_p_admin_quizzes','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'s','Spreadsheets','tiki-sheets.php',780,'feature_sheet','tiki_p_view_sheet','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','List Sheets','tiki-sheets.php',782,'feature_sheet','tiki_p_view_sheet','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'s','Trackers','tiki-list_trackers.php',800,'feature_trackers','tiki_p_list_trackers','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','List Trackers','tiki-list_trackers.php',805,'feature_trackers','tiki_p_list_trackers','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','Manage Tabular Formats','tiki-tabular-manage',810,'tracker_tabular_enabled','tiki_p_tabular_admin','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'s','Machine Learning','tiki-ml-list',820,'feature_machine_learning','tiki_p_machine_learning','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','List Models','tiki-ml-list',825,'feature_machine_learning','tiki_p_machine_learning','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'s', 'Accounting', 'tiki-accounting_books.php', 830, 'feature_accounting', 'tiki_p_acct_view', '', 0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o', 'Accounting books', 'tiki-accounting_books.php', 835, 'feature_accounting', 'tiki_p_acct_view', '', 0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'s','Surveys','tiki-list_surveys.php',850,'feature_surveys','tiki_p_take_survey','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','List Surveys','tiki-list_surveys.php',855,'feature_surveys','tiki_p_take_survey','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','Stats','tiki-survey_stats.php',860,'feature_surveys','tiki_p_view_survey_stats','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','Admin Surveys','tiki-admin_surveys.php',865,'feature_surveys','tiki_p_admin_surveys','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'s','Newsletters','tiki-newsletters.php',900,'feature_newsletters','tiki_p_subscribe_newsletters','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'s','Newsletters','tiki-newsletters.php',900,'feature_newsletters','tiki_p_send_newsletters','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'s','Newsletters','tiki-newsletters.php',900,'feature_newsletters','tiki_p_admin_newsletters','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'s','Newsletters','tiki-newsletters.php',900,'feature_newsletters','tiki_p_list_newsletters','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','Send Newsletters','tiki-send_newsletters.php',905,'feature_newsletters','tiki_p_send_newsletters','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','Admin Newsletters','tiki-admin_newsletters.php',910,'feature_newsletters','tiki_p_admin_newsletters','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'r','Settings','tiki-admin.php',1050,'','tiki_p_admin','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'r','Settings','tiki-admin.php',1050,'','tiki_p_admin_categories','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'r','Settings','tiki-admin.php',1050,'','tiki_p_admin_banners','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'r','Settings','tiki-admin.php',1050,'','tiki_p_edit_templates','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'r','Settings','tiki-admin.php',1050,'','tiki_p_edit_cookies','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'r','Settings','tiki-admin.php',1050,'','tiki_p_admin_dynamic','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'r','Settings','tiki-admin.php',1050,'','tiki_p_admin_mailin','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'r','Settings','tiki-admin.php',1050,'','tiki_p_edit_content_templates','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'r','Settings','tiki-admin.php',1050,'','tiki_p_edit_html_pages','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'r','Settings','tiki-admin.php',1050,'','tiki_p_view_referer_stats','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'r','Settings','tiki-admin.php',1050,'','tiki_p_admin_shoutbox','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'r','Settings','tiki-admin.php',1050,'','tiki_p_live_support_admin','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'r','Settings','tiki-admin.php',1050,'','user_is_operator','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'r','Settings','tiki-admin.php',1050,'feature_integrator','tiki_p_admin_integrator','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'r','Settings','tiki-admin.php',1050,'feature_edit_templates','tiki_p_edit_templates','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'r','Settings','tiki-admin.php',1050,'feature_view_tpl','tiki_p_edit_templates','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'r','Settings','tiki-admin.php',1050,'feature_editcss','tiki_p_create_css','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'r','Settings','tiki-admin.php',1050,'','tiki_p_admin_contribution','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'r','Settings','tiki-admin.php',1050,'','tiki_p_admin_users','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'r','Settings','tiki-admin.php',1050,'','tiki_p_admin_toolbars','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'r','Settings','tiki-admin.php',1050,'','tiki_p_edit_menu','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'r','Settings','tiki-admin.php',1050,'','tiki_p_clean_cache','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'r','Settings','tiki-admin.php',1050,'','tiki_p_admin_modules','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'r','Settings','tiki-admin.php',1050,'','tiki_p_admin_webservices','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o',' Control Panels','tiki-admin.php',1051,'','tiki_p_admin','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','Live Support','tiki-live_support_admin.php',1055,'feature_live_support','tiki_p_live_support_admin','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','Live Support','tiki-live_support_admin.php',1055,'feature_live_support','user_is_operator','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','Banning','tiki-admin_banning.php',1060,'feature_banning','tiki_p_admin_banning','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','Calendar','tiki-admin_calendars.php',1065,'feature_calendar','tiki_p_admin_calendar','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','Admin credits','tiki-admin_credits.php',1067,'payment_feature','tiki_p_admin_users','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','Users','tiki-adminusers.php',1070,'','tiki_p_admin_users','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','Groups','tiki-admingroups.php',1075,'','tiki_p_admin','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','External Pages Cache','tiki-list_cache.php',1080,'cachepages','tiki_p_admin','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','Modules','tiki-admin_modules.php',1085,'','tiki_p_admin_modules','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','Performance','tiki-performance_stats.php',1088,'','tiki_monitor_performance','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','Hotwords','tiki-admin_hotwords.php',1095,'feature_hotwords','tiki_p_admin','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','Edit languages','tiki-edit_languages.php',1098,'lang_use_db','tiki_p_edit_languages','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','External Feeds','tiki-admin_rssmodules.php',1100,'','tiki_p_admin_rssmodules','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','External Wikis','tiki-admin_external_wikis.php',1102,'','tiki_p_admin','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','Menus','tiki-admin_menus.php',1105,'','tiki_p_edit_menu','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','Polls','tiki-admin_polls.php',1110,'feature_polls','tiki_p_admin_polls','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','Mail Notifications','tiki-admin_notifications.php',1120,'','tiki_p_admin_notifications','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','Search Stats','tiki-search_stats.php',1125,'feature_search_stats','tiki_p_admin','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','Theme Control','tiki-theme_control.php',1130,'feature_theme_control','tiki_p_admin','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','Tokens','tiki-admin_tokens.php',1132,'auth_token_access','tiki_p_admin','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','Toolbars','tiki-admin_toolbars.php',1135,'','tiki_p_admin_toolbars','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','Transitions','tiki-admin_transitions.php',1140,'','tiki_p_admin','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','Categories','tiki-admin_categories.php',1145,'feature_categories','tiki_p_admin_categories','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','Banners','tiki-list_banners.php',1150,'feature_banners','tiki_p_admin_banners','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','Edit Templates','tiki-edit_templates.php',1155,'feature_edit_templates','tiki_p_edit_templates','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','View Templates','tiki-edit_templates.php',1155,'feature_view_tpl','tiki_p_edit_templates','',2);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','Edit CSS','tiki-edit_css.php',1158,'feature_editcss','tiki_p_create_css','',2);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','Dynamic content','tiki-list_contents.php',1165,'feature_dynamic_content','tiki_p_admin_dynamic','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','Mail-in','tiki-admin_mailin.php',1175,'feature_mailin','tiki_p_admin_mailin','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','HTML Pages','tiki-admin_html_pages.php',1185,'feature_html_pages','tiki_p_edit_html_pages','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','Shoutbox','tiki-shoutbox.php',1190,'feature_shoutbox','tiki_p_admin_shoutbox','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','Shoutbox Words','tiki-admin_shoutbox_words.php',1191,'feature_shoutbox','tiki_p_admin_shoutbox','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','Referer Stats','tiki-referer_stats.php',1195,'feature_referer_stats','tiki_p_view_referer_stats','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','Integrator','tiki-admin_integrator.php',1205,'feature_integrator','tiki_p_admin_integrator','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','phpinfo','tiki-phpinfo.php',1215,'','tiki_p_admin','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','Tiki Cache/Sys Admin','tiki-admin_system.php',1230,'','tiki_p_clean_cache','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','Tiki Importer','tiki-importer.php',1240,'','tiki_p_admin_importer','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','Tiki Logs','tiki-syslog.php',1245,'','tiki_p_admin','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','Tiki Manager','tiki-ajax_services.php?controller=manager&action=index',1247,'','tiki_p_admin','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','Security Admin','tiki-admin_security.php',1250,'','tiki_p_admin','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','Action Log','tiki-admin_actionlog.php',1255,'feature_actionlog','tiki_p_admin','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','Action Log','tiki-admin_actionlog.php',1255,'feature_actionlog','tiki_p_view_actionlog','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','Action Log','tiki-admin_actionlog.php',1255,'feature_actionlog','tiki_p_view_actionlog_owngroups','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','Content Templates','tiki-admin_content_templates.php',1256,'feature_wiki_templates','tiki_p_edit_content_templates','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','Comments','tiki-list_comments.php',1260,'feature_wiki_comments','tiki_p_admin','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','Comments','tiki-list_comments.php',1260,'feature_article_comments','tiki_p_admin','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','Comments','tiki-list_comments.php',1260,'feature_file_galleries_comments','tiki_p_admin','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','Comments','tiki-list_comments.php',1260,'feature_poll_comments','tiki_p_admin','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','Comments','tiki-list_comments.php',1260,'feature_faq_comments','tiki_p_admin','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','Contribution','tiki-admin_contribution.php',1265,'feature_contribution','tiki_p_admin_contribution','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'s', 'Kaltura Video', 'tiki-list_kaltura_entries.php', 950, 'feature_kaltura', 'tiki_p_admin | tiki_p_admin_kaltura | tiki_p_list_videos', '', 0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o', 'List Media', 'tiki-list_kaltura_entries.php', 952, 'feature_kaltura', 'tiki_p_admin | tiki_p_admin_kaltura | tiki_p_list_videos', '', 0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o', 'Upload Media', 'tiki-kaltura_upload.php', 954, 'feature_kaltura', 'tiki_p_admin | tiki_p_admin_kaltura | tiki_p_upload_videos', '', 0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','Permissions','tiki-objectpermissions.php',1077,'','tiki_p_admin|tiki_p_admin_objects','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','Perspectives','tiki-edit_perspective.php',1081,'feature_perspective','tiki_p_admin','',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','Social networks','tiki-socialnetworks.php',115,'feature_mytiki,feature_socialnetworks','tiki_p_socialnetworks|tiki_p_admin_socialnetworks','Registered',0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','Scheduler','tiki-admin_schedulers.php',1270,'','tiki_p_admin','', 0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','Webservices','tiki-admin_webservices.php',1280,'feature_webservices','tiki_p_admin_webservices','', 0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42,'o','References','tiki-references.php',255,'feature_wiki,feature_references','tiki_p_edit_references','', 0);
INSERT INTO `tiki_menu_options` (`menuId`, `type`, `name`, `url`, `position`, `section`, `perm`, `groupname`, `userlevel`) VALUES (42, 'o', 'Custom Routes', 'tiki-admin_routes.php', 1290, 'feature_sefurl_routes', 'tiki_p_admin', '', 0);

DROP TABLE IF EXISTS `tiki_menus`;
CREATE TABLE `tiki_menus` (
  `menuId` int(8) NOT NULL auto_increment,
  `name` varchar(200) NOT NULL default '',
  `description` text,
  `type` char(1) default NULL,
  `icon` varchar(200) default NULL,
  `use_items_icons` char(1) NOT NULL DEFAULT 'n',
  `parse` char(1)  NOT NULL  DEFAULT 'n',
  PRIMARY KEY (`menuId`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

INSERT INTO tiki_menus (`menuId`,`name`,`description`,`type`,`parse`) VALUES ('42','Application menu','Main extensive navigation menu','d','n');

DROP TABLE IF EXISTS `tiki_minical_events`;
CREATE TABLE `tiki_minical_events` (
  `user` varchar(200) default '',
  `eventId` int(12) NOT NULL auto_increment,
  `title` varchar(250) default NULL,
  `description` text,
  `start` int(14) default NULL,
  `end` int(14) default NULL,
  `security` char(1) default NULL,
  `duration` int(3) default NULL,
  `topicId` int(12) default NULL,
  `reminded` char(1) default NULL,
  PRIMARY KEY (`eventId`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `tiki_minical_topics`;
CREATE TABLE `tiki_minical_topics` (
  `user` varchar(200) default '',
  `topicId` int(12) NOT NULL auto_increment,
  `name` varchar(250) default NULL,
  `filename` varchar(200) default NULL,
  `filetype` varchar(200) default NULL,
  `filesize` varchar(200) default NULL,
  `data` longblob,
  `path` varchar(250) default NULL,
  `isIcon` char(1) default NULL,
  PRIMARY KEY (`topicId`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `tiki_modules`;
CREATE TABLE `tiki_modules` (
  `moduleId` int(8) NOT NULL auto_increment,
  `name` varchar(200) NOT NULL default '',
  `position` varchar(20) NOT NULL DEFAULT '',
  `ord` int(4) NOT NULL DEFAULT '0',
  `type` char(1) default NULL,
  `title` varchar(255) default NULL,
  `cache_time` int(14) default NULL,
  `rows` int(4) default NULL,
  `params` text,
  `groups` text,
  PRIMARY KEY (`moduleId`),
  KEY `positionType` (position, type),
  KEY `namePosOrdParam` (`name`(100), `position`, `ord`, `params`(120))
) ENGINE=MyISAM;

INSERT INTO `tiki_modules` (name,position,ord,cache_time,params,`groups`) VALUES
    ('menu','left',1,7200,'id=42&title=System+Menu','a:1:{i:0;s:10:"Registered";}'),
    ('logo','top',1,7200,'nobox=y','a:0:{}'),
    ('login_box','top',2,0,'mode=popup&nobox=y','a:0:{}'),
    ('rsslist','bottom',1,7200,'nobox=y','a:0:{}'),
    ('poweredby','bottom',2,7200,'nobox=y&icons=n&version=n','a:0:{}');

DROP TABLE IF EXISTS `tiki_newsletter_subscriptions`;
CREATE TABLE `tiki_newsletter_subscriptions` (
  `nlId` int(12) NOT NULL default '0',
  `email` varchar(255) NOT NULL default '',
  `code` varchar(32) default NULL,
  `valid` char(1) default NULL,
  `subscribed` int(14) default NULL,
  `isUser` char(1) NOT NULL default 'n',
  `included` char(1) NOT NULL default 'n',
  PRIMARY KEY (`nlId`,`email`(178),`isUser`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `tiki_newsletter_groups`;
CREATE TABLE `tiki_newsletter_groups` (
  `nlId` int(12) NOT NULL default '0',
  `groupName` varchar(255) NOT NULL default '',
  `code` varchar(32) default NULL,
  `include_groups` char(1) DEFAULT 'y',
  PRIMARY KEY (`nlId`,`groupName`(179))
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `tiki_newsletter_included`;
CREATE TABLE `tiki_newsletter_included` (
  `nlId` int(12) NOT NULL default '0',
  `includedId` int(12) NOT NULL default '0',
  PRIMARY KEY (`nlId`,`includedId`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `tiki_newsletter_pages`;
CREATE TABLE `tiki_newsletter_pages` (
    `nlId` INT( 12 ) NOT NULL ,
    `wikiPageName` VARCHAR( 160 ) NOT NULL ,
    `validateAddrs` CHAR( 1 ) NOT NULL DEFAULT 'n',
    `addToList` CHAR( 1 ) NOT NULL DEFAULT 'n',
    PRIMARY KEY ( `nlId` , `wikiPageName` )
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `tiki_newsletters`;
CREATE TABLE `tiki_newsletters` (
  `nlId` int(12) NOT NULL auto_increment,
  `name` varchar(200) default NULL,
  `description` text,
  `created` int(14) default NULL,
  `lastSent` int(14) default NULL,
  `editions` int(10) default NULL,
  `users` int(10) default NULL,
  `allowUserSub` char(1) default 'y',
  `allowAnySub` char(1) default NULL,
  `unsubMsg` char(1) default 'y',
  `validateAddr` char(1) default 'y',
  `frequency` int(14) default NULL,
  `allowTxt` char(1) default 'y',
  `author` varchar(200) default NULL,
  `allowArticleClip` char(1) default 'y',
  `autoArticleClip` char(1) default 'n',
  `articleClipTypes` text,
  `articleClipRange` int(14) default NULL,
  `emptyClipBlocksSend` char(1) default 'n',
  PRIMARY KEY (`nlId`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `tiki_page_footnotes`;
CREATE TABLE `tiki_page_footnotes` (
  `user` varchar(200) NOT NULL default '',
  `pageName` varchar(250) NOT NULL default '',
  `data` text,
  PRIMARY KEY (`user`(150),`pageName`(100))
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `tiki_pages`;
CREATE TABLE `tiki_pages` (
  `page_id` int(14) NOT NULL auto_increment,
  `pageName` varchar(160) NOT NULL default '',
  `pageSlug` varchar(160) NULL,
  `hits` int(8) default NULL,
  `data` mediumtext,
  `description` varchar(200) default NULL,
  `lastModif` int(14) default NULL,
  `comment` varchar(255) default NULL,
  `version` int(8) NOT NULL default '0',
  `version_minor` int(8) NOT NULL default '0',
  `user` varchar(200) default '',
  `ip` varchar(39) default NULL,
  `flag` char(1) default NULL,
  `points` int(8) default NULL,
  `votes` int(8) default NULL,
  `cache` longtext,
  `wiki_cache` int(10) default NULL,
  `cache_timestamp` int(14) default NULL,
  `pageRank` decimal(4,3) default NULL,
  `creator` varchar(200) default NULL,
  `page_size` int(10) unsigned default '0',
  `lang` varchar(16) default NULL,
  `lockedby` varchar(200) default NULL,
  `is_html` tinyint(1) default 0,
  `created` int(14),
  `wysiwyg` char(1) default NULL,
  `wiki_authors_style` varchar(20) default '',
  `comments_enabled` char(1) default NULL,
  `keywords` TEXT,
  PRIMARY KEY (`page_id`),
  UNIQUE KEY `pageName` (`pageName`),
  UNIQUE KEY `pageSlug` (`pageSlug`),
  KEY `data` (`data`(191)),
  KEY `pageRank` (`pageRank`),
  KEY `lastModif`(`lastModif`)
) ENGINE=MyISAM AUTO_INCREMENT=1;

DROP TABLE IF EXISTS `tiki_pageviews`;
CREATE TABLE `tiki_pageviews` (
  `day` int(14) NOT NULL default '0',
  `pageviews` int(14) default NULL,
  PRIMARY KEY (`day`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `tiki_poll_objects`;
CREATE TABLE `tiki_poll_objects` (
  `catObjectId` int(11) NOT NULL default '0',
  `pollId` int(11) NOT NULL default '0',
  `title` varchar(255) default NULL,
  PRIMARY KEY (`catObjectId`,`pollId`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `tiki_poll_options`;
CREATE TABLE `tiki_poll_options` (
  `pollId` int(8) NOT NULL default '0',
  `optionId` int(8) NOT NULL auto_increment,
  `title` varchar(200) default NULL,
  `position` int(4) NOT NULL default '0',
  `votes` int(8) default NULL,
  PRIMARY KEY (`optionId`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `tiki_polls`;
CREATE TABLE `tiki_polls` (
  `pollId` int(8) NOT NULL auto_increment,
  `title` varchar(200) default NULL,
  `votes` int(8) default NULL,
  `active` char(1) default NULL,
  `publishDate` int(14) default NULL,
  `voteConsiderationSpan` int(4) default 0,
  PRIMARY KEY (`pollId`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;
ALTER TABLE tiki_polls ADD INDEX tiki_poll_lookup ( active , title(190) );

DROP TABLE IF EXISTS `tiki_preferences`;
CREATE TABLE `tiki_preferences` (
  `name` varchar(255) NOT NULL default '',
  `value` mediumtext,
  PRIMARY KEY (`name`(191))
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `tiki_private_messages`;
CREATE TABLE `tiki_private_messages` (
  `messageId` int(8) NOT NULL auto_increment,
  `toNickname` varchar(200) NOT NULL default '',
  `poster` varchar(200) NOT NULL default 'anonymous',
  `timestamp` int(14) default NULL,
  `received` tinyint(1) not null default 0,
  `message` varchar(255) default NULL,
  PRIMARY KEY (`messageId`),
  KEY (`received`),
  KEY (`timestamp`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `tiki_programmed_content`;
CREATE TABLE `tiki_programmed_content` (
  `pId` int(8) NOT NULL auto_increment,
  `contentId` int(8) NOT NULL default '0',
  `content_type` VARCHAR( 20 ) NOT NULL DEFAULT 'static',
  `publishDate` int(14) NOT NULL default '0',
  `data` text,
  PRIMARY KEY (`pId`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `tiki_quiz_question_options`;
CREATE TABLE `tiki_quiz_question_options` (
  `optionId` int(10) NOT NULL auto_increment,
  `questionId` int(10) default NULL,
  `optionText` text,
  `points` int(4) default NULL,
  PRIMARY KEY (`optionId`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `tiki_quiz_questions`;
CREATE TABLE `tiki_quiz_questions` (
  `questionId` int(10) NOT NULL auto_increment,
  `quizId` int(10) default NULL,
  `question` text,
  `position` int(4) default NULL,
  `type` char(1) default NULL,
  `maxPoints` int(4) default NULL,
  PRIMARY KEY (`questionId`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `tiki_quiz_results`;
CREATE TABLE `tiki_quiz_results` (
  `resultId` int(10) NOT NULL auto_increment,
  `quizId` int(10) default NULL,
  `fromPoints` int(4) default NULL,
  `toPoints` int(4) default NULL,
  `answer` text,
  PRIMARY KEY (`resultId`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `tiki_quiz_stats`;
CREATE TABLE `tiki_quiz_stats` (
  `quizId` int(10) NOT NULL default '0',
  `questionId` int(10) NOT NULL default '0',
  `optionId` int(10) NOT NULL default '0',
  `votes` int(10) default NULL,
  PRIMARY KEY (`quizId`,`questionId`,`optionId`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `tiki_quiz_stats_sum`;
CREATE TABLE `tiki_quiz_stats_sum` (
  `quizId` int(10) NOT NULL default '0',
  `quizName` varchar(255) default NULL,
  `timesTaken` int(10) default NULL,
  `avgpoints` decimal(5,2) default NULL,
  `avgavg` decimal(5,2) default NULL,
  `avgtime` decimal(5,2) default NULL,
  PRIMARY KEY (`quizId`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `tiki_quizzes`;
CREATE TABLE `tiki_quizzes` (
  `quizId` int(10) NOT NULL auto_increment,
  `name` varchar(255) default NULL,
  `description` text,
  `canRepeat` char(1) default NULL,
  `storeResults` char(1) default NULL,
  `questionsPerPage` int(4) default NULL,
  `timeLimited` char(1) default NULL,
  `timeLimit` int(14) default NULL,
  `created` int(14) default NULL,
  `taken` int(10) default NULL,
  `immediateFeedback` char(1) default NULL,
  `showAnswers` char(1) default NULL,
  `shuffleQuestions` char(1) default NULL,
  `shuffleAnswers` char(1) default NULL,
  `publishDate` int(14) default NULL,
  `expireDate` int(14) default NULL,
  `bDeleted` char(1) default NULL,
  `nAuthor` int(4) default NULL,
  `bOnline` char(1) default NULL,
  `bRandomQuestions` char(1) default NULL,
  `nRandomQuestions` tinyint(4) default NULL,
  `bLimitQuestionsPerPage` char(1) default NULL,
  `nLimitQuestionsPerPage` tinyint(4) default NULL,
  `bMultiSession` char(1) default NULL,
  `nCanRepeat` tinyint(4) default NULL,
  `sGradingMethod` varchar(80) default NULL,
  `sShowScore` varchar(80) default NULL,
  `sShowCorrectAnswers` varchar(80) default NULL,
  `sPublishStats` varchar(80) default NULL,
  `bAdditionalQuestions` char(1) default NULL,
  `bForum` char(1) default NULL,
  `sForum` varchar(80) default NULL,
  `sPrologue` text,
  `sData` text,
  `sEpilogue` text,
  `passingperct` int(4) default 0,
  PRIMARY KEY (`quizId`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `tiki_received_articles`;
CREATE TABLE `tiki_received_articles` (
  `receivedArticleId` int(14) NOT NULL auto_increment,
  `receivedFromSite` varchar(200) default NULL,
  `receivedFromUser` varchar(200) default NULL,
  `receivedDate` int(14) default NULL,
  `title` varchar(80) default NULL,
  `authorName` varchar(60) default NULL,
  `size` int(12) default NULL,
  `useImage` char(1) default NULL,
  `image_name` varchar(80) default NULL,
  `image_type` varchar(80) default NULL,
  `image_size` int(14) default NULL,
  `image_x` int(4) default NULL,
  `image_y` int(4) default NULL,
  `image_data` longblob,
  `publishDate` int(14) default NULL,
  `expireDate` int(14) default NULL,
  `created` int(14) default NULL,
  `heading` text,
  `body` longblob,
  `hash` varchar(32) default NULL,
  `author` varchar(200) default NULL,
  `type` varchar(50) default NULL,
  `rating` decimal(3,2) default NULL,
  PRIMARY KEY (`receivedArticleId`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `tiki_received_pages`;
CREATE TABLE `tiki_received_pages` (
  `receivedPageId` int(14) NOT NULL auto_increment,
  `pageName` varchar(160) NOT NULL default '',
  `data` longblob,
  `description` varchar(200) default NULL,
  `comment` varchar(200) default NULL,
  `receivedFromSite` varchar(200) default NULL,
  `receivedFromUser` varchar(200) default NULL,
  `receivedDate` int(14) default NULL,
  `parent` varchar(255) default NULL,
  `position` tinyint(3) unsigned default NULL,
  `alias` varchar(255) default NULL,
  `structureName` varchar(250) default NULL,
  `parentName` varchar(250) default NULL,
  `page_alias` varchar(250) default '',
  `pos` int(4) default NULL,
  PRIMARY KEY (`receivedPageId`),
  KEY `structureName` (`structureName`(191))
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `tiki_referer_stats`;
CREATE TABLE `tiki_referer_stats` (
  `referer` varchar(255) NOT NULL default '',
  `hits` int(10) default NULL,
  `last` int(14) default NULL,
  `lasturl` text default NULL,
  PRIMARY KEY (`referer`(191))
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `tiki_related_categories`;
CREATE TABLE `tiki_related_categories` (
  `categId` int(10) NOT NULL default '0',
  `relatedTo` int(10) NOT NULL default '0',
  PRIMARY KEY (`categId`,`relatedTo`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `tiki_rss_modules`;
CREATE TABLE `tiki_rss_modules` (
  `rssId` int(8) NOT NULL auto_increment,
  `name` varchar(30) NOT NULL default '',
  `description` text,
  `url` varchar(255) NOT NULL default '',
  `refresh` int(8) default NULL,
  `lastUpdated` int(14) default NULL,
  `showTitle` char(1) default 'n',
  `showPubDate` char(1) default 'n',
  `sitetitle` VARCHAR(255),
  `siteurl` VARCHAR(255),
  `actions` TEXT,
  PRIMARY KEY (`rssId`),
  KEY `name` (name)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `tiki_rss_feeds`;
CREATE TABLE `tiki_rss_feeds` (
  `name` varchar(60) NOT NULL default '',
  `rssVer` char(1) NOT NULL default '1',
  `refresh` int(8) default '300',
  `lastUpdated` int(14) default NULL,
  `cache` longblob,
  PRIMARY KEY (`name`,`rssVer`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `tiki_search_stats`;
CREATE TABLE `tiki_search_stats` (
  `term` varchar(50) NOT NULL default '',
  `hits` int(10) default NULL,
  PRIMARY KEY (`term`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `tiki_secdb`;
CREATE TABLE tiki_secdb(
  `md5_value` varchar(32) NOT NULL,
  `filename` varchar(250) NOT NULL,
  `tiki_version` varchar(60) NOT NULL,
  `severity` int(4) NOT NULL default '0',
  PRIMARY KEY (`filename`(171),`tiki_version`(20)),
  KEY `sdb_fn` (filename(191))
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `tiki_semaphores`;
CREATE TABLE `tiki_semaphores` (
  `semName` varchar(250) NOT NULL default '',
  `objectType` varchar(20) default 'wiki page',
  `user` varchar(200) NOT NULL default '',
  `timestamp` int(14) default NULL,
  `value` VARCHAR(255) NULL,
  PRIMARY KEY (`semName`(191))
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `tiki_sent_newsletters`;
CREATE TABLE `tiki_sent_newsletters` (
  `editionId` int(12) NOT NULL auto_increment,
  `nlId` int(12) NOT NULL default '0',
  `users` int(10) default NULL,
  `sent` int(14) default NULL,
  `subject` varchar(200) default NULL,
  `data` longblob,
  `datatxt` longblob,
  `wysiwyg` char(1) default NULL,
  `is_html` varchar(2) default NULL,
  PRIMARY KEY (`editionId`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `tiki_sent_newsletters_errors`;
CREATE TABLE `tiki_sent_newsletters_errors` (
  `editionId` int(12),
  `email` varchar(255),
  `login` varchar(40) default '',
  `error` char(1) default '',
  KEY (`editionId`)
) ENGINE=MyISAM ;

DROP TABLE IF EXISTS `tiki_sessions`;
CREATE TABLE `tiki_sessions` (
  `sessionId` varchar(32) NOT NULL default '',
  `user` varchar(200) default '',
  `timestamp` int(14) default NULL,
  `tikihost` varchar(200) default NULL,
  PRIMARY KEY (`sessionId`),
  KEY `user` (user(191)),
  KEY `timestamp` (`timestamp`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `tiki_sheet_layout`;
CREATE TABLE `tiki_sheet_layout` (
  `sheetId` int(8) NOT NULL default '0',
  `begin` int(10) NOT NULL default '0',
  `end` int(10) default NULL,
  `headerRow` int(4) NOT NULL default '0',
  `footerRow` int(4) NOT NULL default '0',
  `className` varchar(64) default NULL,
  `parseValues` char( 1 ) NOT NULL default 'n',
  `clonedSheetId` int(8) NULL,
  `metadata` longblob,
  UNIQUE KEY `sheetId` (`sheetId`, `begin`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `tiki_sheet_values`;
CREATE TABLE `tiki_sheet_values` (
  `sheetId` int(8) NOT NULL default '0',
  `begin` int(10) NOT NULL default '0',
  `end` int(10) default NULL,
  `rowIndex` int(4) NOT NULL default '0',
  `columnIndex` int(4) NOT NULL default '0',
  `value` varchar(255) default NULL,
  `calculation` varchar(255) default NULL,
  `width` int(4) NOT NULL default '1',
  `height` int(4) NOT NULL default '1',
  `format` varchar(255) default NULL,
  `user` varchar(200) default '',
  `style` varchar( 255 ) default '',
  `class` varchar( 255 ) default '',
  `clonedSheetId` int(8) NULL,
  UNIQUE KEY `sheetId` (`sheetId`,begin,`rowIndex`,`columnIndex`),
  KEY `sheetId_2` (`sheetId`,`rowIndex`,`columnIndex`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `tiki_sheets`;
CREATE TABLE `tiki_sheets` (
  `sheetId` int(8) NOT NULL auto_increment,
  `title` varchar(200) NOT NULL default '',
  `description` text,
  `author` varchar(200) NOT NULL default '',
  `parentSheetId` int(8) NULL,
  `clonedSheetId` int(8) NULL,
  PRIMARY KEY (`sheetId`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `tiki_shoutbox`;
CREATE TABLE `tiki_shoutbox` (
  `msgId` int(10) NOT NULL auto_increment,
  `message` varchar(255) default NULL,
  `timestamp` int(14) default NULL,
  `user` varchar(200) NULL default '',
  `hash` varchar(32) default NULL,
  `tweetId` bigint(20) unsigned NOT NULL default 0,
  PRIMARY KEY (`msgId`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `tiki_shoutbox_words`;
CREATE TABLE `tiki_shoutbox_words` (
  `word` VARCHAR( 40 ) NOT NULL ,
  `qty` INT DEFAULT '0' NOT NULL ,
  PRIMARY KEY (`word`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `tiki_structure_versions`;
CREATE TABLE `tiki_structure_versions` (
  `structure_id` int(14) NOT NULL auto_increment,
  `version` int(14) default NULL,
  PRIMARY KEY (`structure_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `tiki_structures`;
CREATE TABLE `tiki_structures` (
  `page_ref_id` int(14) NOT NULL auto_increment,
  `structure_id` int(14) NOT NULL,
  `parent_id` int(14) default NULL,
  `page_id` int(14) NOT NULL,
  `page_version` int(8) default NULL,
  `page_alias` varchar(240) default '',
  `pos` int(4) default NULL,
  PRIMARY KEY (`page_ref_id`),
  KEY `pidpaid` (page_id,parent_id),
  KEY `page_id` (page_id)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `tiki_submissions`;
CREATE TABLE `tiki_submissions` (
  `subId` int(8) NOT NULL auto_increment,
  `topline` varchar(255) default NULL,
  `title` varchar(255) default NULL,
  `subtitle` varchar(255) default NULL,
  `linkto` varchar(255) default NULL,
  `lang` varchar(16) default NULL,
  `authorName` varchar(60) default NULL,
  `topicId` int(14) default NULL,
  `topicName` varchar(40) default NULL,
  `size` int(12) default NULL,
  `useImage` char(1) default NULL,
  `image_name` varchar(80) default NULL,
  `image_caption` text default NULL,
  `image_type` varchar(80) default NULL,
  `im