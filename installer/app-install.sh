#!/bin/bash -e

APP_DB='pgt'
APP_DB_PASS=$(< /dev/urandom tr -dc _A-Za-z0-9 | head -c32);

WWW_DIR='/var/www/html'
DATA_DIR='/var/www/pgt'

ADMIN_APP_PASS='tile';
ADMIN_PG_PASS=$(< /dev/urandom tr -dc _A-Za-z0-9 | head -c32);

# 1. Install packages (assume PG is preinstalled)
apt-get -y install apache2 libapache2-mod-php php-{pgsql,json,zip} gdal-bin

ADMIN_APP_PASS_ENCODED=$(php -r "echo password_hash('${ADMIN_APP_PASS}', PASSWORD_DEFAULT);")
sed -i.save "s|ADMIN_APP_PASS|${ADMIN_APP_PASS_ENCODED}|
s|ADMIN_PG_PASS|${ADMIN_PG_PASS}|
" installer/init.sql

# 2. Create db
su postgres <<CMD_EOF
createdb ${APP_DB}
createuser -sd ${APP_DB}
psql -c "alter user ${APP_DB} with password '${APP_DB_PASS}'"
psql -c "ALTER DATABASE ${APP_DB} OWNER TO ${APP_DB}"

createuser -sd admin1
psql -c "alter user admin1 with password '${ADMIN_PG_PASS}'"

psql -d ${APP_DB} < installer/setup.sql
psql -d ${APP_DB} < installer/init.sql
CMD_EOF

echo "${APP_DB} pass: ${APP_DB_PASS}" >> /root/auth.txt

cat >admin/incl/const.php <<CAT_EOF
<?php
const DB_HOST="localhost";
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

cp -r . /var/www/html/
chown -R www-data:www-data ${WWW_DIR}
rm -rf /var/www/html/{installer,plugins}

mkdir -p ${DATA_DIR}/{upload,pg}
chown -R www-data:www-data ${DATA_DIR}
chmod 770 ${DATA_DIR}
chmod 770 ${DATA_DIR}/upload
chmod 770 ${DATA_DIR}/pg

touch /var/www/pg_service.conf
chown www-data:www-data /var/www/pg_service.conf
chmod 600 /var/www/pg_service.conf

# fixes for file upload size
PHP_VER=$(php -version | head -n 1 | cut -f2 -d' ' | cut -f1,2 -d.)
POST_MAX=$(grep -m 1 '^post_max_size =' /etc/php/${PHP_VER}/apache2/php.ini | cut -f2 -d=)
sed -i.save "s/^upload_max_filesize =.*/upload_max_filesize = ${POST_MAX}/" /etc/php/${PHP_VER}/apache2/php.ini

a2enmod rewrite
sed "s|\$WWW_DIR|${WWW_DIR}|" < installer/apache.conf > /etc/apache2/sites-available/000-default.conf
systemctl restart apache2

for f in pgt_svc_ctl; do
	cp installer/${f}.sh /usr/local/bin/
	chown www-data:www-data /usr/local/bin/${f}.sh
	chmod 0550 /usr/local/bin/${f}.sh
done

cat >/etc/sudoers.d/pgt <<CAT_EOF
www-data ALL = NOPASSWD: /usr/local/bin/pgt_svc_ctl.sh
CAT_EOF

# fix permissions to allow apache to save toml config
for svc in pg_tileserv pg_featureserv; do
	chown pgis:www-data /opt/${svc}/config
	chmod g+w 					/opt/${svc}/config
done

#Load Natual Earth data for testing
export SVC_DB='countries'
export SVC_USR='admin1'

su postgres <<CMD_EOF
createdb ${SVC_DB}
psql -d ${SVC_DB} -c 'CREATE EXTENSION IF NOT EXISTS POSTGIS'
CMD_EOF

for svc in pg_tileserv pg_featureserv; do
	sed "
s|# DbConnection = \"postgresql://username:password@host/dbname\"|DbConnection = \"postgresql://${SVC_USR}:${ADMIN_PG_PASS}@localhost/${SVC_DB}\"|
s|7800|7801|
s|9000|9001|
" < /opt/${svc}/config/${svc}.toml.example > /opt/${svc}/config/${svc}1.toml
	
	chown pgis:www-data /opt/${svc}/config/${svc}1.toml
	
	systemctl enable ${svc}@1 
	systemctl start ${svc}@1
done

tar -xf installer/ne_50m_admin_0_countries.tgz -C${DATA_DIR}/upload

export PGPASSWORD="${ADMIN_PG_PASS}"

pushd ${DATA_DIR}/upload
  shp2pgsql -I -s 4326 -W "latin1" ne_50m_admin_0_countries.shp ${SVC_DB} | psql -U ${SVC_USR} -d ${SVC_DB}
  rm -f ne_50m_admin_0_countries.*
popd

apt-get -y clean all
