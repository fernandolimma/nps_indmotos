USE nps_indmotos;

SET @migration_sql = IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE()
       AND TABLE_NAME = 'configuracoes'
       AND COLUMN_NAME = 'evolution_enabled') = 0,
    'ALTER TABLE configuracoes ADD COLUMN evolution_enabled BOOLEAN NOT NULL DEFAULT FALSE AFTER dias_validade',
    'SELECT ''configuracoes.evolution_enabled already exists'' AS migration_info'
);
PREPARE migration_statement FROM @migration_sql;
EXECUTE migration_statement;
DEALLOCATE PREPARE migration_statement;

SET @migration_sql = IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE()
       AND TABLE_NAME = 'configuracoes'
       AND COLUMN_NAME = 'evolution_url') = 0,
    'ALTER TABLE configuracoes ADD COLUMN evolution_url VARCHAR(500) NULL AFTER evolution_enabled',
    'SELECT ''configuracoes.evolution_url already exists'' AS migration_info'
);
PREPARE migration_statement FROM @migration_sql;
EXECUTE migration_statement;
DEALLOCATE PREPARE migration_statement;

SET @migration_sql = IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE()
       AND TABLE_NAME = 'configuracoes'
       AND COLUMN_NAME = 'evolution_instance') = 0,
    'ALTER TABLE configuracoes ADD COLUMN evolution_instance VARCHAR(255) NULL AFTER evolution_url',
    'SELECT ''configuracoes.evolution_instance already exists'' AS migration_info'
);
PREPARE migration_statement FROM @migration_sql;
EXECUTE migration_statement;
DEALLOCATE PREPARE migration_statement;

SET @migration_sql = IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE()
       AND TABLE_NAME = 'configuracoes'
       AND COLUMN_NAME = 'evolution_api_key') = 0,
    'ALTER TABLE configuracoes ADD COLUMN evolution_api_key TEXT NULL AFTER evolution_instance',
    'SELECT ''configuracoes.evolution_api_key already exists'' AS migration_info'
);
PREPARE migration_statement FROM @migration_sql;
EXECUTE migration_statement;
DEALLOCATE PREPARE migration_statement;

SET @migration_sql = IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE()
       AND TABLE_NAME = 'clientes_os'
       AND COLUMN_NAME = 'telefone_whatsapp') = 0,
    'ALTER TABLE clientes_os ADD COLUMN telefone_whatsapp VARCHAR(30) NULL AFTER email_cliente',
    'SELECT ''clientes_os.telefone_whatsapp already exists'' AS migration_info'
);
PREPARE migration_statement FROM @migration_sql;
EXECUTE migration_statement;
DEALLOCATE PREPARE migration_statement;

SET @migration_sql = IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE()
       AND TABLE_NAME = 'clientes_os'
       AND COLUMN_NAME = 'status_whatsapp') = 0,
    'ALTER TABLE clientes_os ADD COLUMN status_whatsapp ENUM(''nao_enviado'', ''enviado'', ''retornado_erro'', ''nao_disponivel'') NOT NULL DEFAULT ''nao_disponivel'' AFTER status_email',
    'SELECT ''clientes_os.status_whatsapp already exists'' AS migration_info'
);
PREPARE migration_statement FROM @migration_sql;
EXECUTE migration_statement;
DEALLOCATE PREPARE migration_statement;

SELECT
    TABLE_NAME,
    COLUMN_NAME,
    COLUMN_TYPE,
    IS_NULLABLE,
    COLUMN_DEFAULT
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND (
      (TABLE_NAME = 'configuracoes' AND COLUMN_NAME IN (
          'evolution_enabled',
          'evolution_url',
          'evolution_instance',
          'evolution_api_key'
      ))
      OR
      (TABLE_NAME = 'clientes_os' AND COLUMN_NAME IN (
          'telefone_whatsapp',
          'status_whatsapp'
      ))
  )
ORDER BY TABLE_NAME, ORDINAL_POSITION;
