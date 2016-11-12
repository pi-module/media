# Doc table
CREATE TABLE `{doc}` (
  `id`           INT(10) UNSIGNED    NOT NULL AUTO_INCREMENT,
  # URL to access, required
  `url`          VARCHAR(255)        NOT NULL DEFAULT '',
  # Absolute path to access, optional; for uploaded doc only
  `path`         VARCHAR(255)        NOT NULL DEFAULT '',
  # renamed file name
  `name`         VARCHAR(255)        NOT NULL DEFAULT '',
  # filename, for download
  `filename`     VARCHAR(255)        NOT NULL DEFAULT '',
  # Encoded file attributes: mimetype, size, width, height, etc.
  `attributes`   TEXT,
  `size`         INT(10) UNSIGNED    NOT NULL DEFAULT 0,
  `mimetype`     VARCHAR(255)        NOT NULL DEFAULT '',
  # Doc attributes
  `title`        VARCHAR(255)        NOT NULL DEFAULT '',
  `description`  VARCHAR(255)        NOT NULL DEFAULT '',
  `active`       TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
  `time_created` INT(10) UNSIGNED    NOT NULL DEFAULT 0,
  `time_updated` INT(10) UNSIGNED    NOT NULL DEFAULT 0,
  `time_deleted` INT(10) UNSIGNED    NOT NULL DEFAULT 0,
  # Application attributes
  `appkey`       VARCHAR(64)         NOT NULL DEFAULT '',
  `module`       VARCHAR(64)         NOT NULL DEFAULT '',
  # Application type for doc
  `type`         VARCHAR(64)         NOT NULL DEFAULT '',
  # Token to identify a group of docs just in case
  `token`        VARCHAR(64)         NOT NULL DEFAULT '',
  # User attributes
  `uid`          INT(10) UNSIGNED    NOT NULL DEFAULT 0,
  `ip`           VARCHAR(64)         NOT NULL DEFAULT '',
  # Usage stats
  `count`        INT(10) UNSIGNED    NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `active` (`active`),
  KEY `uid` (`uid`),
  KEY `module` (`module`),
  KEY `appkey` (`appkey`),
  KEY `application` (`appkey`, `module`, `type`)
);

# Extended meta for docs
CREATE TABLE `{meta}` (
  `id`    INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `doc`   INT(10) UNSIGNED NOT NULL DEFAULT 0,
  `name`  VARCHAR(64)      NOT NULL DEFAULT '',
  `value` TEXT,
  PRIMARY KEY (`id`),
  UNIQUE KEY `meta` (`doc`, `name`)
);

# Application table, for module management only
CREATE TABLE `{application}` (
  `id`     INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `appkey` VARCHAR(64)               DEFAULT NULL,
  `title`  VARCHAR(255)     NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `appkey`   (`appkey`)
);

CREATE TABLE `{asset}` (
  `id`     INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `doc`    INT(10) UNSIGNED NOT NULL DEFAULT 0,
  `item`   INT(10) UNSIGNED NOT NULL DEFAULT 0,
  `part`   VARCHAR(64)      NOT NULL DEFAULT '',
  `module` VARCHAR(64)      NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `doc_item` (`doc`, `item`, `part`, `module`),
  KEY `doc_item` (`item`, `part`, `module`),
  KEY `doc` (`doc`)
);