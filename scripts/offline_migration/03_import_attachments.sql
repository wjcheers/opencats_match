DROP TABLE IF EXISTS cats.cats_backup_attachment_before_offline_migration_20260427;
CREATE TABLE cats.cats_backup_attachment_before_offline_migration_20260427 AS SELECT * FROM cats.attachment;

DROP TABLE IF EXISTS cats.migration_attachment_import_plan;
CREATE TABLE cats.migration_attachment_import_plan (
  offline_attachment_id int(11) NOT NULL,
  offline_candidate_id int(11) NOT NULL,
  cats_candidate_id int(11) NOT NULL,
  source_directory_name varchar(64) DEFAULT NULL,
  source_stored_filename varchar(255) NOT NULL DEFAULT '',
  target_directory_name varchar(64) NOT NULL DEFAULT '',
  target_stored_filename varchar(255) NOT NULL DEFAULT '',
  action varchar(20) NOT NULL DEFAULT 'import',
  PRIMARY KEY (offline_attachment_id),
  KEY idx_cats_candidate_id (cats_candidate_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO cats.migration_attachment_import_plan (
  offline_attachment_id,
  offline_candidate_id,
  cats_candidate_id,
  source_directory_name,
  source_stored_filename,
  target_directory_name,
  target_stored_filename,
  action
)
SELECT
  a.attachment_id,
  a.data_item_id,
  m.cats_candidate_id,
  a.directory_name,
  a.stored_filename,
  CONCAT('site_1/offline_migration/', a.attachment_id, '/'),
  a.stored_filename,
  CASE
    WHEN existing.attachment_id IS NOT NULL THEN 'skip_duplicate'
    ELSE 'import'
  END
FROM offline.attachment a
INNER JOIN cats.migration_candidate_id_map m
  ON a.data_item_id = m.offline_candidate_id
LEFT JOIN cats.attachment existing
  ON existing.data_item_type = a.data_item_type
 AND existing.data_item_id = m.cats_candidate_id
 AND existing.md5_sum != ''
 AND a.md5_sum != ''
 AND existing.md5_sum = a.md5_sum
WHERE a.data_item_type = 100
AND m.action IN ('create', 'merge');

INSERT INTO cats.attachment (
  data_item_id,
  data_item_type,
  site_id,
  title,
  original_filename,
  stored_filename,
  content_type,
  resume,
  text,
  date_created,
  date_modified,
  profile_image,
  directory_name,
  md5_sum,
  file_size_kb,
  md5_sum_text,
  entered_by
)
SELECT
  p.cats_candidate_id,
  a.data_item_type,
  1,
  a.title,
  a.original_filename,
  a.stored_filename,
  a.content_type,
  a.resume,
  a.text,
  a.date_created,
  NOW(),
  a.profile_image,
  p.target_directory_name,
  a.md5_sum,
  a.file_size_kb,
  a.md5_sum_text,
  1
FROM cats.migration_attachment_import_plan p
INNER JOIN offline.attachment a
  ON p.offline_attachment_id = a.attachment_id
WHERE p.action = 'import';

UPDATE cats.candidate c
INNER JOIN (
  SELECT data_item_id AS candidate_id, MAX(resume) AS has_resume, COUNT(*) AS attachment_count
  FROM cats.attachment
  WHERE data_item_type = 100
  GROUP BY data_item_id
) a ON c.candidate_id = a.candidate_id
INNER JOIN cats.migration_candidate_id_map m ON c.candidate_id = m.cats_candidate_id
SET
  c.is_attachment = IF(a.attachment_count > 0, 1, c.is_attachment),
  c.is_resume = IF(a.has_resume > 0, 1, c.is_resume),
  c.date_modified = NOW()
WHERE m.action IN ('create', 'merge');

SELECT action, COUNT(*) AS cnt FROM cats.migration_attachment_import_plan GROUP BY action ORDER BY action;
