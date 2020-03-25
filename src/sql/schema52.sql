-- Schema 52
START TRANSACTION;
    -- ALTER TABLE `users` DROP FOREIGN KEY `fk_users_teams_id`;
    ALTER TABLE `experiments` DROP FOREIGN KEY `fk_experiments_teams_id`;
    ALTER TABLE `experiments` DROP `team`;
    ALTER TABLE `experiments` ADD `canwrite` VARCHAR(255) NOT NULL DEFAULT 'user';
    ALTER TABLE `experiments` CHANGE `visibility` `canread` VARCHAR(255) NOT NULL DEFAULT 'team';
    ALTER TABLE `items` ADD `canwrite` VARCHAR(255) NOT NULL DEFAULT 'team';
    ALTER TABLE `items` CHANGE `visibility` `canread` VARCHAR(255) NOT NULL DEFAULT 'team';
    ALTER TABLE `users` CHANGE `default_vis` `default_read` VARCHAR(255) NULL DEFAULT 'team';
    ALTER TABLE `users` ADD `default_write` VARCHAR(255) NULL DEFAULT 'user';
    ALTER TABLE `users` ADD `display_size` VARCHAR(2) NOT NULL DEFAULT 'lg';
    ALTER TABLE `users` DROP `allow_edit`;
    ALTER TABLE `users` DROP `allow_group_edit`;
    ALTER TABLE `users` DROP `close_warning`;
    ALTER TABLE `team_events` ADD `experiment` int(10) UNSIGNED DEFAULT NULL;
    INSERT INTO config (conf_name, conf_value) VALUES ('email_domain', NULL);
    INSERT INTO config (conf_name, conf_value) VALUES ('saml_sync_teams', 0);
    CREATE TABLE `users2teams` (
      `users_id` int(10) UNSIGNED NOT NULL,
      `teams_id` int(10) UNSIGNED NOT NULL
    );
    INSERT INTO `users2teams`(`users_id`, `teams_id`) SELECT `userid`, `team` FROM `users`;
    ALTER TABLE `users2teams` ADD CONSTRAINT `fk_users2teams_teams_id` FOREIGN KEY (`teams_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
    ALTER TABLE `users2teams` ADD CONSTRAINT `fk_users2teams_users_id` FOREIGN KEY (`users_id`) REFERENCES `users` (`userid`) ON DELETE CASCADE ON UPDATE CASCADE;
    ALTER TABLE `api_keys` ADD `team` int(10) UNSIGNED NOT NULL;
    UPDATE `api_keys` SET `team` = (SELECT `team` FROM `users` WHERE users.userid = api_keys.userid);
    ALTER TABLE `api_keys` ADD CONSTRAINT `fk_api_keys_teams_id` FOREIGN KEY (`team`) REFERENCES `teams` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
    ALTER TABLE `users` DROP `team`;
    ALTER TABLE `experiments` ADD `lastchange` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;
    ALTER TABLE `experiments` CHANGE `datetime` `datetime` timestamp NULL;
    ALTER TABLE `items` ADD `lastchange` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;
    ALTER TABLE `users` ADD `json_editor` tinyint(1) UNSIGNED NOT NULL DEFAULT '0';
    CREATE TABLE `experiments_signatures` (
      `id` int(10) UNSIGNED NOT NULL,
      `datetime` datetime NOT NULL,
      `item_id` int(10) UNSIGNED NOT NULL,
      `userid` int(10) UNSIGNED NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    CREATE TABLE `items_signatures` (
      `id` int(10) UNSIGNED NOT NULL,
      `datetime` datetime NOT NULL,
      `item_id` int(10) UNSIGNED NOT NULL,
      `userid` int(10) UNSIGNED NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    ALTER TABLE `experiments_signatures` ADD PRIMARY KEY (`id`);
    ALTER TABLE `experiments_signatures` ADD KEY `fk_experiments_signatures_experiments_id` (`item_id`);
    ALTER TABLE `experiments_signatures` ADD KEY `fk_experiments_signatures_users_userid` (`userid`);
    ALTER TABLE `experiments_signatures` MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
    ALTER TABLE `experiments_signatures` ADD CONSTRAINT `fk_experiments_signatures_experiments_id` FOREIGN KEY (`item_id`) REFERENCES `experiments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
    ALTER TABLE `experiments_signatures` ADD CONSTRAINT `fk_experiments_signatures_users_userid` FOREIGN KEY (`userid`) REFERENCES `users` (`userid`) ON DELETE CASCADE ON UPDATE CASCADE;
    ALTER TABLE `items_signatures` ADD PRIMARY KEY (`id`);
    ALTER TABLE `items_signatures` ADD KEY `fk_items_signatures_items_id` (`item_id`);
    ALTER TABLE `items_signatures` ADD KEY `fk_items_signatures_users_userid` (`userid`);
    ALTER TABLE `items_signatures` MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
    ALTER TABLE `items_signatures` ADD CONSTRAINT `fk_items_signatures_items_id` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
    ALTER TABLE `items_signatures` ADD CONSTRAINT `fk_items_signatures_users_userid` FOREIGN KEY (`userid`) REFERENCES `users` (`userid`) ON DELETE CASCADE ON UPDATE CASCADE;
    UPDATE config SET conf_value = 52 WHERE conf_name = 'schema';
COMMIT;
