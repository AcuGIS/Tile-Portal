#!/bin/bash -e

# update hostname and port for services
for svc in pg_tileserv pg_featureserv; do
    sed -i.save "s/^[# ]*UrlBase = .*/UrlBase = \"http:\/\/${PUBLIC_IP}:${PUBLIC_PORT}\/\"/" /opt/${svc}/config/${svc}.toml.example
done

# update demo toml
export SVC_DB='countries'
export SVC_USR='admin1'

for svc in pg_tileserv pg_featureserv; do
	
    sed "
s|# DbConnection = \"postgresql://username:password@host/dbname\"|DbConnection = \"postgresql://${SVC_USR}:${ADMIN_PG_PASS}@db/${SVC_DB}\"|
s|7800|7801|
s|9000|9001|
" < /opt/${svc}/config/${svc}.toml.example > /opt/${svc}/config/${svc}1.toml
	
	chown www-data:www-data /opt/${svc}/config/${svc}1.toml
done

cat >/var/www/pgt/pg_service.conf <<CAT_EOF
[pgtapp]
host=db
port=5432
dbname=${APP_DB}
user=${APP_DB}
password=${APP_DB_PASS}
CAT_EOF

/usr/sbin/apache2 -DFOREGROUND
