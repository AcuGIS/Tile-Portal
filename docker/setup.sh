#!/bin/bash -e

createdb ${APP_DB}
createuser -sd ${APP_DB}
psql -c "alter user ${APP_DB} with password '${APP_DB_PASS}'"
psql -c "ALTER DATABASE ${APP_DB} OWNER TO ${APP_DB}"

createuser -sd admin1
psql -c "alter user admin1 with password '${ADMIN_PG_PASS}'"

psql -d ${APP_DB} < /tmp/setup.sql
sed "s|ADMIN_APP_PASS|${ADMIN_APP_PASS_ENCODED}|
s|ADMIN_PG_PASS|${ADMIN_PG_PASS}|
s|localhost|db|" /tmp/init.sql | psql -d ${APP_DB}


#Load Natual Earth data for testing
pg_restore --create -Fc -d postgres /tmp/countries.dump
