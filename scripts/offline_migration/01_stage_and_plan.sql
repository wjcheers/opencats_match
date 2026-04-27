SET SESSION group_concat_max_len = 1048576;

DROP TABLE IF EXISTS cats.migration_candidate_match_detail;
DROP TABLE IF EXISTS cats.migration_offline_candidate_stage;
DROP TABLE IF EXISTS cats.migration_cats_candidate_norm;
DROP TABLE IF EXISTS cats.migration_candidate_plan;

CREATE TABLE cats.migration_offline_candidate_stage (
  offline_candidate_id int(11) NOT NULL,
  offline_site_id int(11) NOT NULL DEFAULT 0,
  target_site_id int(11) NOT NULL DEFAULT 1,
  first_name varchar(64) NOT NULL DEFAULT '',
  last_name varchar(64) NOT NULL DEFAULT '',
  chinese_name varchar(64) DEFAULT NULL,
  current_employer varchar(128) DEFAULT NULL,
  job_title varchar(256) DEFAULT NULL,
  email1 varchar(128) DEFAULT NULL,
  email2 varchar(128) DEFAULT NULL,
  email1_norm varchar(128) DEFAULT NULL,
  email2_norm varchar(128) DEFAULT NULL,
  phone_home varchar(40) DEFAULT NULL,
  phone_cell varchar(40) DEFAULT NULL,
  phone_work varchar(40) DEFAULT NULL,
  phone_home_norm varchar(40) DEFAULT NULL,
  phone_cell_norm varchar(40) DEFAULT NULL,
  phone_work_norm varchar(40) DEFAULT NULL,
  linkedin_norm varchar(512) DEFAULT NULL,
  github_norm varchar(512) DEFAULT NULL,
  cakeresume_norm varchar(512) DEFAULT NULL,
  original_entered_by int(11) NOT NULL DEFAULT 0,
  original_entered_by_name varchar(255) NOT NULL DEFAULT '',
  original_owner int(11) DEFAULT NULL,
  original_owner_name varchar(255) NOT NULL DEFAULT '',
  date_created datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  date_modified datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  attachment_count int(11) NOT NULL DEFAULT 0,
  activity_count int(11) NOT NULL DEFAULT 0,
  submission_count int(11) NOT NULL DEFAULT 0,
  direct_match_count int(11) NOT NULL DEFAULT 0,
  direct_candidate_count int(11) NOT NULL DEFAULT 0,
  weak_match_count int(11) NOT NULL DEFAULT 0,
  weak_candidate_count int(11) NOT NULL DEFAULT 0,
  recommended_action varchar(20) NOT NULL DEFAULT 'create',
  recommended_cats_candidate_id int(11) NOT NULL DEFAULT 0,
  notes text,
  PRIMARY KEY (offline_candidate_id),
  KEY idx_email1_norm (email1_norm),
  KEY idx_email2_norm (email2_norm),
  KEY idx_phone_home_norm (phone_home_norm),
  KEY idx_phone_cell_norm (phone_cell_norm),
  KEY idx_phone_work_norm (phone_work_norm),
  KEY idx_linkedin_norm (linkedin_norm(255)),
  KEY idx_github_norm (github_norm(255)),
  KEY idx_cakeresume_norm (cakeresume_norm(255)),
  KEY idx_action (recommended_action)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE cats.migration_cats_candidate_norm (
  cats_candidate_id int(11) NOT NULL,
  site_id int(11) NOT NULL DEFAULT 1,
  first_name varchar(64) NOT NULL DEFAULT '',
  last_name varchar(64) NOT NULL DEFAULT '',
  current_employer varchar(128) DEFAULT NULL,
  email1_norm varchar(128) DEFAULT NULL,
  email2_norm varchar(128) DEFAULT NULL,
  phone_home_norm varchar(40) DEFAULT NULL,
  phone_cell_norm varchar(40) DEFAULT NULL,
  phone_work_norm varchar(40) DEFAULT NULL,
  linkedin_norm varchar(512) DEFAULT NULL,
  github_norm varchar(512) DEFAULT NULL,
  cakeresume_norm varchar(512) DEFAULT NULL,
  PRIMARY KEY (cats_candidate_id),
  KEY idx_email1_norm (email1_norm),
  KEY idx_email2_norm (email2_norm),
  KEY idx_phone_home_norm (phone_home_norm),
  KEY idx_phone_cell_norm (phone_cell_norm),
  KEY idx_phone_work_norm (phone_work_norm),
  KEY idx_linkedin_norm (linkedin_norm(255)),
  KEY idx_github_norm (github_norm(255)),
  KEY idx_cakeresume_norm (cakeresume_norm(255)),
  KEY idx_name_company (first_name, last_name, current_employer)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO cats.migration_offline_candidate_stage (
  offline_candidate_id, offline_site_id, first_name, last_name, chinese_name,
  current_employer, job_title, email1, email2, email1_norm, email2_norm,
  phone_home, phone_cell, phone_work, phone_home_norm, phone_cell_norm, phone_work_norm,
  linkedin_norm, github_norm, cakeresume_norm,
  original_entered_by, original_entered_by_name, original_owner, original_owner_name,
  date_created, date_modified, notes
)
SELECT
  c.candidate_id, c.site_id, c.first_name, c.last_name, c.chinese_name,
  c.current_employer, c.job_title, c.email1, c.email2,
  NULLIF(LOWER(TRIM(c.email1)), ''), NULLIF(LOWER(TRIM(c.email2)), ''),
  c.phone_home, c.phone_cell, c.phone_work,
  NULLIF(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(TRIM(c.phone_home)), '+', ''), '-', ''), ' ', ''), '(', ''), ')', ''), '.', ''), '/', ''), ''),
  NULLIF(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(TRIM(c.phone_cell)), '+', ''), '-', ''), ' ', ''), '(', ''), ')', ''), '.', ''), '/', ''), ''),
  NULLIF(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(TRIM(c.phone_work)), '+', ''), '-', ''), ' ', ''), '(', ''), ')', ''), '.', ''), '/', ''), ''),
  NULLIF(LOWER(TRIM(c.linkedin)), ''), NULLIF(LOWER(TRIM(c.github)), ''), NULLIF(LOWER(TRIM(c.cakeresume)), ''),
  c.entered_by,
  TRIM(CONCAT(IFNULL(entered_user.first_name, ''), ' ', IFNULL(entered_user.last_name, ''), ' (', IFNULL(entered_user.user_name, ''), ')')),
  c.owner,
  TRIM(CONCAT(IFNULL(owner_user.first_name, ''), ' ', IFNULL(owner_user.last_name, ''), ' (', IFNULL(owner_user.user_name, ''), ')')),
  c.date_created, c.date_modified, c.notes
FROM offline.candidate c
LEFT JOIN offline.user entered_user ON c.entered_by = entered_user.user_id
LEFT JOIN offline.user owner_user ON c.owner = owner_user.user_id;

INSERT INTO cats.migration_cats_candidate_norm (
  cats_candidate_id, site_id, first_name, last_name, current_employer,
  email1_norm, email2_norm, phone_home_norm, phone_cell_norm, phone_work_norm,
  linkedin_norm, github_norm, cakeresume_norm
)
SELECT
  c.candidate_id, c.site_id, c.first_name, c.last_name, c.current_employer,
  NULLIF(LOWER(TRIM(c.email1)), ''), NULLIF(LOWER(TRIM(c.email2)), ''),
  NULLIF(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(TRIM(c.phone_home)), '+', ''), '-', ''), ' ', ''), '(', ''), ')', ''), '.', ''), '/', ''), ''),
  NULLIF(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(TRIM(c.phone_cell)), '+', ''), '-', ''), ' ', ''), '(', ''), ')', ''), '.', ''), '/', ''), ''),
  NULLIF(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(TRIM(c.phone_work)), '+', ''), '-', ''), ' ', ''), '(', ''), ')', ''), '.', ''), '/', ''), ''),
  NULLIF(LOWER(TRIM(c.linkedin)), ''), NULLIF(LOWER(TRIM(c.github)), ''), NULLIF(LOWER(TRIM(c.cakeresume)), '')
FROM cats.candidate c
WHERE c.site_id = 1;

UPDATE cats.migration_offline_candidate_stage s
LEFT JOIN (SELECT data_item_id AS candidate_id, COUNT(*) AS cnt FROM offline.attachment WHERE data_item_type = 100 GROUP BY data_item_id) a ON s.offline_candidate_id = a.candidate_id
LEFT JOIN (SELECT data_item_id AS candidate_id, COUNT(*) AS cnt FROM offline.activity WHERE data_item_type = 100 GROUP BY data_item_id) act ON s.offline_candidate_id = act.candidate_id
LEFT JOIN (SELECT candidate_id, COUNT(*) AS cnt FROM offline.candidate_joborder GROUP BY candidate_id) sub ON s.offline_candidate_id = sub.candidate_id
SET s.attachment_count = IFNULL(a.cnt, 0), s.activity_count = IFNULL(act.cnt, 0), s.submission_count = IFNULL(sub.cnt, 0);

CREATE TABLE cats.migration_candidate_match_detail (
  id int(11) NOT NULL AUTO_INCREMENT,
  offline_candidate_id int(11) NOT NULL,
  cats_candidate_id int(11) NOT NULL,
  match_type varchar(40) NOT NULL,
  match_strength varchar(20) NOT NULL,
  offline_value varchar(512) DEFAULT NULL,
  cats_value varchar(512) DEFAULT NULL,
  created_at datetime NOT NULL,
  PRIMARY KEY (id),
  KEY idx_offline_candidate_id (offline_candidate_id),
  KEY idx_cats_candidate_id (cats_candidate_id),
  KEY idx_match_type (match_type),
  KEY idx_strength (match_strength)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO cats.migration_candidate_match_detail (offline_candidate_id, cats_candidate_id, match_type, match_strength, offline_value, cats_value, created_at)
SELECT DISTINCT s.offline_candidate_id, c.cats_candidate_id, 'email', 'direct', s.email1_norm, c.email1_norm, NOW()
FROM cats.migration_offline_candidate_stage s INNER JOIN cats.migration_cats_candidate_norm c ON s.email1_norm IN (c.email1_norm, c.email2_norm)
WHERE s.email1_norm IS NOT NULL;

INSERT INTO cats.migration_candidate_match_detail (offline_candidate_id, cats_candidate_id, match_type, match_strength, offline_value, cats_value, created_at)
SELECT DISTINCT s.offline_candidate_id, c.cats_candidate_id, 'email', 'direct', s.email2_norm, c.email2_norm, NOW()
FROM cats.migration_offline_candidate_stage s INNER JOIN cats.migration_cats_candidate_norm c ON s.email2_norm IN (c.email1_norm, c.email2_norm)
WHERE s.email2_norm IS NOT NULL;

INSERT INTO cats.migration_candidate_match_detail (offline_candidate_id, cats_candidate_id, match_type, match_strength, offline_value, cats_value, created_at)
SELECT DISTINCT s.offline_candidate_id, c.cats_candidate_id, 'phone', 'direct', s.phone_home_norm, c.phone_home_norm, NOW()
FROM cats.migration_offline_candidate_stage s INNER JOIN cats.migration_cats_candidate_norm c ON s.phone_home_norm IN (c.phone_home_norm, c.phone_cell_norm, c.phone_work_norm)
WHERE s.phone_home_norm IS NOT NULL AND LENGTH(s.phone_home_norm) >= 7;

INSERT INTO cats.migration_candidate_match_detail (offline_candidate_id, cats_candidate_id, match_type, match_strength, offline_value, cats_value, created_at)
SELECT DISTINCT s.offline_candidate_id, c.cats_candidate_id, 'phone', 'direct', s.phone_cell_norm, c.phone_cell_norm, NOW()
FROM cats.migration_offline_candidate_stage s INNER JOIN cats.migration_cats_candidate_norm c ON s.phone_cell_norm IN (c.phone_home_norm, c.phone_cell_norm, c.phone_work_norm)
WHERE s.phone_cell_norm IS NOT NULL AND LENGTH(s.phone_cell_norm) >= 7;

INSERT INTO cats.migration_candidate_match_detail (offline_candidate_id, cats_candidate_id, match_type, match_strength, offline_value, cats_value, created_at)
SELECT DISTINCT s.offline_candidate_id, c.cats_candidate_id, 'phone', 'direct', s.phone_work_norm, c.phone_work_norm, NOW()
FROM cats.migration_offline_candidate_stage s INNER JOIN cats.migration_cats_candidate_norm c ON s.phone_work_norm IN (c.phone_home_norm, c.phone_cell_norm, c.phone_work_norm)
WHERE s.phone_work_norm IS NOT NULL AND LENGTH(s.phone_work_norm) >= 7;

INSERT INTO cats.migration_candidate_match_detail (offline_candidate_id, cats_candidate_id, match_type, match_strength, offline_value, cats_value, created_at)
SELECT DISTINCT s.offline_candidate_id, c.cats_candidate_id, 'linkedin', 'direct', s.linkedin_norm, c.linkedin_norm, NOW()
FROM cats.migration_offline_candidate_stage s INNER JOIN cats.migration_cats_candidate_norm c ON s.linkedin_norm = c.linkedin_norm
WHERE s.linkedin_norm IS NOT NULL;

INSERT INTO cats.migration_candidate_match_detail (offline_candidate_id, cats_candidate_id, match_type, match_strength, offline_value, cats_value, created_at)
SELECT DISTINCT s.offline_candidate_id, c.cats_candidate_id, 'github', 'direct', s.github_norm, c.github_norm, NOW()
FROM cats.migration_offline_candidate_stage s INNER JOIN cats.migration_cats_candidate_norm c ON s.github_norm = c.github_norm
WHERE s.github_norm IS NOT NULL;

INSERT INTO cats.migration_candidate_match_detail (offline_candidate_id, cats_candidate_id, match_type, match_strength, offline_value, cats_value, created_at)
SELECT DISTINCT s.offline_candidate_id, c.cats_candidate_id, 'cakeresume', 'direct', s.cakeresume_norm, c.cakeresume_norm, NOW()
FROM cats.migration_offline_candidate_stage s INNER JOIN cats.migration_cats_candidate_norm c ON s.cakeresume_norm = c.cakeresume_norm
WHERE s.cakeresume_norm IS NOT NULL;

UPDATE cats.migration_offline_candidate_stage s
LEFT JOIN (SELECT offline_candidate_id, COUNT(*) AS cnt, COUNT(DISTINCT cats_candidate_id) AS candidate_cnt, MIN(cats_candidate_id) AS min_candidate_id FROM cats.migration_candidate_match_detail WHERE match_strength = 'direct' GROUP BY offline_candidate_id) direct_match ON s.offline_candidate_id = direct_match.offline_candidate_id
SET
  s.direct_match_count = IFNULL(direct_match.cnt, 0),
  s.direct_candidate_count = IFNULL(direct_match.candidate_cnt, 0),
  s.recommended_action = CASE
    WHEN IFNULL(direct_match.candidate_cnt, 0) = 1 THEN 'merge'
    WHEN IFNULL(direct_match.candidate_cnt, 0) > 1 THEN 'review'
    ELSE 'create'
  END,
  s.recommended_cats_candidate_id = CASE WHEN IFNULL(direct_match.candidate_cnt, 0) = 1 THEN direct_match.min_candidate_id ELSE 0 END;

CREATE TABLE cats.migration_candidate_plan (
  offline_candidate_id int(11) NOT NULL,
  action varchar(20) NOT NULL DEFAULT 'create',
  target_cats_candidate_id int(11) NOT NULL DEFAULT 0,
  plan_source varchar(20) NOT NULL DEFAULT 'auto',
  review_status varchar(20) NOT NULL DEFAULT 'pending',
  plan_notes text,
  attachment_count int(11) NOT NULL DEFAULT 0,
  activity_count int(11) NOT NULL DEFAULT 0,
  submission_count int(11) NOT NULL DEFAULT 0,
  created_at datetime NOT NULL,
  updated_at datetime NOT NULL,
  PRIMARY KEY (offline_candidate_id),
  KEY idx_action (action),
  KEY idx_target_cats_candidate_id (target_cats_candidate_id),
  KEY idx_review_status (review_status)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO cats.migration_candidate_plan
SELECT
  offline_candidate_id,
  CASE WHEN recommended_action = 'merge' THEN 'merge' WHEN recommended_action = 'create' THEN 'create' ELSE 'review' END,
  CASE WHEN recommended_action = 'merge' THEN recommended_cats_candidate_id ELSE 0 END,
  'auto',
  CASE WHEN recommended_action IN ('merge', 'create') THEN 'ready' ELSE 'pending' END,
  CONCAT('Auto plan from staging recommendation: ', recommended_action),
  attachment_count,
  activity_count,
  submission_count,
  NOW(),
  NOW()
FROM cats.migration_offline_candidate_stage;

UPDATE cats.migration_candidate_plan SET action='merge', target_cats_candidate_id=42786, plan_source='manual', review_status='ready', plan_notes='Manual review: merge offline 32 into cats 42786', updated_at=NOW() WHERE offline_candidate_id=32;
UPDATE cats.migration_candidate_plan SET action='merge', target_cats_candidate_id=72902, plan_source='manual', review_status='ready', plan_notes='Manual review: merge offline 81 into cats 72902', updated_at=NOW() WHERE offline_candidate_id=81;
UPDATE cats.migration_candidate_plan SET action='merge', target_cats_candidate_id=82720, plan_source='manual', review_status='ready', plan_notes='Manual review: merge offline 153 into cats 82720', updated_at=NOW() WHERE offline_candidate_id=153;
UPDATE cats.migration_candidate_plan SET action='merge', target_cats_candidate_id=78470, plan_source='manual', review_status='ready', plan_notes='Manual review: merge offline 164 into cats 78470', updated_at=NOW() WHERE offline_candidate_id=164;
UPDATE cats.migration_candidate_plan SET action='merge', target_cats_candidate_id=55269, plan_source='manual', review_status='ready', plan_notes='Manual review: merge offline 310 into cats 55269', updated_at=NOW() WHERE offline_candidate_id=310;
UPDATE cats.migration_candidate_plan SET action='merge', target_cats_candidate_id=53660, plan_source='manual', review_status='ready', plan_notes='Manual review: merge offline 413 into cats 53660', updated_at=NOW() WHERE offline_candidate_id=413;
UPDATE cats.migration_candidate_plan SET action='merge', target_cats_candidate_id=72927, plan_source='manual', review_status='ready', plan_notes='Manual review: merge offline 485 into cats 72927', updated_at=NOW() WHERE offline_candidate_id=485;
UPDATE cats.migration_candidate_plan SET action='merge', target_cats_candidate_id=45979, plan_source='manual', review_status='ready', plan_notes='Manual review: merge offline 588 into cats 45979', updated_at=NOW() WHERE offline_candidate_id=588;
UPDATE cats.migration_candidate_plan SET action='skip', target_cats_candidate_id=0, plan_source='manual', review_status='ready', plan_notes='Manual review: skip/delete offline 704 from migration', updated_at=NOW() WHERE offline_candidate_id=704;

SELECT action, COUNT(*) AS cnt FROM cats.migration_candidate_plan GROUP BY action ORDER BY action;
