
# $Id$

#
# If you are using Tiki 3.x or later, this script will automatically be run by the installer when it detects that your data is from pre 3.x
#
# This approach of making one .sql file per version is obsolete. The new system is explained at: http://dev.tiki.org/Database+Schema+Upgrade
#
#
#
# The following script will update a Tiki database from version 1.9 to 2.0 (which was previously named 1.10)
# 
# To execute this file do the following:
#
# $ mysql -f dbname < tiki_1.9to2.0.sql
#
# where dbname is the name of your tiki database.
#
# For example, if your tiki database is named tiki (not a bad choice), type:
#
# $ mysql -f tiki < tiki_1.9to2.0.sql
# 
# You may execute this command as often as you like, 
# and may safely ignore any error messages that appear.

#2005-06-22 rlpowell: available_languages was getting truncated if all languages were selected
ALTER TABLE `tiki_preferences` CHANGE value value text;

#2005-07-15 rlpowell: Had a wiki page get truncated! Very annoying.
# This will allow up to 16777216 bytes instead of 65536
ALTER TABLE `tiki_pages` CHANGE data data mediumtext;
ALTER TABLE `tiki_pages` CHANGE cache cache mediumtext;

# 2005-08-26 / 2005-09-31: mdavey: new table tiki_events for notificationlib / tikisignal
CREATE TABLE `tiki_events` (
  `callback_type` int(1) NOT NULL default '3',
  `order` int(2) NOT NULL default '50',
  `event` varchar(200) NOT NULL default '',
  `file` varchar(200) NOT NULL default '',
  `object` varchar(200) NOT NULL default '',
  `method` varchar(200) NOT NULL default '',
  PRIMARY KEY  (`callback_type`,`order`)
) ENGINE=MyISAM;

# 2005-09-31: mdavey: make sure developers are using the 6-column version of tiki_events
ALTER TABLE `tiki_events` ADD `file` varchar(200) NOT NULL default '' AFTER `event`;

# 2005-08-26 / 2005-09-31: mdavey: new table tiki_events for notificationlib / tikisignal
INSERT IGNORE INTO tiki_events(`callback_type`,`order`,`event`,`file`,`object`,`method`) VALUES ('1', '20', 'user_registers', 'lib/registration/registrationlib.php', 'registrationlib', 'callback_tikiwiki_setup_custom_fields');
INSERT IGNORE INTO tiki_events(`event`,`file`,`object`,`method`) VALUES ('user_registers', 'lib/registration/registrationlib.php', 'registrationlib', 'callback_tikiwiki_save_registration');
INSERT IGNORE INTO tiki_events(`callback_type`,`order`,`event`,`file`,`object`,`method`) VALUES ('5', '20', 'user_registers', 'lib/registration/registrationlib.php', 'registrationlib', 'callback_logslib_user_registers');
INSERT IGNORE INTO tiki_events(`callback_type`,`order`,`event`,`file`,`object`,`method`) VALUES ('5', '25', 'user_registers', 'lib/registration/registrationlib.php', 'registrationlib', 'callback_tikiwiki_send_email');
INSERT IGNORE INTO tiki_events(`callback_type`,`order`,`event`,`file`,`object`,`method`) VALUES ('5', '30', 'user_registers', 'lib/registration/registrationlib.php', 'registrationlib', 'callback_tikimail_user_registers');

# 2005-09-31: mdavey: make sure developers are using the 6-column version of tiki_events
UPDATE `tiki_events` SET `file` = 'lib/registration/registrationlib.php' WHERE `callback_type` = '1' AND `order` = '20';
UPDATE `tiki_events` SET `file` = 'lib/registration/registrationlib.php' WHERE `callback_type` = '3' AND `order` = '50';
UPDATE `tiki_events` SET `file` = 'lib/registration/registrationlib.php' WHERE `callback_type` = '5' AND `order` = '20';
UPDATE `tiki_events` SET `file` = 'lib/registration/registrationlib.php' WHERE `callback_type` = '5' AND `order` = '25';
UPDATE `tiki_events` SET `file` = 'lib/registration/registrationlib.php' WHERE `callback_type` = '5' AND `order` = '30';

# 2005-08-31: mdavey: new table tiki_registration_fields
CREATE TABLE `tiki_registration_fields` (
  `id` int(11) NOT NULL auto_increment,
  `field` varchar(255) NOT NULL default '',
  `name` varchar(255) default NULL,
  `type` varchar(255) NOT NULL default 'text',
  `show` tinyint(1) NOT NULL default '1',
  `size` varchar(10) default '10',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM;

# 2005-09-22: mdavey: move custom fields to new table
INSERT IGNORE INTO `tiki_registration_fields` (field, name) SELECT prefName as field, value as name FROM `tiki_user_preferences` WHERE user='CustomFields';
DELETE FROM  `tiki_user_preferences` WHERE user='CustomFields';

# 2005-09-07: rlpowell: These changes make a *huge* difference to speed of retrieval of forum threads.
ALTER TABLE tiki_comments MODIFY COLUMN `message_id` varchar(128) default NULL;
ALTER TABLE tiki_comments MODIFY COLUMN `in_reply_to` varchar(128) default NULL;
ALTER TABLE tiki_comments ADD INDEX THREADED (message_id, in_reply_to, parentId);

# 2005-09-07: rlpowell: These changes stop the mail system from repeatedly adding the same posts.
ALTER TABLE tiki_comments MODIFY COLUMN `userName` varchar(40) default NULL;
ALTER IGNORE TABLE tiki_comments ADD UNIQUE parentId(parentId, userName, title, commentDate, message_id, in_reply_to);
# NOTE: It is possible to lose data with the "ALTER IGNORE TABLE" line, but it should only be repeat data anyways.
# In addition, ALTER IGNORE TABLE is a MySQL extension.  If it doesn't work,
# the following should give you a tiki_comments table that you can apply the unique key to, but I suggest
# making a copy first.
# delete from tiki_comments tc1, tiki_comments tc2 where tc1.threadId < tc2.threadId and tc1.parentId = tc2.parentId and  tc1.userName = tc2.userName and  tc1.title = tc2.title and  tc1.commentDate = tc2.commentDate and  tc1.message_id = tc2.message_id and tc1.in_reply_to = tc2.in_reply_to;

# 2005-09-12 sylvieg
ALTER TABLE `tiki_actionlog` CHANGE `pageName` `object` varchar(255) default NULL;
ALTER TABLE `tiki_actionlog` ADD `objectType` varchar(32) NOT NULL default '' AFTER `object`;
ALTER TABLE `tiki_actionlog` ADD `categId` int(12) NOT NULL default '0' AFTER `comment`;
ALTER TABLE `tiki_actionlog` ADD `actionId` int(8) NOT NULL auto_increment FIRST, ADD PRIMARY KEY (`actionId`);
DELETE FROM `tiki_menu_options` WHERE menuId='42' and type='o' and name='Action Log' and url='tiki-admin_actionlog.php' and position='1255' and section='feature_actionlog' and perm='tiki_p_admin' and groupname='' ;
INSERT INTO tiki_menu_options (menuId,type,name,url,position,section,perm,groupname) VALUES (42,'o','Action Log','tiki-admin_actionlog.php',1255,'feature_actionlog','tiki_p_admin','');
CREATE TABLE `tiki_actionlog_conf` (
 `action` varchar(32) NOT NULL default '',
 `objectType`varchar(32) NOT NULL default '',
 `status` char(1) default '',
 PRIMARY KEY (`action`, `objectType`)
) ENGINE=MyISAM;
INSERT IGNORE INTO `tiki_actionlog_conf`(`action`, `objectType`, `status`) VALUES ('Created', 'wiki page', 'y');
INSERT IGNORE INTO `tiki_actionlog_conf`(`action`, `objectType`, `status`) VALUES ('Updated', 'wiki page', 'y');
INSERT IGNORE INTO `tiki_actionlog_conf`(`action`, `objectType`, `status`) VALUES ('Removed', 'wiki page', 'y');
INSERT IGNORE INTO `tiki_actionlog_conf`(`action`, `objectType`, `status`) VALUES ('Viewed', 'wiki page', 'n');
INSERT IGNORE INTO `tiki_actionlog_conf`(`action`, `objectType`, `status`) VALUES ('Viewed', 'forum', 'n');
INSERT IGNORE INTO `tiki_actionlog_conf`(`action`, `objectType`, `status`) VALUES ('Posted', 'forum', 'n');
INSERT IGNORE INTO `tiki_actionlog_conf`(`action`, `objectType`, `status`) VALUES ('Replied', 'forum', 'n');
INSERT IGNORE INTO `tiki_actionlog_conf`(`action`, `objectType`, `status`) VALUES ('Updated', 'forum', 'n');
INSERT IGNORE INTO `tiki_actionlog_conf`(`action`, `objectType`, `status`) VALUES ('Viewed', 'file gallery', 'n');
INSERT IGNORE INTO `tiki_actionlog_conf`(`action`, `objectType`, `status`) VALUES ('Viewed', 'image gallery', 'n');
INSERT IGNORE INTO `tiki_actionlog_conf`(`action`, `objectType`, `status`) VALUES ('Uploaded', 'file gallery', 'n');
INSERT IGNORE INTO `tiki_actionlog_conf`(`action`, `objectType`, `status`) VALUES ('Uploaded', 'image gallery', 'n');
INSERT IGNORE INTO `tiki_actionlog_conf`(`action`, `objectType`, `status`) VALUES ('*', 'category', 'n');
INSERT IGNORE INTO `tiki_actionlog_conf`(`action`, `objectType`, `status`) VALUES ('*', 'login', 'n');
INSERT IGNORE INTO `tiki_actionlog_conf`(`action`, `objectType`, `status`) VALUES ('Posted', 'message', 'n');
INSERT IGNORE INTO `tiki_actionlog_conf`(`action`, `objectType`, `status`) VALUES ('Replied', 'message', 'n');
INSERT IGNORE INTO `tiki_actionlog_conf`(`action`, `objectType`, `status`) VALUES ('Viewed', 'message', 'n');
INSERT IGNORE INTO `tiki_actionlog_conf`(`action`, `objectType`, `status`) VALUES ('Removed version', 'wiki page', 'n');
INSERT IGNORE INTO `tiki_actionlog_conf`(`action`, `objectType`, `status`) VALUES ('Removed last version', 'wiki page', 'n');
INSERT IGNORE INTO `tiki_actionlog_conf`(`action`, `objectType`, `status`) VALUES ('Rollback', 'wiki page', 'n');

#2005-09-27 brazilian tiki study group
INSERT INTO users_permissions (permName, permDesc, level, type) VALUES ('tiki_p_batch_subscribe_email', 'Can subscribe many e-mails at once (requires tiki_p_subscribe email)', 'editors', 'newsletters');

#2005-10-04 sylvieg
DELETE FROM tiki_logs where logmessage='timeout' and loguser='Anonymous';

#2005-10-21 sylvieg
INSERT INTO `tiki_actionlog_conf`(`action`, `objectType`, `status`) VALUES ('Removed', 'forum', 'n');
CREATE INDEX lastModif on tiki_pages (lastModif);

#2005-10-24 sylvieg to boost tiki_stats and tiki_orphan
CREATE INDEX toPage on tiki_links (toPage);
CREATE INDEX page_id on tiki_structures (page_id);

#2005-10-26 sylvieg
INSERT IGNORE INTO `tiki_actionlog_conf`(`action`, `objectType`, `status`) VALUES ('Downloaded', 'file gallery', 'n');

#2005-11-07 sylvieg
INSERT IGNORE INTO `tiki_actionlog_conf`(`action`, `objectType`, `status`) VALUES ('Posted', 'comment', 'n');
INSERT IGNORE INTO `tiki_actionlog_conf`(`action`, `objectType`, `status`) VALUES ('Replied', 'comment', 'n');
INSERT IGNORE INTO `tiki_actionlog_conf`(`action`, `objectType`, `status`) VALUES ('Updated', 'comment', 'n');
INSERT IGNORE INTO `tiki_actionlog_conf`(`action`, `objectType`, `status`) VALUES ('Removed', 'comment', 'n');

#2005-11-09 sylvieg
DELETE FROM tiki_preferences WHERE name='users_prefs_language';
DELETE FROM tiki_preferences WHERE name='users_prefs_theme';
DELETE FROM tiki_preferences WHERE name='users_prefs_mailCharset';
DELETE FROM tiki_user_preferences WHERE prefName='users_prefs_language' and value='global';
DELETE FROM tiki_user_preferences WHERE prefName='users_prefs_theme' and value='global';
DELETE FROM `tiki_menu_options` WHERE menuId='42' and type='o' and name='Comments' and url='tiki-list_comments.php' and position='1260' and perm='tiki_p_admin' and groupname='' ;
INSERT INTO tiki_menu_options (menuId,type,name,url,position,section,perm,groupname) VALUES (42,'o','Comments','tiki-list_comments.php',1260,'feature_wiki_comments','tiki_p_admin','');
INSERT INTO tiki_menu_options (menuId,type,name,url,position,section,perm,groupname) VALUES (42,'o','Comments','tiki-list_comments.php',1260,'feature_article_comments','tiki_p_admin','');
INSERT INTO tiki_menu_options (menuId,type,name,url,position,section,perm,groupname) VALUES (42,'o','Comments','tiki-list_comments.php',1260,'feature_blog_comments','tiki_p_admin','');
INSERT INTO tiki_menu_options (menuId,type,name,url,position,section,perm,groupname) VALUES (42,'o','Comments','tiki-list_comments.php',1260,'feature_file_galleries_comments','tiki_p_admin','');
INSERT INTO tiki_menu_options (menuId,type,name,url,position,section,perm,groupname) VALUES (42,'o','Comments','tiki-list_comments.php',1260,'feature_image_galleries_comments','tiki_p_admin','');
INSERT INTO tiki_menu_options (menuId,type,name,url,position,section,perm,groupname) VALUES (42,'o','Comments','tiki-list_comments.php',1260,'feature_poll_comments','tiki_p_admin','');
INSERT INTO tiki_menu_options (menuId,type,name,url,position,section,perm,groupname) VALUES (42,'o','Comments','tiki-list_comments.php',1260,'feature_faq_comments','tiki_p_admin','');

#2005-11-14 sylvieg
CREATE INDEX positionType ON tiki_modules (position, type);

#2005-12-02 amette
CREATE TABLE `tiki_freetags` (
  `tagId` int(10) unsigned NOT NULL auto_increment,
  `tag` varchar(30) NOT NULL default '',
  `raw_tag` varchar(50) NOT NULL default '',
  PRIMARY KEY  (`tagId`)
) ENGINE=MyISAM;

#2005-12-06 lfagundes
ALTER TABLE `tiki_categorized_objects` rename to `tiki_objects`;
ALTER TABLE `tiki_objects` CHANGE `catObjectId` `objectId` int(12) not null auto_increment;
ALTER TABLE `tiki_objects` CHANGE `objId` `itemId` varchar(255);

CREATE TABLE `tiki_freetagged_objects` (
  `tagId` int(12) NOT NULL auto_increment,
  `objectId` int(11) NOT NULL default 0,
  `user` varchar(40) NOT NULL default '',
  `created` int(14) NOT NULL default '0',
  PRIMARY KEY  (`tagId`,`user`,`objectId`),
  KEY (`tagId`),
  KEY (`user`),
  KEY (`objectId`)
) ENGINE=MyISAM;

#2005-12-07 lfagundes

CREATE TABLE `tiki_categorized_objects` (
  `catObjectId` int(11) NOT NULL default '0',
  PRIMARY KEY  (`catObjectId`)
) ENGINE=MyISAM ;

#2005-12-09 lfagundes

INSERT INTO `tiki_categorized_objects` SELECT `objectId` FROM `tiki_objects`;

#2005-12-12 sylvieg
ALTER TABLE users_groups ADD registrationChoice CHAR(1) DEFAULT NULL;
CREATE INDEX login ON users_users (login);

#2005-12-15 amette - Freetag permissions and menu item
INSERT INTO users_permissions (permName, permDesc, level, type) VALUES ('tiki_p_view_freetags', 'Can browse freetags', 'basic', 'freetags');
INSERT INTO users_permissions (permName, permDesc, level, type) VALUES ('tiki_p_freetags_tag', 'Can tag objects', 'registered', 'freetags');
DELETE FROM `tiki_menu_options` WHERE menuId='42' and type='o' and name='Tags' and url='tiki-browse_freetags.php' and position='27' and perm='tiki_p_view_freetags' and groupname='' ;
INSERT INTO tiki_menu_options (menuId,type,name,url,position,section,perm,groupname) VALUES (42,'o','Freetags','tiki-browse_freetags.php',27,'feature_freetags','tiki_p_view_freetags','');

#2005-12-16 lfagundes
ALTER TABLE `tiki_history` ADD KEY user (`user`);

#2006-01-05 sg
ALTER TABLE users_groups ADD registrationUsersFieldIds text;
ALTER TABLE tiki_tracker_fields ADD description text;

#2006-02-11 lfagundes
alter table tiki_private_messages add `received` tinyint(1) not null default 0;
alter table tiki_private_messages add key received (`received`); 
alter table tiki_private_messages add key timestamp (`timestamp`); 

# "data" is reserved word in cpaint
alter table tiki_private_messages add `message` varchar(255);
update tiki_private_messages set `message`=`data`;
alter table `tiki_private_messages` drop `data`;

# sylvieg 3/2/06 & Jyhem 2007-06-14
CREATE TABLE tiki_contributions (
  contributionId int(12) NOT NULL auto_increment,
  name varchar(100) default NULL,
  description varchar(250) default NULL,
  PRIMARY KEY  (contributionId)
) ENGINE=MyISAM AUTO_INCREMENT=1;

CREATE TABLE tiki_contributions_assigned (
  contributionId int(12) NOT NULL,
  objectId int(12) NOT NULL,
  PRIMARY KEY  (objectId, contributionId)
) ENGINE=MyISAM;

DELETE FROM `tiki_menu_options` WHERE menuId='42' and type='r' and name='Admin' and url='tiki-admin.php' and position='1050' and section='' and perm='tiki_p_admin_contribution' and groupname='' ;
INSERT INTO `tiki_menu_options` (menuId,type,name,url,position,section,perm,groupname) VALUES (42,'r','Admin','tiki-admin.php',1050,'','tiki_p_admin_contribution','');
DELETE FROM `tiki_menu_options` WHERE menuId='42' and type='o' and name='Contribution' and url='tiki-admin_contribution.php' and position='1265' and section='feature_contribution' and perm='tiki_p_admin_contribution' and groupname='' ;
INSERT INTO tiki_menu_options (menuId,type,name,url,position,section,perm,groupname) VALUES (42,'o','Contribution','tiki-admin_contribution.php',1265,'feature_contribution','tiki_p_admin_contribution','');
INSERT INTO users_permissions (permName, permDesc, level, type) VALUES ('tiki_p_admin_contribution', 'Can admin contributions', 'admin', 'contribution');
ALTER TABLE `tiki_history` ADD `historyId` int(12) NOT NULL auto_increment FIRST, ADD  KEY (`historyId`);

#2006-03-12 lfagundes
CREATE TABLE tiki_page_drafts (
  user varchar(40) NOT NULL,
  pageName varchar(255) NOT NULL,
  data mediumtext,
  description varchar(200) default NULL,
  comment varchar(200) default NULL,
  PRIMARY KEY  (pageName, user)
) ENGINE=MyISAM;

#2006-03-19 lfagundes
alter table `tiki_page_drafts` add `lastModif` int(14); 

#2006-03-30 sylvieg
UPDATE tiki_menu_options SET perm='tiki_p_view_sheet' where url='tiki-sheets.php';
CREATE TABLE tiki_actionlog_params (
  actionId int(8) NOT NULL,

  name varchar(40) NOT NULL,
  value text,
  KEY  (actionId)
) ENGINE=MyISAM;
#2006-04-06
INSERT IGNORE INTO `tiki_actionlog_conf`(`action`, `objectType`, `status`) VALUES ('Renamed', 'wiki page', 'n');
#2006-04-11
DELETE FROM `tiki_menu_options` WHERE menuId='42' and type='o' and name='List TikiSheets' and url='tiki-sheets.php' and position='782' and section='feature_sheet' and perm='tiki_p_view_sheet' and groupname='' ;
INSERT IGNORE INTO tiki_menu_options (menuId,type,name,url,position,section,perm,groupname) VALUES (42,'o','List TikiSheets','tiki-sheets.php',782,'feature_sheet','tiki_p_view_sheet','');
INSERT IGNORE INTO `tiki_actionlog_conf`(`action`, `objectType`, `status`) VALUES ('Created', 'sheet', 'n');
INSERT IGNORE INTO `tiki_actionlog_conf`(`action`, `objectType`, `status`) VALUES ('Updated', 'sheet', 'n');
INSERT IGNORE INTO `tiki_actionlog_conf`(`action`, `objectType`, `status`) VALUES ('Removed', 'sheet', 'n');
INSERT IGNORE INTO `tiki_actionlog_conf`(`action`, `objectType`, `status`) VALUES ('Viewed', 'sheet', 'n');
ALTER TABLE `tiki_sheet_values` ADD `user` varchar(40) NULL default '' AFTER `format`;
#2006-04-25
CREATE TABLE tiki_sent_newsletters_errors (
  editionId int(12),
  email varchar(255),
  login varchar(40) default '',
  error char(1) default '',
  KEY  (editionId)
) ENGINE=MyISAM ;
#2006-04-27
ALTER TABLE `tiki_semaphores` ADD `objectType` varchar(20) default 'wiki page' AFTER `semName`;

#2006-05-25 sampaioprimo & Jyhem 2007-06-14
INSERT INTO users_permissions (permName, permDesc, level, type) VALUES ('tiki_p_wiki_view_source', 'Can view source of wiki pages', 'basic', 'wiki');

# 2006-06-07 sylvieg (merge from 1.9)
insert into users_permissions (permName,permDesc,level,type) values ('tiki_p_admin_objects','Can edit object permissions', 'admin', 'tiki');
insert into users_permissions (permName,permDesc,level,type) values ('tiki_p_admin_polls','Can admin polls', 'admin', 'tiki');
INSERT INTO users_permissions (permName,permDesc,level,type) values ('tiki_p_admin_rssmodules','Can admin rss modules', 'admin', 'tiki');

ALTER TABLE users_users MODIFY COLUMN `hash` varchar(34) default NULL;
#2006-07-28 mkalbere
ALTER TABLE `tiki_sent_newsletters` ADD `datatxt` longblob AFTER data;
ALTER TABLE `tiki_newsletters` ADD `allowTxt` varchar(1);

#sylvieg 9/13/06
INSERT IGNORE INTO `tiki_actionlog_conf`(`action`, `objectType`, `status`) VALUES ('Viewed', 'blog', 'n');
INSERT IGNORE INTO `tiki_actionlog_conf`(`action`, `objectType`, `status`) VALUES ('Posted', 'blog', 'n');
INSERT IGNORE INTO `tiki_actionlog_conf`(`action`, `objectType`, `status`) VALUES ('Updated', 'blog', 'n');
INSERT IGNORE INTO `tiki_actionlog_conf`(`action`, `objectType`, `status`) VALUES ('Removed', 'blog', 'n');

#ohertel 9/20/06 - type, required for special fgals (podcasts)
ALTER TABLE `tiki_file_galleries` ADD `type` varchar(20) NOT NULL default 'default' AFTER `name`;

#ohertel 09/23/06 adding Directory Batch Load feature for File Galleries
INSERT INTO users_permissions (permName, permDesc, level, type) VALUES ('tiki_p_batch_upload_file_dir', 'Can use Directory Batch Load', 'editors', 'file galleries');

#sylvieg 10/27/06 (delete not null for batch use)
ALTER TABLE tiki_logs CHANGE logip logip varchar(200);

#sylvieg 11/3/06
INSERT INTO users_permissions (permName, permDesc, level, type) VALUES ('tiki_p_watch_trackers', 'Can watch tracker', 'Registered', 'trackers');

# 2006-10-21 mose/cfreeze - changed pear auth params to be more generic
update tiki_preferences set name='auth_pear_host' where name='auth_ldap_host';
update tiki_preferences set name='auth_pear_port' where name='auth_ldap_port';

#sylvieg 2006-11-13
ALTER TABLE `tiki_file_galleries` ADD `parentId` int(14) NOT NULL default -1;

#sylvieg 2006-11-16 & Jyhem 2007-06-14
ALTER TABLE `tiki_file_galleries` ADD `lockable` char(1) default 'n';
ALTER TABLE `tiki_file_galleries` ADD `show_lockedby` char(1) default NULL;
ALTER TABLE `tiki_files` ADD `lockedby`  varchar(40) default NULL;
INSERT INTO users_permissions (permName, permDesc, level, type) VALUES ('tiki_p_edit_gallery_file', 'Can edit a gallery file', 'editors', 'file galleries');
DELETE FROM `tiki_menu_options` WHERE menuId='42' and type='o' and name='Directory batch' and url='tiki-batch_upload_files.php' and position='617' and section='feature_file_galleries_batch' and perm='tiki_p_batch_upload_file_dir' and groupname='' ;
INSERT INTO tiki_menu_options (menuId,type,name,url,position,section,perm,groupname) VALUES (42,'o','Directory batch','tiki-batch_upload_files.php',617,'feature_file_galleries_batch','tiki_p_batch_upload_file_dir','');

#mkalbere 2006-11-23 - To have multilingual tracker fields
ALTER TABLE `tiki_tracker_fields` ADD `isMultilingual` char(1) default 'n';
ALTER TABLE `tiki_tracker_item_fields` ADD `lang` char(16) default NULL;
ALTER TABLE `tiki_tracker_item_fields` DROP PRIMARY KEY;
ALTER TABLE `tiki_tracker_item_fields` ADD PRIMARY KEY  (itemId,fieldId,lang);
#sylvieg 2006-11-21
ALTER TABLE `tiki_file_galleries` ADD `archives` int(4) default -1;
ALTER TABLE `tiki_files` ADD `comment` varchar(200) default NULL;
ALTER TABLE `tiki_files` ADD `archiveId` int(14) default 0;

#lmoss 2006-11-28 - Increase article title length to 255
ALTER TABLE `tiki_articles` CHANGE `title` `title` varchar(255) default NULL;
ALTER TABLE `tiki_submissions` CHANGE `title` `title` varchar(255) default NULL;

# mose 2006-11-28 - new user contacts menu entry & Jyhem 2007-06-14
DELETE FROM `tiki_menu_options` WHERE menuId='42' and type='o' and name='Contacts' and url='tiki-contacts.php' and position='87' and (section='feature_contacts' or section='feature_mytiki,feature_contacts') and perm='' and groupname='Registered' ;
INSERT INTO tiki_menu_options (menuId,type,name,url,position,section,perm,groupname) VALUES (42,'o','Contacts','tiki-contacts.php',87,'feature_contacts','','Registered');

# mose 2006-11-28 - new contacts groups feature
CREATE TABLE tiki_webmail_contacts_groups (
  contactId int(12) NOT NULL,
  groupName varchar(255) NOT NULL,
  PRIMARY KEY  (contactId,groupName(200))
) ENGINE=MyISAM ;

#sylvieg 2006-11-30
ALTER TABLE `users_grouppermissions` CHANGE `permName` `permName` varchar(31)  NOT NULL default '';
ALTER TABLE `users_objectpermissions` CHANGE `permName` `permName` varchar(31)  NOT NULL default '';
ALTER TABLE `users_permissions` CHANGE `permName` `permName` varchar(31)  NOT NULL default '';
INSERT INTO users_permissions (permName, permDesc, level, type) VALUES ('tiki_p_assign_perm_file_gallery', 'Can assign perms to file gallery', 'admin', 'file galleries');

#sylvieg 2006-12-01
ALTER TABLE `tiki_file_galleries` ADD `sort_mode` char(20) default NULL;

# mose 2006-12-03 & Jyhem 2007-06-14
CREATE TABLE tiki_calendar_options (
    calendarId int(14) NOT NULL default 0,
    optionName varchar(120) NOT NULL default '',
    value varchar(255),
    PRIMARY KEY (calendarId,optionName)
) ENGINE=MyISAM ;

update `users_permissions` set type='tiki' where `permName`='tiki_p_view_tiki_calendar' and `type`='calendar';
DELETE FROM `tiki_menu_options` WHERE menuId='42' and type='o' and name='Tiki Calendar' and url='tiki-action_calendar.php' and position='36' and section='feature_action_calendar' and perm='tiki_p_view_tiki_calendar' and groupname='' ;
INSERT INTO tiki_menu_options (menuId,type,name,url,position,section,perm,groupname) VALUES (42,'o','Tiki Calendar','tiki-action_calendar.php',36,'feature_action_calendar','tiki_p_view_tiki_calendar','');

# mose 2006-12-05
INSERT INTO users_permissions (permName, permDesc, level, type) VALUES ('tiki_p_view_events', 'Can view events details', 'registered', 'calendar');

#sylvieg 2006-12-08 & jyhem 2008-02-29
ALTER TABLE `tiki_objects` DROP KEY `type`;
ALTER TABLE tiki_objects ADD KEY type (type, objectId);
ALTER TABLE `tiki_file_galleries` ADD `show_modified` char(1) default NULL;

#sylvieg 2006-12-12
ALTER TABLE tiki_files ADD author varchar(40) default NULL AFTER user;
ALTER TABLE `tiki_file_galleries` ADD `show_author` char(1) default NULL;
ALTER TABLE `tiki_file_galleries` ADD `show_creator` char(1) default NULL;
UPDATE tiki_file_galleries SET show_creator='y' WHERE show_created='y' and show_creator is NULL;

#sylvieg 2006-12-20
ALTER TABLE tiki_files ADD KEY created (created);
ALTER TABLE tiki_files ADD KEY archiveId (archiveId);
ALTER TABLE tiki_files ADD KEY galleryId (galleryId);
ALTER TABLE tiki_user_assigned_modules  DROP PRIMARY KEY , ADD PRIMARY KEY name (name(30),user,position);
ALTER TABLE tiki_theme_control_objects DROP PRIMARY KEY , ADD PRIMARY KEY objId (objId(100), type(100));
ALTER TABLE tiki_objects ADD KEY itemId (itemId, type);
ALTER TABLE users_users ADD KEY registrationDate (registrationDate);
ALTER TABLE tiki_rss_modules ADD KEY name (name);

#sylvie 2006-12-21
INSERT INTO users_permissions (permName, permDesc, level, type) VALUES ('tiki_p_view_actionlog', 'Can view action log', 'registered', 'tiki');

# mose 2006-12-27
# this is a destructive action, but required because backups has been removed
delete from tiki_menu_options where url='tiki-backup.php';

# mose 2006-12-28
# changing username max length
ALTER TABLE tiki_newsreader_marks DROP PRIMARY KEY ;
alter table tiki_newsreader_marks change user user varchar(200) not null default '';
ALTER TABLE tiki_newsreader_marks ADD PRIMARY KEY (user(100),serverId,groupName(100));
ALTER TABLE tiki_page_footnotes DROP PRIMARY KEY ;
alter table tiki_page_footnotes change user user varchar(200) not null default '';
ALTER TABLE tiki_page_footnotes ADD PRIMARY KEY (user(150),pageName(100));
alter table tiki_user_taken_quizzes change user user varchar(200) not null default '';
ALTER TABLE tiki_user_taken_quizzes DROP PRIMARY KEY ;
ALTER TABLE tiki_user_taken_quizzes ADD PRIMARY KEY (user,quizId(50));
alter table tiki_user_votings change user user varchar(200) not null default '';
ALTER TABLE tiki_user_votings DROP PRIMARY KEY ;
ALTER TABLE tiki_user_votings ADD PRIMARY KEY (user(100),id(100));
ALTER TABLE tiki_user_watches DROP PRIMARY KEY ;
ALTER TABLE tiki_user_watches change user user varchar(200) not null default '' ;
ALTER TABLE tiki_user_watches ADD PRIMARY KEY (user(100),event,object(50));
ALTER TABLE tiki_friends DROP PRIMARY KEY ;
ALTER TABLE tiki_friends change user user varchar(200) not null default '';
ALTER TABLE tiki_friends change friend friend varchar(200) not null default '';
ALTER TABLE tiki_friends ADD PRIMARY KEY (user(120),friend(120));
ALTER TABLE tiki_friendship_requests DROP PRIMARY KEY ;
ALTER TABLE tiki_friendship_requests change userFrom userFrom varchar(200) not null default '';
ALTER TABLE tiki_friendship_requests change userTo userTo varchar(200) not null default '' ;
ALTER TABLE tiki_friendship_requests ADD PRIMARY KEY (userFrom(120),userTo(120));

alter table users_users change login login varchar(200) not null default '';
alter table tiki_wiki_attachments change user user varchar(200) not null default '';
alter table tiki_webmail_messages change user user varchar(200) not null default '';
alter table tiki_webmail_contacts change user user varchar(200) not null default '';
alter table tiki_users change user user varchar(200) not null default '';
alter table tiki_userpoints change user user varchar(200) not null default '';
alter table tiki_userfiles change user user varchar(200) not null default '';
alter table tiki_user_tasks change user user varchar(200) not null default '';
alter table tiki_user_quizzes change user user varchar(200) not null default '';
alter table tiki_user_preferences change user user varchar(200) not null default '';
alter table tiki_user_postings change user user varchar(200) not null default '';
alter table tiki_user_notes change user user varchar(200) not null default '';
alter table tiki_user_menus change user user varchar(200) not null default '';
alter table tiki_user_mail_accounts change user user varchar(200) not null default '';
alter table tiki_user_bookmarks_urls change user user varchar(200) not null default '';
alter table tiki_user_bookmarks_folders change user user varchar(200) not null default '';
alter table tiki_user_assigned_modules change user user varchar(200) not null default '';
alter table tiki_tags change user user varchar(200) not null default '';
alter table tiki_suggested_faq_questions change user user varchar(200) not null default '';
alter table tiki_submissions change author author varchar(200) not null default '';
alter table tiki_shoutbox change user user varchar(200) null default '';
alter table tiki_sessions change user user varchar(200) not null default '';
alter table tiki_semaphores change user user varchar(200) not null default '';
alter table tiki_pages change user user varchar(200) not null default '';
alter table tiki_newsreader_servers change user user varchar(200) not null default '';
alter table tiki_minical_topics change user user varchar(200) not null default '';
alter table tiki_minical_events change user user varchar(200) not null default '';
alter table tiki_mailin_accounts change user user varchar(200) not null default '';
alter table tiki_live_support_requests change user user varchar(200) not null default '';
alter table tiki_live_support_operators change user user varchar(200) not null default '';
alter table tiki_live_support_messages change user user varchar(200) not null default '';
alter table tiki_images change user user varchar(200) not null default '';
alter table tiki_history change user user varchar(200) not null default '';
alter table tiki_galleries change user user varchar(200) not null default '';
alter table tiki_forums_reported change user user varchar(200) not null default '';
alter table tiki_forums_queue change user user varchar(200) not null default '';
alter table tiki_forum_reads change user user varchar(200) not null default '';
alter table tiki_files change user user varchar(200) not null default '';
alter table tiki_files change lockedby lockedby varchar(200) not null default '';
alter table tiki_file_galleries change user user varchar(200) not null default '';
alter table tiki_drawings change user user varchar(200) not null default '';
alter table tiki_copyrights change userName userName varchar(200) not null default '';
ALTER TABLE tiki_comments DROP KEY parentId;
ALTER TABLE tiki_comments DROP KEY parentId_2;
ALTER TABLE tiki_comments DROP KEY parentId_3;
ALTER TABLE tiki_comments DROP KEY parentId_4;
ALTER TABLE tiki_comments DROP KEY parentId_5;
ALTER TABLE tiki_comments DROP KEY parentId_6;
ALTER TABLE tiki_comments DROP KEY parentId_8;
ALTER TABLE tiki_comments DROP KEY no_repeats;
ALTER TABLE tiki_comments ADD UNIQUE KEY no_repeats(parentId, userName(40), title(100), commentDate, message_id(40), in_reply_to(40));
alter table tiki_comments change userName userName varchar(200) not null default '';
alter table tiki_charts_votes change user user varchar(200) not null default '';
alter table tiki_calendars change user user varchar(200) not null default '';
alter table tiki_calendar_roles change userName userName varchar(200) not null default '';
alter table tiki_calendar_items change user user varchar(200) not null default '';
alter table tiki_blogs change user user varchar(200) not null default '';
alter table tiki_banning change user user varchar(200) not null default '';
alter table tiki_actionlog change user user varchar(200) not null default '';
alter table messu_messages change user user varchar(200) not null default '';
alter table galaxia_workitems change user user varchar(200) not null default '';
alter table galaxia_user_roles change user user varchar(200) not null default '';
alter table galaxia_instance_comments change user user varchar(200) not null default '';
alter table galaxia_instance_activities change user user varchar(200) not null default '';
alter table tiki_freetagged_objects change user user varchar(200) not null default '';
 
#01/07/1007 sylvieg
alter table tiki_actionlog change user user varchar(200) default '';
alter table galaxia_instance_activities change user user varchar(200) default '';
alter table galaxia_instance_comments change user user varchar(200) default '';
alter table galaxia_workitems change user user varchar(200) default '';
alter table tiki_banning change user user varchar(200) default '';
alter table tiki_blog_posts change user user varchar(200) default '';
alter table tiki_blogs change user user varchar(200) default '';
alter table tiki_calendar_items change user user varchar(200) default '';
alter table tiki_comments change userName userName varchar(200) default '';
alter table tiki_copyrights change userName userName varchar(200) default '';
alter table tiki_drawings change user user varchar(200) default '';
alter table tiki_file_galleries change user user varchar(200) default '';
alter table tiki_files change user user varchar(200) default '';
alter table tiki_files change lockedby lockedby varchar(200) default '';
alter table tiki_forums_queue change user user varchar(200) default '';