ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY 'admin';

CREATE TABLE `reg_keys` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `secret` VARCHAR(6),
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
);

CREATE TABLE `users` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `username` VARCHAR(50),
    `email` VARCHAR(50),
    `affiliation` VARCHAR(50),
    `password` VARCHAR(255),
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
);

CREATE TABLE `password_resets` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `email` VARCHAR(50),
    `token` VARCHAR(255),
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
);

CREATE TABLE `projects` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `username` VARCHAR(50),
    `project_id` VARCHAR(36),
    `project_name` VARCHAR(100),
    `target` VARCHAR(30),
    `type` VARCHAR(10),
    `source_type` VARCHAR(13),
    `video_loading` VARCHAR(8),
    `endless` VARCHAR(3),
    `n_of_entries` INT(10),
    `n_of_participant_runs` INT(10),
    `end_message` VARCHAR(255),
    `survey_link` VARCHAR(255),
    `sound` VARCHAR(3),
    `start_message` VARCHAR(255),
    `archived` VARCHAR(5),
    `upload_message` VARCHAR(500),
    `autofill_id` VARCHAR(11),
    `monochrome` VARCHAR(3),
    `ranktrace_smooth` VARCHAR(3),
    `ranktrace_rate` VARCHAR(5),
    `gtrace_control` VARCHAR(10),
    `gtrace_update` VARCHAR(3),
    `gtrace_click` VARCHAR(3),
    `gtrace_rate` INT(5),
    `tolerance` INT(3),
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
);

CREATE TABLE `project_entries` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `project_id` VARCHAR(36),
    `entry_id` VARCHAR(36),
    `source_type` VARCHAR(13),
    `source_url` VARCHAR(255),
    `original_name` VARCHAR(128),
    `type` VARCHAR(50),
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
);

CREATE TABLE `logs` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `project_id` VARCHAR(36),
    `participant_id` VARCHAR(36),
    `session_id` VARCHAR(36),
    `time_stamp` BIGINT(32),
    `videotime` INT(32),
    `annotation_value` INT(32),
    `original_name` VARCHAR(128),
    `annotation_type` VARCHAR(10),
    `entry_id` VARCHAR(36),
    PRIMARY KEY (`id`)
);

INSERT INTO `reg_keys` (`secret`) VALUES (123456);