#!/bin/bash -e

APP_DB='pgt'
APP_DB_PASS=$(< /dev/urandom tr -dc _A-Za-z0-9 | head -c32);

WWW_DIR='/var/www/html'
DATA_DIR='/var/www/pgt'

ADMIN_APP_PASS='1234';
ADMIN_PG_PASS=$(< /dev/urandom tr -dc _A-Za-z0-9 | head -c32);
POSTGRES_PASSWORD=$(< /dev/urandom tr -dc _A-Za-z0-9 | head -c32);

ADMIN_APP_PASS='tile';
#ADMIN_APP_PASS_ENCODED=$(php-cli -r "echo password_hash('${ADMIN_APP_PASS}', PASSWORD_DEFAULT);")
ADMIN_APP_PASS_ENCODED='$2y$10$xZdMXzAyOY0cCCvxLOOFM.DGwXcLzPz/iAbC.3v1p6AQdtirxX0uW'

cat >docker/const.php <<CAT_EOF
<?php
const DB_HOST="db";
const DB_NAME="${APP_DB}";
const DB_USER="${APP_DB}";
const DB_PASS="${APP_DB_PASS}";
const DB_PORT = 5432;
const DB_SCMA='public';
const SESS_USR_KEY = 'pgt_user';
const SUPER_ADMIN_ID = 1;
const WWW_DIR = '${WWW_DIR}';
const DATA_DIR = '${DATA_DIR}';
?>
CAT_EOF

cat >docker/.env <<CAT_EOF
PUBLIC_IP=192.168.0.25
PUBLIC_PORT=8000
POSTGRES_USER=postgres
PGUSER=postgres
POSTGRES_PASSWORD=${POSTGRES_PASSWORD}
ADMIN_APP_PASS=${ADMIN_APP_PASS}
ADMIN_APP_PASS_ENCODED=${ADMIN_APP_PASS_ENCODED}
ADMIN_PG_PASS=${ADMIN_PG_PASS}
APP_DB=${APP_DB}
APP_DB_PASS=${APP_DB_PASS}
CAT_EOF

sed "s|\$WWW_DIR|${WWW_DIR}|" < installer/apache.conf > docker/apache.conf
