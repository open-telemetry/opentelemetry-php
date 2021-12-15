#!/bin/bash

# This script generates PHP code for semantic conventions
#
# Supported semantic conventions:
#  - Trace
#  - Resource

SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
ROOT_DIR="${SCRIPT_DIR}/../../"

# freeze the spec & generator tools versions to make SemanticAttributes generation reproducible
SEMCONV_VERSION=${SEMCONV_VERSION:=1.8.0}
SPEC_VERSION=v$SEMCONV_VERSION
SCHEMA_URL=https://opentelemetry.io/schemas/$SEMCONV_VERSION
GENERATOR_VERSION=0.8.0

cd ${SCRIPT_DIR}

rm -rf opentelemetry-specification || true
mkdir opentelemetry-specification
cd opentelemetry-specification

git init
git remote add origin https://github.com/open-telemetry/opentelemetry-specification.git
git fetch origin "$SPEC_VERSION"
git reset --hard FETCH_HEAD

cd ${SCRIPT_DIR}

rm -rf ${ROOT_DIR}/src/SemConv || true
mkdir -p ${ROOT_DIR}/src/SemConv

# Trace
docker run --rm \
  -v ${SCRIPT_DIR}/opentelemetry-specification/semantic_conventions/trace:/source \
  -v ${SCRIPT_DIR}/templates:/templates \
  -v ${ROOT_DIR}/src/SemConv:/output \
  -u ${UID} \
  otel/semconvgen:$GENERATOR_VERSION \
  -f /source code \
  --template /templates/Attributes.php.j2 \
  --output "/output/TraceAttributes.php" \
  -Dnamespace="OpenTelemetry\\SemConv" \
  -Dclass="Trace" \
  -DschemaUrl=$SCHEMA_URL

docker run --rm \
  -v ${SCRIPT_DIR}/opentelemetry-specification/semantic_conventions/trace:/source \
  -v ${SCRIPT_DIR}/templates:/templates \
  -v ${ROOT_DIR}/src/SemConv:/output \
  -u ${UID} \
  otel/semconvgen:$GENERATOR_VERSION \
  -f /source code \
  --template /templates/AttributeValues.php.j2 \
  --output "/output/TraceAttributeValues.php" \
  -Dnamespace="OpenTelemetry\\SemConv" \
  -Dclass="Trace" \
  -DschemaUrl=$SCHEMA_URL


# Resource
docker run --rm \
  -v ${SCRIPT_DIR}/opentelemetry-specification/semantic_conventions/resource:/source \
  -v ${SCRIPT_DIR}/templates:/templates \
  -v ${ROOT_DIR}/src/SemConv:/output \
  -u ${UID} \
  otel/semconvgen:$GENERATOR_VERSION \
  -f /source code \
  --template /templates/Attributes.php.j2 \
  --output "/output/ResourceAttributes.php" \
  -Dnamespace="OpenTelemetry\\SemConv" \
  -Dclass="Resource" \
  -DschemaUrl=$SCHEMA_URL

docker run --rm \
  -v ${SCRIPT_DIR}/opentelemetry-specification/semantic_conventions/resource:/source \
  -v ${SCRIPT_DIR}/templates:/templates \
  -v ${ROOT_DIR}/src/SemConv:/output \
  -u ${UID} \
  otel/semconvgen:$GENERATOR_VERSION \
  -f /source code \
  --template /templates/AttributeValues.php.j2 \
  --output "/output/ResourceAttributeValues.php" \
  -Dnamespace="OpenTelemetry\\SemConv" \
  -Dclass="Resource" \
  -DschemaUrl=$SCHEMA_URL
