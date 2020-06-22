UPDATE `tiki_sefurl_regex_out` SET `type` = 'file gallery' WHERE `right` = 'file$1' AND `type` <> 'file gallery';
INSERT IGNORE INTO `tiki_sefurl_regex_out` (`left`, `right`, `type`, `feature`, `order`)
    VALUES ('tiki-view_forum_thread.php\\?comments_parentId=(\\d+)', 'forumthread$1', 'forumthread', 'feature_forums', 0);
