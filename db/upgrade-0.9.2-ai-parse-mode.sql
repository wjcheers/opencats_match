-- Adds parse_mode column to ai_resume_parse_log so usage reports can
-- distinguish between fast (快速) and full (完整) AI parses.

ALTER TABLE `ai_resume_parse_log`
    ADD COLUMN `parse_mode` varchar(10) NOT NULL DEFAULT '' AFTER `status`,
    ADD KEY `idx_parse_mode` (`parse_mode`);
