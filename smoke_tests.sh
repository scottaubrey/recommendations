#!/usr/bin/env bash
set -ex

[ $(curl --write-out %{http_code} --silent --output /dev/null localhost/ping) == 200 ]
#[ $(curl --write-out %{http_code} --silent --output /dev/null localhost/recommendations/../..) == 200 ]
