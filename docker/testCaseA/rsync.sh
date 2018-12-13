#!/usr/bin/env bash

rsync -Pavz -e "ssh -i $(pwd)/id_rsa -o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null -p2208" user@localhost:/root/test/* ./test/

#rsync -av -e "ssh -A root@proxy ssh" ./src user@target:/dst

