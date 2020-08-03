
CREATE TABLE IF NOT EXISTS tiki_h5p_libraries_hub_cache (
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
)
  ENGINE = MyISAM;