CREATE TABLE IF NOT EXISTS `#__miniorange_mediarestriction_customer_details` (
`id` int(11) UNSIGNED NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `admin_phone` VARCHAR(255) NOT NULL,
    `customer_key` VARCHAR(255) NOT NULL,
    `customer_token` VARCHAR(255) NOT NULL,
    `api_key` VARCHAR(255) NOT NULL,
    `status` VARCHAR(255) NOT NULL,
    `login_status` tinyint(1) DEFAULT 0,
    `registration_status` VARCHAR(255) NOT NULL,
    `transaction_id` VARCHAR(255) NOT NULL,
    `email_count` int(11),
    `sms_count` int(11),
    `submited_feedback` int(11),
    `save_configuration` int(11),
    `created_file` int(11),
    `rolebased_lc_key` VARCHAR(255) NOT NULL DEFAULT '',
    `licenseExpiry` TIMESTAMP NULL DEFAULT NULL,
    `supportExpiry` TIMESTAMP NULL DEFAULT NULL,
    `licensePlan` VARCHAR(64) NOT NULL DEFAULT '',
    `trists` TEXT NULL,
    PRIMARY KEY (`id`)
) DEFAULT COLLATE=utf8_general_ci;

CREATE TABLE IF NOT EXISTS `#__miniorange_mediarestriction_settings` (
    `id` int(11) UNSIGNED NOT NULL,
    `enable_media_restriction` tinyint(1) DEFAULT 0,
    `mo_media_restriction_file_types` TEXT NULL,
    `mo_redirection_option` TEXT NULL,
    `mo_redirection_option_value` TEXT NULL,
    `enable_role_based_restriction` tinyint(1) DEFAULT 0,
    `mo_folder_name` TEXT NULL,
    `mo_role_based_restriction_values` TEXT NULL,
    PRIMARY KEY (`id`)
) DEFAULT COLLATE=utf8_general_ci;


INSERT IGNORE INTO `#__miniorange_mediarestriction_customer_details`(`id`,`login_status`) values (1,0);
INSERT IGNORE INTO `#__miniorange_mediarestriction_settings`(`id`,`enable_media_restriction`,`mo_media_restriction_file_types`) values (1,1,'png,pdf,jpg,doc,gif') ;

