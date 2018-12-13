#!/usr/bin/env bash

##not working:

rsync -avz -e "ssh -F ssh_config" hostc:~/test/* ./test/
