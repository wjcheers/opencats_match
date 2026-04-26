CREATE TABLE `ai_job_title_dictionary` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `canonical_key` varchar(100) NOT NULL,
  `name_en` varchar(255) NOT NULL,
  `name_zh` varchar(255) NOT NULL DEFAULT '',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_canonical_key` (`canonical_key`),
  KEY `idx_name_en` (`name_en`),
  KEY `idx_name_zh` (`name_zh`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `ai_job_title_alias` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `dictionary_id` int(10) unsigned NOT NULL,
  `alias_value` varchar(255) NOT NULL,
  `alias_lang` varchar(20) NOT NULL DEFAULT 'unknown',
  `normalized_value` varchar(255) NOT NULL DEFAULT '',
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_dictionary_id` (`dictionary_id`),
  KEY `idx_alias_value` (`alias_value`),
  KEY `idx_normalized_value` (`normalized_value`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `ai_function_dictionary` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `canonical_key` varchar(100) NOT NULL,
  `name_en` varchar(255) NOT NULL,
  `name_zh` varchar(255) NOT NULL DEFAULT '',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_canonical_key` (`canonical_key`),
  KEY `idx_name_en` (`name_en`),
  KEY `idx_name_zh` (`name_zh`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `ai_function_alias` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `dictionary_id` int(10) unsigned NOT NULL,
  `alias_value` varchar(255) NOT NULL,
  `alias_lang` varchar(20) NOT NULL DEFAULT 'unknown',
  `normalized_value` varchar(255) NOT NULL DEFAULT '',
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_dictionary_id` (`dictionary_id`),
  KEY `idx_alias_value` (`alias_value`),
  KEY `idx_normalized_value` (`normalized_value`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `ai_skill_dictionary` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `canonical_key` varchar(100) NOT NULL,
  `name_en` varchar(255) NOT NULL,
  `name_zh` varchar(255) NOT NULL DEFAULT '',
  `is_key_skill` tinyint(1) NOT NULL DEFAULT '1',
  `priority` int(11) NOT NULL DEFAULT '100',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_canonical_key` (`canonical_key`),
  KEY `idx_name_en` (`name_en`),
  KEY `idx_name_zh` (`name_zh`),
  KEY `idx_is_key_skill` (`is_key_skill`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `ai_skill_alias` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `dictionary_id` int(10) unsigned NOT NULL,
  `alias_value` varchar(255) NOT NULL,
  `alias_lang` varchar(20) NOT NULL DEFAULT 'unknown',
  `normalized_value` varchar(255) NOT NULL DEFAULT '',
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_dictionary_id` (`dictionary_id`),
  KEY `idx_alias_value` (`alias_value`),
  KEY `idx_normalized_value` (`normalized_value`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `ai_resume_parse_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `site_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `source_type` varchar(20) NOT NULL,
  `original_filename` varchar(255) NOT NULL DEFAULT '',
  `stored_filename` varchar(255) NOT NULL DEFAULT '',
  `document_language` varchar(20) NOT NULL DEFAULT '',
  `provider` varchar(50) NOT NULL,
  `model` varchar(100) NOT NULL,
  `input_tokens` int(11) NOT NULL DEFAULT '0',
  `output_tokens` int(11) NOT NULL DEFAULT '0',
  `status` varchar(20) NOT NULL DEFAULT 'success',
  `saved_candidate_id` int(11) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_site_user_created` (`site_id`,`user_id`,`created_at`),
  KEY `idx_status` (`status`),
  KEY `idx_saved_candidate_id` (`saved_candidate_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `ai_resume_parse_result` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `parse_log_id` int(10) unsigned NOT NULL,
  `first_name` varchar(255) NOT NULL DEFAULT '',
  `last_name` varchar(255) NOT NULL DEFAULT '',
  `chinese_name` varchar(255) NOT NULL DEFAULT '',
  `email` varchar(255) NOT NULL DEFAULT '',
  `phone` varchar(255) NOT NULL DEFAULT '',
  `address` text,
  `city` varchar(255) NOT NULL DEFAULT '',
  `state` varchar(255) NOT NULL DEFAULT '',
  `zip_code` varchar(50) NOT NULL DEFAULT '',
  `current_employer` varchar(255) NOT NULL DEFAULT '',
  `job_title_raw` varchar(255) NOT NULL DEFAULT '',
  `job_title_zh` varchar(255) NOT NULL DEFAULT '',
  `job_title_en` varchar(255) NOT NULL DEFAULT '',
  `job_title_canonical_key` varchar(100) NOT NULL DEFAULT '',
  `function_raw` varchar(255) NOT NULL DEFAULT '',
  `function_zh` varchar(255) NOT NULL DEFAULT '',
  `function_en` varchar(255) NOT NULL DEFAULT '',
  `function_canonical_key` varchar(100) NOT NULL DEFAULT '',
  `job_level` varchar(50) NOT NULL DEFAULT '',
  `website` varchar(255) NOT NULL DEFAULT '',
  `linkedin` varchar(255) NOT NULL DEFAULT '',
  `github` varchar(255) NOT NULL DEFAULT '',
  `highest_degree` varchar(255) NOT NULL DEFAULT '',
  `major` varchar(255) NOT NULL DEFAULT '',
  `skills_raw` text,
  `skills_zh` text,
  `skills_en` text,
  `key_skills_zh` text,
  `key_skills_en` text,
  `facebook` varchar(512) NOT NULL DEFAULT '',
  `googleplus` varchar(512) NOT NULL DEFAULT '',
  `twitter` varchar(512) NOT NULL DEFAULT '',
  `cakeresume` varchar(512) NOT NULL DEFAULT '',
  `link1` varchar(512) NOT NULL DEFAULT '',
  `link2` varchar(512) NOT NULL DEFAULT '',
  `link3` varchar(512) NOT NULL DEFAULT '',
  `career_summary` text,
  `skill_summary` text,
  `job_title_confidence` decimal(5,4) NOT NULL DEFAULT '0.0000',
  `function_confidence` decimal(5,4) NOT NULL DEFAULT '0.0000',
  `job_level_confidence` decimal(5,4) NOT NULL DEFAULT '0.0000',
  `skills_confidence` decimal(5,4) NOT NULL DEFAULT '0.0000',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_parse_log_id` (`parse_log_id`),
  KEY `idx_job_title_canonical_key` (`job_title_canonical_key`),
  KEY `idx_function_canonical_key` (`function_canonical_key`),
  KEY `idx_job_level` (`job_level`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `ai_parse_suggestion` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `site_id` int(11) NOT NULL,
  `suggestion_type` varchar(20) NOT NULL,
  `raw_value` varchar(255) NOT NULL,
  `suggested_canonical_key` varchar(100) NOT NULL DEFAULT '',
  `suggested_name_en` varchar(255) NOT NULL DEFAULT '',
  `suggested_name_zh` varchar(255) NOT NULL DEFAULT '',
  `confidence_score` decimal(5,4) NOT NULL DEFAULT '0.0000',
  `parse_log_id` int(10) unsigned NOT NULL DEFAULT '0',
  `created_by` int(11) NOT NULL DEFAULT '0',
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_site_type_status` (`site_id`,`suggestion_type`,`status`),
  KEY `idx_raw_value` (`raw_value`),
  KEY `idx_canonical_key` (`suggested_canonical_key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
