#!/bin/sh

set -e

cd "$(dirname "$0")/.."

# checkout jaeger thrift files
rm -rf jaeger-idl
git clone https://github.com/jaegertracing/jaeger-idl

# define thrift cmd
THRIFT="docker run -u $(id -u) -v '${PWD}:/data' jaegertracing/thrift:0.13 thrift -o /data/jaeger-idl"
THRIFT_CMD="${THRIFT} --gen php"

# generate php files
FILES=$(find jaeger-idl/thrift -type f -name \*.thrift)
for f in ${FILES}; do
    echo "${THRIFT_CMD} "/data/${f}""
    eval $THRIFT_CMD "/data/${f}"
done

# move generated files
rm -rf thrift
mkdir -p thrift
mv jaeger-idl/gen-php/Jaeger/Thrift thrift/jaeger

# remove thrift files
rm -rf jaeger-idl