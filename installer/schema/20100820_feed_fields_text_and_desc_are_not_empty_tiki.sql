DELETE FROM `tiki_preferences` WHERE `name` = 'feed_articles_desc' AND (`value` = '' OR `value` IS NULL);
DELETE FROM `tiki_preferences` WHERE `name` = 'feed_articles_title' AND (`value` = '' OR `value` IS NULL);
DELETE FROM `tiki