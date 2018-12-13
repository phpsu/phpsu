#!/usr/bin/env bash

ssh -i $(pwd)/id_rsa \
-o StrictHostKeyChecking=no \
-o UserKnownHostsFile=/dev/null \
-o "ProxyCommand ssh -W %h:%p -i $(pwd)/id_rsa -o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null -p2208 user@localhost" \
user@host_b
