#!/bin/sh
set -e

eval $( fixuid -q )

if [ -f /home/phpsu/.ssh/id_rsa ]; then
  eval `ssh-agent -s`
  ssh-add /home/phpsu/.ssh/id_rsa
fi

exec "$@"
