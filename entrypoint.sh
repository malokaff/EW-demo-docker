#!/bin/sh

PASSWDFILE=/mosquitto/config/pwfile

if [ -f $PASSWDFILE ]; then
    echo "converting password file"
	echo "mqtt:mqtt" >> $PASSWDFILE
    mosquitto_passwd -U $PASSWDFILE
fi

exec "$@"