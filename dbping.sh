#!/bin/bash
# Program name :dbping.sh
date
while true;

do 
	ping -c1 "10.0.0.150" > /dev/null
	if [ $? -eq 0 ]; then
	echo "Host $output is running"

	else
	php /home/mihir/git/rabbitMQ/testRabbitMQServer.php
	echo "Server is running"
fi
done
