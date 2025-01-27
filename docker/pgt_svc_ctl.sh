#!/bin/bash -e

ACTION=${1}
SVC=${2}

function start(){
    if [ ! -f /var/run/apache2/${SVC}.pid ]; then
        pushd /opt/${NAME}/
            nohup /opt/${NAME}/${NAME} --config /opt/${NAME}/config/${SVC}.toml 1>/dev/null 2>&1 &
            echo $! > /var/run/apache2/${SVC}.pid
        popd
    else
        echo "Error: Service ${SVC} is already running"
        exit 2
    fi
}

function stop(){
    if [ -f /var/run/apache2/${SVC}.pid ]; then
        SVC_PID=$(cat /var/run/apache2/${SVC}.pid)
        while [ -d /proc/${SVC_PID} ]; do
    		pkill -F /var/run/apache2/${SVC}.pid
    		sleep 2;
    	done
        rm -f /var/run/apache2/${SVC}.pid
    else
        echo "Error: Service ${SVC} not running"
        exit 2
    fi
}

if [ "${SVC:0:11}" == 'pg_tileserv' ]; then
    NAME='pg_tileserv'
elif [ "${SVC:0:14}" == 'pg_featureserv' ]; then
    NAME='pg_featureserv'
else
	echo "Error: Invalid service name '${SVC}'"
	exit 1;
fi

SVC=$(echo ${SVC} | tr -d '@')

if [ "${ACTION}" == 'start' ]; then
    start;
elif [ "${ACTION}" == 'stop' ]; then
    stop;
elif [ "${ACTION}" == 'restart' ]; then
    stop;
    start;
elif [ "${ACTION}" == 'status' ]; then
    if [ -f /var/run/apache2/${SVC}.pid ]; then
        SVC_PID=$(cat /var/run/apache2/${SVC}.pid)
        
        if [ -d /proc/${SVC_PID} ]; then
    		echo "  Active: active (running)"
    		echo "  Main PID: ${SVC_PID} (${NAME})"
    		SVC_TIME=$(ps -p ${SVC_PID} -o pid,comm,etime | tail -n 1 | sed 's/[ \t]\+/ /g' | cut -f4 -d' ')
    		echo "  CPU: ${SVC_TIME} s"
    	else
    		#rm -f /var/www/data/layers/${LAYER_ID}/seed.pid
    		echo "  Active: inactive (dead)"
    	fi
    else
        echo "  Active: inactive (stopped)"
    fi
else
    echo "Error: Invalid action '${ACTION}'"
	exit 3;
fi
