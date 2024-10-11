#!/bin/bash

# This script generates PHP code for semantic conventions
#
# Supported semantic conventions:
#  - Trace
#  - Resource
#
# Source repositories:
#  - https://github.com/open-telemetry/semantic-conventions/releases
#  - https://github.com/open-telemetry/build-tools/releases
set -ex

SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
ROOT_DIR="${SCRIPT_DIR}/../.."
SPEC_DIR="${ROOT_DIR}/var/semantic-conventions"
CODE_DIR="${ROOT_DIR}/src/SemConv"

# freeze the spec & generator tools versions to make SemanticAttributes generation reproducible
SEMCONV_VERSION=1.27.0
SPEC_VERSION=v$SEMCONV_VERSION
SCHEMA_URL=https://opentelemetry.io/schemas/$SEMCONV_VERSION
OTEL_WEAVER_IMG_VERSION=v0.10.0

rm -rf "${SPEC_DIR}"
mkdir "${SPEC_DIR}"
cd "${SPEC_DIR}"

git init -b main
git remote add origin https://github.com/open-telemetry/semantic-conventions.git
git fetch origin "$SPEC_VERSION"
git reset --hard FETCH_HEAD

cd "${SCRIPT_DIR}"

mkdir -p "${CODE_DIR}"
find "${CODE_DIR}" -name "*.php" ! -name "Version.php" -exec rm -f {} \;

echo "${SEMCONV_VERSION}" > ${SCRIPT_DIR}/templates/registry/php/version.txt

generate () {
  docker run --rm \
    -v "${SPEC_DIR}/model:/home/weaver/model" \
    -v "${SCRIPT_DIR}/templates:/home/weaver/templates" \
    -v "${CODE_DIR}:/home/weaver/output" \
    -u "1000" \
    otel/weaver:$OTEL_WEAVER_IMG_VERSION \
    registry generate php
}

#TODO split stable from experimental
#TODO one file per group? (see java's implementation)
generate
