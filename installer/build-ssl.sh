#!/bin/bash -e

HNAME=$(hostname -f)

function webmin_ssl() {
	cat /etc/letsencrypt/live/quail-testing.webgis1.com/cert.pem > /etc/webmin/miniserv.pem
	cat /etc/letsencrypt/live/quail-testing.webgis1.com/privkey.pem >> /etc/webmin/miniserv.pem
	echo "extracas=/etc/letsencrypt/live/quail-testing.webgis1.com/fullchain.pem" >> /etc/webmin/miniserv.conf
	
	systemctl restart webmin
}


function get_certs() {
	cp /etc/letsencrypt/live/quail-testing.webgis1.com/fullchain.pem /opt/pg_tileserv/fullchain.pem
	cp /etc/letsencrypt/live/quail-testing.webgis1.com/privkey.pem /opt/pg_tileserv/privkey.pem
	
	cp /etc/letsencrypt/live/quail-testing.webgis1.com/fullchain.pem /opt/pg_featureserv/fullchain.pem
	cp /etc/letsencrypt/live/quail-testing.webgis1.com/privkey.pem /opt/pg_featureserv/privkey.pem
}

function own_certs() {
	
	chown pgis:pgis /opt/pg_tileserv/fullchain.pem
	chown pgis:pgis /opt/pg_tileserv/privkey.pem
	chown pgis:pgis /opt/pg_featureserv/fullchain.pem
	chown pgis:pgis /opt/pg_featureserv/privkey.pem
	
}

function update_confs() {
	
	sed -i.save "s/HttpPort/#/g" /opt/pg_featureserv/config/pg_featureserv.toml
	sed -i.save "s|\# HttpsPort|HttpsPort|g" /opt/pg_featureserv/config/pg_featureserv.toml
	
	sed -i.save "s/HttpPort/#/g" /opt/pg_tileserv/config/pg_tileserv.toml
	sed -i.save "s|\# HttpsPort|HttpsPort|g" /opt/pg_tileserv/config/pg_tileserv.toml
}

function update_certs() {
	
	sed -i.save '2 i TlsServerCertificateFile = "fullchain.pem"' /opt/pg_featureserv/config/pg_featureserv.toml
	sed -i.save '3 i TlsServerPrivateKeyFile = "privkey.pem"' /opt/pg_featureserv/config/pg_featureserv.toml
	
	cat <<EOT >> /opt/pg_tileserv/config/pg_tileserv.toml
	TlsServerCertificateFile = "fullchain.pem"
	TlsServerPrivateKeyFile = "privkey.pem"
EOT
	
}

function restart_servs() {
	
	systemctl restart pg_tileserv
	systemctl restart pg_featureserv
}

get_certs;
own_certs;
update_confs;
update_certs;
restart_servs;


