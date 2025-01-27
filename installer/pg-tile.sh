#!/bin/bash -e

#Set application user and database name
APPUSER='pgis'
APPDB='postgisftw'
APPUSER_PG_PASS=$(< /dev/urandom tr -dc _A-Za-z0-9 | head -c32);

#Get hostname
HNAME=$(hostname | sed -n 1p | cut -f1 -d' ' | tr -d '\n')

TILESERV_HOME='/opt/pg_tileserv'
FEATSERV_HOME='/opt/pg_featureserv'

function install_pg_tileserv(){
  mkdir -p ${TILESERV_HOME}

  pushd ${TILESERV_HOME}
    wget --quiet -P/tmp https://postgisftw.s3.amazonaws.com/pg_tileserv_latest_linux.zip
    unzip /tmp/pg_tileserv_latest_linux.zip
    rm -f /tmp/pg_tileserv_latest_linux.zip

    pushd config
     	sed -i.save "
s|^AssetsPath =.*|AssetsPath = \"${TILESERV_HOME}/assets\"|g
s/^[# ]*HttpPort = .*/HttpPort = 7800/
s/^[# ]*CacheTTL = .*/CacheTTL = 600/
s/^[# ]*HttpHost = .*/HttpHost = \"localhost\"/
s/^[# ]*UrlBase = .*/UrlBase = \"https:\/\/${HNAME}\/\"/
s/^[# ]*CORSOrigins =/CORSOrigins =/
s/^HttpsPort =/#HttpsPort =/
" pg_tileserv.toml.example
    popd
  popd

  chown -R ${APPUSER}:${APPUSER} ${TILESERV_HOME}

	#The service file
  cat >/etc/systemd/system/pg_tileserv@.service <<CMD_EOF
[Unit]
Description=PG TileServ
After=multi-user.target

[Service]
User=${APPUSER}
WorkingDirectory=${TILESERV_HOME}
Type=simple
Restart=always
ExecStart=${TILESERV_HOME}/pg_tileserv --config ${TILESERV_HOME}/config/pg_tileserv%i.toml

[Install]
WantedBy=multi-user.target
CMD_EOF

  systemctl daemon-reload
}

#Install pg_featureserv and config to run as a service
function install_pg_featureserv(){
  mkdir -p ${FEATSERV_HOME}

  pushd ${FEATSERV_HOME}
    wget --quiet -P/tmp https://postgisftw.s3.amazonaws.com/pg_featureserv_latest_linux.zip
    unzip /tmp/pg_featureserv_latest_linux.zip
    rm -f /tmp/pg_featureserv_latest_linux.zip

    pushd config
      sed -i.save "
s|^AssetsPath =.*|AssetsPath = \"${FEATSERV_HOME}/assets\"|g
s/^HttpHost = .*/HttpHost = \"localhost\"/
s/^[# ]*UrlBase = .*/UrlBase = \"https:\/\/${HNAME}\/\"/
s/^[# ]*CORSOrigins =/CORSOrigins =/
s/^HttpPort = .*/HttpPort = 9000/
s/^HttpsPort =/#HttpsPort =/
" pg_featureserv.toml.example
    popd
  popd

  chown -R ${APPUSER}:${APPUSER} ${FEATSERV_HOME}

  cat >/etc/systemd/system/pg_featureserv@.service <<CMD_EOF
[Unit]
Description=PG FeatureServ
After=multi-user.target

[Service]
User=${APPUSER}
WorkingDirectory=${FEATSERV_HOME}
Type=simple
Restart=always
ExecStart=${FEATSERV_HOME}/pg_featureserv --config ${FEATSERV_HOME}/config/pg_featureserv%i.toml

[Install]
WantedBy=multi-user.target
CMD_EOF

  systemctl daemon-reload
}

function info_for_user() {

	#End message for user
	echo -e "Installation is now completed."
	echo -e "Access pg-tileserv at ${HNAME}:7800"
	echo -e "Access pg-featureserv at ${HNAME}:9000"
	echo -e "Access pg-routing at ${HNAME}/openlayers-pgrouting.html"
	echo -e "postgres and crunchy pg passwords are saved in /root/auth.txt file"
	
	if [ ${BUILD_SSL} == 'yes' ]; then
		if [ ! -f /etc/letsencrypt/live/${HNAME}/privkey.pem ]; then
			echo 'SSL Provisioning failed.  Please see geohelm.docs.acugis.com for troubleshooting tips.'
		else
			echo 'SSL Provisioning Success.'
		fi
	fi
}

function crunchy_setup_pg() {

  apt-get install -y postgis python3 osm2pgrouting postgresql-pgrouting

  sudo -u postgres createuser ${APPUSER} --superuser

  sudo -u postgres psql <<CMD_EOF
alter user ${APPUSER} with password '${APPUSER_PG_PASS}';
CREATE DATABASE ${APPDB} WITH OWNER = ${APPUSER} ENCODING = 'UTF8';
\connect ${APPDB};
CREATE SCHEMA ${APPDB};
CREATE EXTENSION postgis;
CREATE EXTENSION pgrouting;
CMD_EOF

  echo "pgis PG pass: ${APPUSER_PG_PASS}" >> /root/auth.txt
}

function setup_user(){
  useradd -m ${APPUSER}
	# allow user to access www-files
	usermod -a -G www-data ${APPUSER}
	
  echo "${APPDB}:${APPUSER}:${APPUSER_PG_PASS}" >/home/${APPUSER}/.pgpass
  chown ${APPUSER}:${APPUSER} /home/${APPUSER}/.pgpass
  chmod 0600 /home/${APPUSER}/.pgpass
}

export DEBIAN_FRONTEND=noninteractive

add-apt-repository -y universe
apt-get -y update || true

apt-get -y install wget tar bzip2

setup_user;
crunchy_setup_pg;
install_pg_tileserv;
install_pg_featureserv;


# save some of space
apt-get -y clean all
