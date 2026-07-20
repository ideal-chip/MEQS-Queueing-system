-- Bootstrap script for a fresh Oracle MySQL 8.4 instance. Run as root
-- (e.g. `sudo mysql < database/create_demo_database.sql`) ONCE, then edit
-- the password below (or better, ALTER USER a fresh one immediately after)
-- and put the real value in .env -- never commit a real password here.
--
-- mysql_native_password is used because this project's mysqli build lacks
-- the caching_sha2_password plugin file; see docs/BUG_REGISTER_EN.md
-- BUG-00xx (Oracle MySQL migration) for why.

CREATE DATABASE IF NOT EXISTS project_demo_db CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

CREATE USER IF NOT EXISTS 'project_demo_user'@'127.0.0.1' IDENTIFIED WITH mysql_native_password BY 'CHANGE-ME';
CREATE USER IF NOT EXISTS 'project_demo_user'@'localhost' IDENTIFIED WITH mysql_native_password BY 'CHANGE-ME';

-- Scoped to this one schema only -- no SUPER/FILE/PROCESS/CREATE USER/other-schema access.
GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, ALTER, INDEX, REFERENCES, DROP,
      LOCK TABLES, CREATE TEMPORARY TABLES, CREATE VIEW, SHOW VIEW, TRIGGER, EVENT
    ON project_demo_db.* TO 'project_demo_user'@'127.0.0.1';
GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, ALTER, INDEX, REFERENCES, DROP,
      LOCK TABLES, CREATE TEMPORARY TABLES, CREATE VIEW, SHOW VIEW, TRIGGER, EVENT
    ON project_demo_db.* TO 'project_demo_user'@'localhost';

FLUSH PRIVILEGES;
