# Doc table
CREATE TABLE `{doc}` (
    `id` int(10) UNSIGNED NOT NULL,
    `path` varchar(255) NOT NULL DEFAULT '',
    `filename` varchar(255) NOT NULL DEFAULT '',
    `attributes` text,
    `mimetype` varchar(255) NOT NULL DEFAULT '',
    `title` varchar(255) NOT NULL DEFAULT '',
    `description` varchar(255) NOT NULL DEFAULT '',
    `active` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
    `time_created` int(10) UNSIGNED NOT NULL DEFAULT '0',
    `time_updated` int(10) UNSIGNED NOT NULL DEFAULT '0',
    `time_deleted` int(10) UNSIGNED NOT NULL DEFAULT '0',
    `appkey` varchar(64) NOT NULL DEFAULT '',
    `uid` int(10) UNSIGNED NOT NULL DEFAULT '0',
    `count` int(10) NOT NULL DEFAULT '0',
    `season` tinyint(4) DEFAULT NULL,
    `updated_by` int(11) DEFAULT NULL,
    `license_type` varchar(255) DEFAULT NULL,
    `copyright` varchar(255) DEFAULT NULL,
    `geoloc_latitude` float DEFAULT NULL,
    `geoloc_longitude` float DEFAULT NULL,
    `cropping` text,
    `featured` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `{doc}`
  ADD PRIMARY KEY (`id`),
  ADD KEY `active` (`active`),
  ADD KEY `uid` (`uid`),
  ADD KEY `appkey` (`appkey`),
  ADD KEY `application` (`appkey`),
  ADD FULLTEXT KEY `search_idx` (`title`,`description`),
  ADD FULLTEXT KEY `search_title_idx` (`title`),
  ADD FULLTEXT KEY `search_description_idx` (`description`);


# Test table
CREATE TABLE `{test}` (
    `id` INT NOT NULL AUTO_INCREMENT ,
    `title` VARCHAR(255) NOT NULL ,
    `main_image` INT NULL,
    `additional_images` VARCHAR(255) NULL,
    PRIMARY KEY (`id`)
) ENGINE = InnoDB;

# Link table
CREATE TABLE `{link}` (
    `id` INT NOT NULL AUTO_INCREMENT ,
    `module` VARCHAR(20) NOT NULL ,
    `object_name` VARCHAR(50) NOT NULL ,
    `object_id` INT NOT NULL ,
    `field` VARCHAR(50) NOT NULL ,
    `media_id` INT NOT NULL ,
    PRIMARY KEY (`id`)
) ENGINE = InnoDB;

ALTER TABLE `{link}` ADD INDEX( `media_id`);


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