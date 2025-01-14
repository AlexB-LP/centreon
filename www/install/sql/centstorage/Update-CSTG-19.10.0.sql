--
-- Alter existing tables to conform with strict mode.
--
ALTER TABLE `log_action_modification` MODIFY COLUMN `field_value` text NOT NULL;

--add the audit log retention column for the retention options menu
ALTER TABLE `config` ADD COLUMN IF NOT EXISTS audit_log_retention int(11) DEFAULT 0;
