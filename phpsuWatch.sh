#!/usr/bin/env bash

#inotifywait -qrm --event move,modify --format '%w%f' $(pwd) | grep '\.php$' | while read -r line; do echo "$line"; done


#while inotifywait -qr --event move,modify --format '%w%f' $(pwd)
#do
#    echo "close_write"
#done

while grep -L '\.php$'
do
    echo '_________________________________________________'
    php ./phpsu
done < <(inotifywait -qrm --event move,modify --format '%w%f' $(pwd))
