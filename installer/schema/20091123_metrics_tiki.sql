

DROP TABLE IF EXISTS `metrics_assigned`;
CREATE TABLE IF NOT EXISTS `metrics_assigned` (
    `assigned_id` int(11) NOT NULL AUTO_INCREMENT,
    `metric_id` int(11) NOT NULL,
    `tab_id` int(11) NOT NULL,
    PRIMARY KEY (`assigned_id`),
    KEY `metric_id` (`metric_id`),
    KEY `tab_id` (`tab_id`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `metrics_metric`;
CREATE TABLE IF NOT EXISTS `metrics_metric` (
    `metric_id` int(11) NOT NULL AUTO_INCREMENT,
    `metric_name` varchar(255) NOT NULL,
    `metric_range` varchar(1) NOT NULL DEFAULT '+' COMMENT 'values: + (daily), @ (monthly&weekly), - (weekly)',
    `metric_datatype` varchar(1) NOT NULL DEFAULT 'i' COMMENT 'values: i(nteger), %(percentage), f(loat), L(ist)',
    `metric_lastupdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `metric_query` text,
    PRIMARY KEY (`metric_id`),
    UNIQUE KEY `metric_name` (`metric_name`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `metrics_tab`;
CREATE TABLE IF NOT EXISTS `metrics_tab` (
    `tab_id` int(11) NOT NULL AUTO_INCREMENT,
    `tab_name` varchar(255) NOT NULL,
    `tab_order` int(11) NOT NULL DEFAULT '0',
    `tab_content` longtext NOT NULL,
    PRIMARY KEY (`tab_id`),
    UNIQUE KEY `tab_name` (`tab_name`)
) ENGINE=MyISAM;
