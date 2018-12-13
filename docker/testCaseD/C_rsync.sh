#!/usr/bin/env bash

rsync -avz -e "ssh -F ssh_config" hostc:~/test/* ./test/
