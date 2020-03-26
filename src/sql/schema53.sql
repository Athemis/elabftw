-- Schema 53
    CREATE TABLE `experiments_signatures` (
      `id` int(10) UNSIGNED NOT NULL,
      `datetime` datetime NOT NULL,
      `revoked` tinyint(1) DEFAULT '0',
      `datetime_revoked` datetime DEFAULT NULL,
      `item_id` int(10) UNSIGNED NOT NULL,
      `revision_id` int(10) UNSIGNED NOT NULL,
      `userid` int(10) UNSIGNED NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    CREATE TABLE `items_signatures` (
      `id` int(10) UNSIGNED NOT NULL,
      `datetime` datetime NOT NULL,
      `revoked` tinyint(1) DEFAULT '0',
      `datetime_revoked` datetime DEFAULT NULL,
      `item_id` int(10) UNSIGNED NOT NULL,
      `revision_id` int(10) UNSIGNED NOT NULL,
      `userid` int(10) UNSIGNED NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    ALTER TABLE `experiments_signatures` ADD PRIMARY KEY (`id`);
    ALTER TABLE `experiments_signatures` ADD KEY `fk_experiments_signatures_experiments_id` (`item_id`);
    ALTER TABLE `experiments_signatures` ADD KEY `fk_experiments_signatures_revisions_id` (`revision_id`);
    ALTER TABLE `experiments_signatures` ADD KEY `fk_experiments_signatures_users_userid` (`userid`);
    ALTER TABLE `experiments_signatures` MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
    ALTER TABLE `experiments_signatures` ADD CONSTRAINT `fk_experiments_signatures_experiments_id` FOREIGN KEY (`item_id`) REFERENCES `experiments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
    ALTER TABLE `experiments_signatures` ADD CONSTRAINT `fk_experiments_signatures_revisions_id` FOREIGN KEY (`revision_id`) REFERENCES `experiments_revisions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
    ALTER TABLE `experiments_signatures` ADD CONSTRAINT `fk_experiments_signatures_users_userid` FOREIGN KEY (`userid`) REFERENCES `users` (`userid`) ON DELETE CASCADE ON UPDATE CASCADE;
    ALTER TABLE `items_signatures` ADD PRIMARY KEY (`id`);
    ALTER TABLE `items_signatures` ADD KEY `fk_items_signatures_items_id` (`item_id`);
    ALTER TABLE `items_signatures` ADD KEY `fk_items_signatures_revisions_id` (`revision_id`);
    ALTER TABLE `items_signatures` ADD KEY `fk_items_signatures_users_userid` (`userid`);
    ALTER TABLE `items_signatures` MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
    ALTER TABLE `items_signatures` ADD CONSTRAINT `fk_items_signatures_items_id` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
    ALTER TABLE `items_signatures` ADD CONSTRAINT `fk_items_signatures_revisions_id` FOREIGN KEY (`revision_id`) REFERENCES `items_revisions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
    ALTER TABLE `items_signatures` ADD CONSTRAINT `fk_items_signatures_users_userid` FOREIGN KEY (`userid`) REFERENCES `users` (`userid`) ON DELETE CASCADE ON UPDATE CASCADE;
    UPDATE config SET conf_value = 53 WHERE conf_name = 'schema';
COMMIT;
