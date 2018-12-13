#!/usr/bin/env bash

DBhost=database
DBport=3306
username=root
password=root
database=sequelmovie

DBHost2=127.0.0.1
DBPort2=2206
Username2=root
Password2=root
Database2=sequelmovie2

ssh -F ssh_config user@hostc -C "mysqldump -h${DBhost} -P${DBport} -u${username} -p${password} ${database}" \
| mysql -h${DBHost2} -P${DBPort2} -u${Username2} -p${Password2} ${Database2}

ssh -F ssh_config user@hostc -C "mysqldump -h${DBhost} -P${DBport} -u${username} -p${password} ${database}" \
| ssh -F ssh_config user@hostc -C "mysql -h${DBhost} -P${DBport} -u${username} -p${password} ${Database2}"
