#!/usr/bin/env bash

ssh -i $(pwd)/id_rsa -o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null  -p2208 user@localhost
