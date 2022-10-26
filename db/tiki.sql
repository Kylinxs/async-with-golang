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
DR