
INSERT IGNORE INTO `tiki_sefurl_regex_out` (`left`, `right`, `type`, `feature`, `order`)
    VALUES('tiki-admin_tracker_fields.php\\?trackerId=(\\d+)','trackerfields$1', 'trackerfields', 'feature_trackers', 200);