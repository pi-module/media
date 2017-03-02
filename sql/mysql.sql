# Doc table
CREATE TABLE `{doc}` (
  `id` int(10) UNSIGNED NOT NULL,
  `path`         VARCHAR(255)        NOT NULL DEFAULT '',
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
  `count` int(10) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `{doc}` ADD `season` TINYINT NULL AFTER `count`, ADD `updated_by` INT NULL AFTER `season`, ADD `license_type` VARCHAR(255) NULL AFTER `updated_by`, ADD `copyright` VARCHAR(255) NULL AFTER `license_type`, ADD `geoloc_latitude` FLOAT NULL AFTER `copyright`, ADD `geoloc_longitude` FLOAT NULL AFTER `geoloc_latitude`, ADD `cropping` TEXT NULL AFTER `geoloc_longitude`, ADD `featured` TINYINT NOT NULL AFTER `cropping`;

ALTER TABLE `{doc}`
  ADD PRIMARY KEY (`id`),
  ADD KEY `active` (`active`),
  ADD KEY `uid` (`uid`),
  ADD KEY `appkey` (`appkey`),
  ADD KEY `application` (`appkey`);

ALTER TABLE `{doc}`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

# Test table
CREATE TABLE `{test}` (
    `id` INT NOT NULL AUTO_INCREMENT ,
    `title` VARCHAR(255) NOT NULL ,
    PRIMARY KEY (`id`)
) ENGINE = InnoDB;

ALTER TABLE `{test}` ADD `main_image` INT NULL AFTER `title`, ADD `additional_images` VARCHAR NULL AFTER `main_image`;


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