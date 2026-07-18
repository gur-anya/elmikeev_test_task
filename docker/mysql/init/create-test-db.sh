#!/bin/bash
# Runs automatically on first mysql init (empty data dir). Creates the isolated
# database used by integration tests and grants it to the app user (whatever
# DB_USERNAME resolves to), so GRANT never targets a non-existent user.
set -e
mysql -uroot -p"$MYSQL_ROOT_PASSWORD" <<EOSQL
CREATE DATABASE IF NOT EXISTS elmikeev_test_task_db_test;
GRANT ALL PRIVILEGES ON elmikeev_test_task_db_test.* TO '$MYSQL_USER'@'%';
FLUSH PRIVILEGES;
EOSQL
