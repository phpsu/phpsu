#!/usr/bin/env bash

rsync -avz -e "ssh -i $(pwd)/id_rsa -o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null -p2208" user@localhost:~/test/* ./test/
