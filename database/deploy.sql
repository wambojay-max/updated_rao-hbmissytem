-- Run as a database administrator. Replace the password before deployment.
CREATE USER IF NOT EXISTS 'rao_app'@'localhost' IDENTIFIED BY 'replace-with-a-long-random-password';
GRANT SELECT, INSERT, UPDATE, DELETE ON rao_hbmis.* TO 'rao_app'@'localhost';
FLUSH PRIVILEGES;

-- Set DB_USER=rao_app and DB_PASSWORD to the same secret in the deployment environment.
