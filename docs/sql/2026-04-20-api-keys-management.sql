SET @sql := IF(
    EXISTS(
        SELECT 1
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'api_keys'
          AND COLUMN_NAME = 'allowed_ip'
    ),
    'SELECT 1',
    'ALTER TABLE api_keys ADD COLUMN allowed_ip VARCHAR(45) NULL'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := IF(
    EXISTS(
        SELECT 1
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'api_keys'
          AND COLUMN_NAME = 'status'
    ),
    'SELECT 1',
    'ALTER TABLE api_keys ADD COLUMN status VARCHAR(16) NOT NULL DEFAULT ''active'''
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := IF(
    EXISTS(
        SELECT 1
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'api_keys'
          AND COLUMN_NAME = 'suspended_at'
    ),
    'SELECT 1',
    'ALTER TABLE api_keys ADD COLUMN suspended_at TIMESTAMP NULL DEFAULT NULL'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := IF(
    EXISTS(
        SELECT 1
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'api_keys'
          AND COLUMN_NAME = 'revoked_at'
    ),
    'SELECT 1',
    'ALTER TABLE api_keys ADD COLUMN revoked_at TIMESTAMP NULL DEFAULT NULL'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

UPDATE api_keys
SET status = 'active'
WHERE status IS NULL OR status = '';
