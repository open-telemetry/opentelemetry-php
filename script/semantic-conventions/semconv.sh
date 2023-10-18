#!/bin/bash

# This script generates PHP code for semantic conventions
#
# Supported semantic conventions:
#  - Trace
#  - Resource

SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
ROOT_DIR="${SCRIPT_DIR}/../../"
SPEC_DIR="${ROOT_DIR}/var/semantic-conventions"
CODE_DIR="${ROOT_DIR}/src/SemConv"

# freeze the spec & generator tools versions to make SemanticAttributes generation reproducible
SEMCONV_VERSION=${SEMCONV_VERSION:=1.22.0}
SPEC_VERSION=v$SEMCONV_VERSION
SCHEMA_URL=https://opentelemetry.io/schemas/$SEMCONV_VERSION
GENERATOR_VERSION=0.22.0

cd "${SCRIPT_DIR}" || exit

rm -rf "${SPEC_DIR}" || true
mkdir "${SPEC_DIR}"
cd "${SPEC_DIR}" || exit

git init -b main
git remote add origin https://github.com/open-telemetry/semantic-conventions.git
git fetch origin "$SPEC_VERSION"
git reset --hard FETCH_HEAD

cd "${SCRIPT_DIR}" || exit

mkdir -p "${CODE_DIR}"
find "${CODE_DIR}" -name "*.php" -exec rm -f {} \;

# Trace
docker run --rm \
  -v "${SPEC_DIR}/model:/source" \
  -v "${SCRIPT_DIR}/templates:/templates" \
  -v "${CODE_DIR}:/output" \
  -u "${UID}" \
  otel/semconvgen:$GENERATOR_VERSION \
  --only span,event,attribute_group,scope \
  --yaml-root /source \
  code \
  --template /templates/Attributes.php.j2 \
  --output "/output/TraceAttributes.php" \
  -Dnamespace="OpenTelemetry\\SemConv" \
  -Dclass="Trace" \
  -DschemaUrl=$SCHEMA_URL

# Resource
docker run --rm \
  -v "${SPEC_DIR}/model:/source" \
  -v "${SCRIPT_DIR}/templates:/templates" \
  -v "${CODE_DIR}:/output" \
  -u "${UID}" \
  otel/semconvgen:$GENERATOR_VERSION \
  --only resource \
  --yaml-root /source \
  code \
  --template /templates/Attributes.php.j2 \
  --output "/output/ResourceAttributes.php" \
  -Dnamespace="OpenTelemetry\\SemConv" \
  -Dclass="Resource" \
  -DschemaUrl=$SCHEMA_URL

rm -rf "${SPEC_DIR}" || true
