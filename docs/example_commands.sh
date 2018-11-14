#!/usr/bin/env bash

DBhost=_
DBport=_
username=_
password=_
database=_
localDBHost=_
localUsername=_
localPassword=_
localDatabase=_

## this:
phpsu hostA-\>hostB

## results in this:
ssh hostB 'rsync -avz hostA:/var/www/test/* /var/www/test2/'
ssh hostB 'ssh hostA -C "mysqldump -h${DBhost} -P${port} -u${username} -p${password} ${database}" | mysql -h${DBhost} -u${localUsername} -p${localPassword} ${localDatabase}'


## this:
phpsu hostA-\>local

## results in this:
rsync -avz hostA:/var/www/test/* /var/www/test2/
ssh hostA -C "mysqldump -h${DBhost} -P${DBport} -u${username} -p${password} ${database}" | mysql -h${DBhost} -P${DBport} -u${localUsername} -p${localPassword} ${localDatabase}


## this:
phpsu localA-\>local

## results in this:
rsync -avz /var/www/test/* /var/www/test2/
mysqldump -h${DBhost} -P${DBport} -u${username} -p${password} ${database} | mysql -h${DBhost} -P${DBport} -u${localUsername} -p${localPassword} ${localDatabase}
