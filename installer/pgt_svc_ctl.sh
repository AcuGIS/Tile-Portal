#!/bin/bash -e

ACTION=${1}
SVC=${2}

if [ "${SVC:0:11}" == 'pg_tileserv' ] || [ "${SVC:0:14}" == 'pg_featureserv' ]; then
	systemctl ${ACTION} ${SVC}
else
	echo "Error: Invalid service name ${SVC}"
	exit 1;
fi

