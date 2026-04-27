SET SESSION group_concat_max_len = 1048576;

CREATE TABLE IF NOT EXISTS cats.migration_candidate_id_map (
  offline_candidate_id int(11) NOT NULL,
  cats_candidate_id int(11) NOT NULL,
  action varchar(20) NOT NULL,
  migrated_at datetime NOT NULL,
  PRIMARY KEY (offline_candidate_id),
  KEY idx_cats_candidate_id (cats_candidate_id),
  KEY idx_action (action)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS cats.migration_candidate_notes;
CREATE TABLE cats.migration_candidate_notes (
  offline_candidate_id int(11) NOT NULL,
  migration_note mediumtext,
  note_length int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (offline_candidate_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO cats.migration_candidate_notes (offline_candidate_id, migration_note, note_length)
SELECT
  s.offline_candidate_id,
  CONCAT(
    '\n\n============================================================\n',
    '[Offline Migration: offline_candidate_id=', s.offline_candidate_id, ']\n',
    'Action: ', p.action,
    IF(p.action = 'merge', CONCAT(' -> cats candidate #', p.target_cats_candidate_id), ''), '\n',
    'Original Created By: ', IFNULL(NULLIF(s.original_entered_by_name, ''), CONCAT('user_id=', s.original_entered_by)), '\n',
    'Original Owner: ', IFNULL(NULLIF(s.original_owner_name, ''), CONCAT('user_id=', IFNULL(s.original_owner, 0))), '\n',
    'Original Created At: ', IFNULL(s.date_created, ''), '\n',
    'Original Modified At: ', IFNULL(s.date_modified, ''), '\n',
    'Offline Candidate Name: ', TRIM(CONCAT(IFNULL(s.chinese_name, ''), ' ', IFNULL(s.first_name, ''), ' ', IFNULL(s.last_name, ''))), '\n',
    'Offline Email: ', IFNULL(s.email1, ''), IF(IFNULL(s.email2, '') != '', CONCAT(' / ', s.email2), ''), '\n',
    'Offline Phone: ', IFNULL(s.phone_cell, ''), IF(IFNULL(s.phone_home, '') != '', CONCAT(' / ', s.phone_home), ''), IF(IFNULL(s.phone_work, '') != '', CONCAT(' / ', s.phone_work), ''), '\n',
    'Offline Employer: ', IFNULL(s.current_employer, ''), '\n',
    'Offline Job Title: ', IFNULL(s.job_title, ''), '\n',
    'Attachment Count: ', s.attachment_count, '\n',
    'Activity Count: ', s.activity_count, '\n',
    'Submission Count: ', s.submission_count, '\n',
    IF(IFNULL(s.notes, '') != '', CONCAT('\n[Offline Original Notes]\n', s.notes, '\n'), ''),
    IFNULL(act.activity_block, '\n[Offline Activities]\n(none)\n'),
    IFNULL(sub.submission_block, '\n[Offline Submissions]\n(none)\n'),
    '============================================================\n'
  ) AS migration_note,
  0
FROM cats.migration_offline_candidate_stage s
INNER JOIN cats.migration_candidate_plan p ON s.offline_candidate_id = p.offline_candidate_id
LEFT JOIN (
  SELECT
    a.data_item_id AS offline_candidate_id,
    CONCAT('\n[Offline Activities]\n', GROUP_CONCAT(
      CONCAT(
        DATE_FORMAT(a.date_created, '%Y-%m-%d %H:%i:%s'),
        ' | ', IFNULL(at.short_description, CONCAT('type=', a.type)),
        ' | ', IFNULL(NULLIF(TRIM(CONCAT(IFNULL(u.first_name, ''), ' ', IFNULL(u.last_name, ''))), ''), CONCAT('user_id=', a.entered_by)),
        IF(a.joborder_id IS NOT NULL AND a.joborder_id != 0, CONCAT(' | JobOrder #', a.joborder_id), ''),
        '\n', IFNULL(a.notes, '')
      )
      ORDER BY a.date_created SEPARATOR '\n---\n'
    ), '\n') AS activity_block
  FROM offline.activity a
  LEFT JOIN offline.activity_type at ON a.type = at.activity_type_id
  LEFT JOIN offline.user u ON a.entered_by = u.user_id
  WHERE a.data_item_type = 100
  GROUP BY a.data_item_id
) act ON s.offline_candidate_id = act.offline_candidate_id
LEFT JOIN (
  SELECT
    cj.candidate_id AS offline_candidate_id,
    CONCAT('\n[Offline Submissions]\n', GROUP_CONCAT(
      CONCAT(
        DATE_FORMAT(cj.date_created, '%Y-%m-%d %H:%i:%s'),
        ' | JobOrder #', cj.joborder_id,
        ' | ', IFNULL(j.title, ''),
        IFNULL(CONCAT(' @ ', comp.name), ''),
        ' | Status: ', IFNULL(cjs.short_description, cj.status),
        IF(cj.date_submitted IS NOT NULL, CONCAT(' | Submitted: ', DATE_FORMAT(cj.date_submitted, '%Y-%m-%d %H:%i:%s')), ''),
        IF(IFNULL(cj.last_notes, '') != '', CONCAT('\n', cj.last_notes), '')
      )
      ORDER BY cj.date_created SEPARATOR '\n---\n'
    ), '\n') AS submission_block
  FROM offline.candidate_joborder cj
  LEFT JOIN offline.joborder j ON cj.joborder_id = j.joborder_id
  LEFT JOIN offline.company comp ON j.company_id = comp.company_id
  LEFT JOIN offline.candidate_joborder_status cjs ON cj.status = cjs.candidate_joborder_status_id
  GROUP BY cj.candidate_id
) sub ON s.offline_candidate_id = sub.offline_candidate_id
WHERE p.action IN ('create', 'merge');

UPDATE cats.migration_candidate_notes
SET note_length = CHAR_LENGTH(migration_note);

DROP TABLE IF EXISTS cats.cats_backup_candidate_before_offline_migration_20260427;
CREATE TABLE cats.cats_backup_candidate_before_offline_migration_20260427 AS SELECT * FROM cats.candidate;

INSERT INTO cats.candidate (
  site_id, last_name, first_name, middle_name, phone_home, phone_cell, phone_work,
  address, city, state, zip, source, date_available, can_relocate, notes, key_skills,
  current_employer, entered_by, owner, date_created, date_modified, email1, email2,
  web_site, import_id, is_hot, eeo_ethnic_type_id, eeo_veteran_type_id,
  eeo_disability_status, eeo_gender, desired_pay, current_pay, is_active,
  is_admin_hidden, best_time_to_call, chinese_name, job_title, extra_gender,
  marital_status, birth_year, highest_degree, major, nationality, facebook, github,
  linkedin, googleplus, twitter, cakeresume, link1, link2, link3, line, qq, skype,
  wechat, functions, job_level, is_agreement, is_resume, is_submitted, is_attachment,
  is_interviewed, date_submitted, is_offered
)
SELECT
  1, o.last_name, o.first_name, o.middle_name, o.phone_home, o.phone_cell, o.phone_work,
  o.address, o.city, o.state, o.zip, o.source, o.date_available, o.can_relocate,
  LEFT(CONCAT(IFNULL(o.notes, ''), n.migration_note), 65000),
  o.key_skills, o.current_employer,
  1, 1,
  IF(o.date_created = '0000-00-00 00:00:00', NOW(), o.date_created),
  NOW(),
  o.email1, o.email2, o.web_site, o.import_id, o.is_hot, o.eeo_ethnic_type_id, o.eeo_veteran_type_id,
  o.eeo_disability_status, o.eeo_gender, o.desired_pay, o.current_pay, o.is_active,
  o.is_admin_hidden, o.best_time_to_call, o.chinese_name, o.job_title, o.extra_gender,
  o.marital_status, o.birth_year, o.highest_degree, o.major, o.nationality, o.facebook, o.github,
  o.linkedin, o.googleplus, o.twitter, o.cakeresume, o.link1, o.link2, o.link3, o.line, o.qq, o.skype,
  o.wechat, o.functions, o.job_level, o.is_agreement,
  0, 0, 0, 0, NULL, 0
FROM cats.migration_candidate_plan p
INNER JOIN offline.candidate o ON p.offline_candidate_id = o.candidate_id
INNER JOIN cats.migration_candidate_notes n ON p.offline_candidate_id = n.offline_candidate_id
LEFT JOIN cats.migration_candidate_id_map existing_map ON p.offline_candidate_id = existing_map.offline_candidate_id
WHERE p.action = 'create'
AND existing_map.offline_candidate_id IS NULL;

INSERT INTO cats.migration_candidate_id_map (offline_candidate_id, cats_candidate_id, action, migrated_at)
SELECT p.offline_candidate_id, c.candidate_id, 'create', NOW()
FROM cats.migration_candidate_plan p
INNER JOIN cats.candidate c ON c.site_id = 1
  AND c.entered_by = 1
  AND c.owner = 1
  AND LOCATE(CONCAT('[Offline Migration: offline_candidate_id=', p.offline_candidate_id, ']'), c.notes) > 0
LEFT JOIN cats.migration_candidate_id_map existing_map ON p.offline_candidate_id = existing_map.offline_candidate_id
WHERE p.action = 'create'
AND existing_map.offline_candidate_id IS NULL;

UPDATE cats.candidate c
INNER JOIN cats.migration_candidate_plan p ON c.candidate_id = p.target_cats_candidate_id
INNER JOIN offline.candidate o ON p.offline_candidate_id = o.candidate_id
INNER JOIN cats.migration_candidate_notes n ON p.offline_candidate_id = n.offline_candidate_id
LEFT JOIN cats.migration_candidate_id_map existing_map ON p.offline_candidate_id = existing_map.offline_candidate_id
SET
  c.notes = CASE
    WHEN LOCATE(CONCAT('[Offline Migration: offline_candidate_id=', p.offline_candidate_id, ']'), IFNULL(c.notes, '')) > 0 THEN c.notes
    ELSE LEFT(CONCAT(IFNULL(c.notes, ''), n.migration_note), 65000)
  END,
  c.email1 = CASE WHEN IFNULL(c.email1, '') = '' AND IFNULL(o.email1, '') != '' THEN o.email1 ELSE c.email1 END,
  c.email2 = CASE
    WHEN IFNULL(c.email2, '') = '' AND IFNULL(o.email1, '') != '' AND LOWER(TRIM(o.email1)) NOT IN (LOWER(TRIM(IFNULL(c.email1, ''))), LOWER(TRIM(IFNULL(c.email2, '')))) THEN o.email1
    WHEN IFNULL(c.email2, '') = '' AND IFNULL(o.email2, '') != '' AND LOWER(TRIM(o.email2)) NOT IN (LOWER(TRIM(IFNULL(c.email1, ''))), LOWER(TRIM(IFNULL(c.email2, '')))) THEN o.email2
    ELSE c.email2
  END,
  c.phone_cell = CASE WHEN IFNULL(c.phone_cell, '') = '' AND IFNULL(o.phone_cell, '') != '' THEN o.phone_cell ELSE c.phone_cell END,
  c.phone_home = CASE WHEN IFNULL(c.phone_home, '') = '' AND IFNULL(o.phone_home, '') != '' THEN o.phone_home ELSE c.phone_home END,
  c.phone_work = CASE WHEN IFNULL(c.phone_work, '') = '' AND IFNULL(o.phone_work, '') != '' THEN o.phone_work ELSE c.phone_work END,
  c.chinese_name = CASE WHEN IFNULL(c.chinese_name, '') = '' AND IFNULL(o.chinese_name, '') != '' THEN o.chinese_name ELSE c.chinese_name END,
  c.current_employer = CASE WHEN IFNULL(c.current_employer, '') = '' AND IFNULL(o.current_employer, '') != '' THEN o.current_employer ELSE c.current_employer END,
  c.job_title = CASE WHEN IFNULL(c.job_title, '') = '' AND IFNULL(o.job_title, '') != '' THEN o.job_title ELSE c.job_title END,
  c.key_skills = CASE WHEN IFNULL(c.key_skills, '') = '' AND IFNULL(o.key_skills, '') != '' THEN o.key_skills ELSE c.key_skills END,
  c.date_modified = NOW(),
  c.owner = IFNULL(c.owner, 1)
WHERE p.action = 'merge'
AND existing_map.offline_candidate_id IS NULL;

INSERT INTO cats.migration_candidate_id_map (offline_candidate_id, cats_candidate_id, action, migrated_at)
SELECT p.offline_candidate_id, p.target_cats_candidate_id, 'merge', NOW()
FROM cats.migration_candidate_plan p
LEFT JOIN cats.migration_candidate_id_map existing_map ON p.offline_candidate_id = existing_map.offline_candidate_id
WHERE p.action = 'merge'
AND existing_map.offline_candidate_id IS NULL;

INSERT INTO cats.migration_candidate_id_map (offline_candidate_id, cats_candidate_id, action, migrated_at)
SELECT p.offline_candidate_id, 0, 'skip', NOW()
FROM cats.migration_candidate_plan p
LEFT JOIN cats.migration_candidate_id_map existing_map ON p.offline_candidate_id = existing_map.offline_candidate_id
WHERE p.action = 'skip'
AND existing_map.offline_candidate_id IS NULL;

UPDATE cats.candidate c
INNER JOIN cats.migration_candidate_id_map m ON c.candidate_id = m.cats_candidate_id
SET c.is_attachment = 0, c.is_resume = 0, c.is_submitted = 0, c.is_interviewed = 0, c.is_offered = 0, c.date_submitted = NULL
WHERE m.action = 'create';

SELECT action, COUNT(*) AS cnt FROM cats.migration_candidate_id_map GROUP BY action ORDER BY action;
