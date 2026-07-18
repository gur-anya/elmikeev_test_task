-- Runs automatically on first mysql init (empty data dir).
-- Creates the isolated database used by integration tests (RefreshDatabase migrates into it).
CREATE DATABASE IF NOT EXISTS elmikeev_test_task_db_test;
GRANT ALL PRIVILEGES ON elmikeev_test_task_db_test.* TO 'user'@'%';
FLUSH PRIVILEGES;
