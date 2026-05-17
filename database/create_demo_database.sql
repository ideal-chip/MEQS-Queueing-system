CREATE DATABASE IF NOT EXISTS project_demo_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS 'project_demo_user'@'localhost' IDENTIFIED BY 'ProjectDemo@12345';
GRANT ALL PRIVILEGES ON project_demo_db.* TO 'project_demo_user'@'localhost';
FLUSH PRIVILEGES;
