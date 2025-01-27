FROM ubuntu:24.04

ENV DEBIAN_FRONTEND=noninteractive

ENV LANG=C
ENV APACHE_RUN_DIR=/var/run/apache2
ENV APACHE_RUN_USER=www-data
ENV APACHE_RUN_GROUP=www-data
ENV APACHE_PID_FILE=/var/run/apache2/apache2.pid
ENV APACHE_RUN_DIR=/var/run/apache2
ENV APACHE_LOCK_DIR=/var/lock/apache2
ENV APACHE_LOG_DIR=/var/log/apache2

ENV DATA_DIR=/var/www/pgt
ENV TILESERV_HOME=/opt/pg_tileserv
ENV FEATSERV_HOME=/opt/pg_featureserv

#RUN echo 'Acquire::http { Proxy "http://kpp:3128"; }' > /etc/apt/apt.conf.d/proxy

RUN apt-get -y update && \
	apt-get -y --no-install-suggests --no-install-recommends install apache2 libapache2-mod-php php-pgsql php-json php-zip gdal-bin unzip postgis python3 osm2pgrouting postgresql-pgrouting && \
	apt-get -y clean all && \
	rm -rf /var/lib/apt/lists/*

COPY --chown=www-data:www-data docker/envvars /etc/apache2/envvars

RUN mkdir -p /var/lock/apache2 /var/run/apache2 /var/log/apache2 /var/www/html && \
  chown -R www-data:www-data /var/lock/apache2 /var/run/apache2 /var/log/apache2 /var/www/html
			
#RUN sed -i.save "s/^upload_max_filesize =.*/upload_max_filesize = ${POST_MAX}/" /etc/php/${PHP_VER}/fpm/php.ini

RUN mkdir -p ${DATA_DIR}/upload && \
    mkdir -p ${DATA_DIR}/pg && \
    chown -R www-data:www-data ${DATA_DIR}  && \
    chmod 770 ${DATA_DIR}  && \
    chmod 770 ${DATA_DIR}/upload  && \
    chmod 770 ${DATA_DIR}/pg

# copy web files
COPY --chown=www-data:www-data index.php login.php logout.php layer.php /var/www/html/
COPY --chown=www-data:www-data admin /var/www/html/admin
COPY --chown=www-data:www-data assets /var/www/html/assets

# update db/qgis-server hostname
RUN sed -i.save 's/localhost/db/' /var/www/html/admin/dist/js/database.js && \
		sed -i.save 's/localhost/db/' /var/www/html/admin/action/pglink.php  && \
		sed -i.save 's/localhost/db/' /var/www/html/admin/action/import.php

RUN a2enmod rewrite
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf		

# crunchy services
ADD https://postgisftw.s3.amazonaws.com/pg_tileserv_latest_linux.zip /tmp/pg_tileserv_latest_linux.zip
RUN mkdir -p "${TILESERV_HOME}" && \
    unzip -d"${TILESERV_HOME}" /tmp/pg_tileserv_latest_linux.zip && \
    rm -f /tmp/pg_tileserv_latest_linux.zip

RUN sed -i.save "s|^AssetsPath =.*|AssetsPath = \"${TILESERV_HOME}/assets\"|g" ${TILESERV_HOME}/config/pg_tileserv.toml.example && \
    sed -i.save "s/^[# ]*HttpPort = .*/HttpPort = 7800/" ${TILESERV_HOME}/config/pg_tileserv.toml.example && \
    sed -i.save "s/^[# ]*CacheTTL = .*/CacheTTL = 600/" ${TILESERV_HOME}/config/pg_tileserv.toml.example && \
    sed -i.save "s/^[# ]*HttpHost = .*/HttpHost = \"localhost\"/" ${TILESERV_HOME}/config/pg_tileserv.toml.example && \
    sed -i.save "s/^[# ]*CORSOrigins =/CORSOrigins =/" ${TILESERV_HOME}/config/pg_tileserv.toml.example && \
    sed -i.save "s/^HttpsPort =/#HttpsPort =/" ${TILESERV_HOME}/config/pg_tileserv.toml.example

ADD https://postgisftw.s3.amazonaws.com/pg_featureserv_latest_linux.zip /tmp/pg_featureserv_latest_linux.zip
RUN mkdir -p "${FEATSERV_HOME}" && \
    unzip -d "${FEATSERV_HOME}" /tmp/pg_featureserv_latest_linux.zip && \
    rm -f /tmp/pg_featureserv_latest_linux.zip
    
RUN sed -i.save "s|^AssetsPath =.*|AssetsPath = \"${FEATSERV_HOME}/assets\"|g" ${FEATSERV_HOME}/config/pg_featureserv.toml.example && \
    sed -i.save "s/^HttpHost = .*/HttpHost = \"localhost\"/" ${FEATSERV_HOME}/config/pg_featureserv.toml.example && \
    sed -i.save "s/^[# ]*CORSOrigins =/CORSOrigins =/" ${FEATSERV_HOME}/config/pg_featureserv.toml.example && \
    sed -i.save "s/^HttpPort = .*/HttpPort = 9000/" ${FEATSERV_HOME}/config/pg_featureserv.toml.example && \
    sed -i.save "s/^HttpsPort =/#HttpsPort =/" ${FEATSERV_HOME}/config/pg_featureserv.toml.example

RUN chown -R www-data:www-data ${TILESERV_HOME} && \
    chown -R www-data:www-data ${FEATSERV_HOME}

COPY docker/pgt_svc_ctl.sh /usr/local/bin/pgt_svc_ctl.sh
RUN chmod +x /usr/local/bin/pgt_svc_ctl.sh && \
	sed -i.save 's/sudo //g' /var/www/html/admin/class/backend.php && \
	sed -i.save 's/sudo //g' /var/www/html/admin/action/service.php

COPY docker/fix_and_run.sh /usr/local/bin/fix_and_run.sh
RUN chmod +x /usr/local/bin/fix_and_run.sh
	
RUN ln -sf /proc/self/fd/1 /var/log/apache2/access.log && \
    ln -sf /proc/self/fd/1 /var/log/apache2/error.log

VOLUME ${DATA_DIR}
VOLUME ${TILESERV_HOME}
VOLUME ${FEATSERV_HOME}

ENTRYPOINT ["/usr/local/bin/fix_and_run.sh"]
