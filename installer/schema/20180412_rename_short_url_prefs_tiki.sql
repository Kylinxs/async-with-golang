
-- UPDATE IGNORE in case of already configured preferences with new name;
UPDATE IGNORE `tiki_preferences` SET `name` = 'sefurl_short_url' WHERE `name` = 'feature_short_url';
UPDATE IGNORE `tiki_preferences` SET `name` = 'sefurl_short_url_base_url' WHERE `name` = 'feature_short_url_domain';
-- DELETE old preferences name
DELETE FROM `tiki_preferences` WHERE `name` in ('feature_short_url', 'feature_short_url_domain');