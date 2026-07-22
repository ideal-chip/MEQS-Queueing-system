-- Additive migration for the existing Ministry idealq3 database.
-- Existing feedback rows and the legacy fb1..fb5 scoring columns are kept.
ALTER TABLE feedback
  ADD COLUMN feedback_scope ENUM('global','counter') NOT NULL DEFAULT 'global' AFTER feedback_id,
  ADD COLUMN counter_id INT(11) UNSIGNED NULL DEFAULT NULL AFTER feedback_scope,
  ADD COLUMN counter_name_snapshot VARCHAR(200) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER counter_id,
  ADD COLUMN counter_number_snapshot INT NULL DEFAULT NULL AFTER counter_name_snapshot,
  ADD COLUMN counter_zone_snapshot VARCHAR(200) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER counter_number_snapshot,
  ADD COLUMN feedback_language VARCHAR(5) NULL DEFAULT NULL AFTER counter_zone_snapshot,
  ADD COLUMN feedback_note TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL AFTER feedback_score,
  ADD INDEX idx_feedback_scope_date (feedback_scope, feedback_date),
  ADD INDEX idx_feedback_counter_date (counter_id, feedback_date);
