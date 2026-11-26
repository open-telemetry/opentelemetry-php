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
SEMCONV_VERSION=1.45.0
SPEC_VERSION=v$SEMCONV_VERSION
SCHEMA_URL=https://opentelemetry.io/schemas/$SEMCONV_VERSION
OTEL_WEAVER_IMG_VERSION=v0.28.0

rm -rf "${SPEC_DIR}"
mkdir "${SPEC_DIR}"
cd "${SPEC_DIR}"

git init -b main
git remote add origin https://github.com/open-telemetry/semantic-conventions.git
git fetch origin "$SPEC_VERSION"
git reset --hard FETCH_HEAD

cd "${SCRIPT_DIR}"

mkdir -p "${CODE_DIR}"
BEFORE_DIR="$(mktemp -d)"
trap 'rm -rf "${BEFORE_DIR}"' EXIT
cp -R "${CODE_DIR}/." "${BEFORE_DIR}"
find "${CODE_DIR}" -name "*.php" \
  ! -name "Version.php" \
  ! -name "ResourceAttributes.php" \
  ! -name "ResourceAttributeValues.php" \
  ! -name "TraceAttributes.php" \
  ! -name "TraceAttributeValues.php" \
  -exec rm -f {} \;

echo "${SCHEMA_URL}" > ${SCRIPT_DIR}/templates/registry/php/version.txt

generate () {
  docker run --rm \
    -v "${SPEC_DIR}/model:/home/weaver/model" \
    -v "${SCRIPT_DIR}/templates:/home/weaver/templates" \
    -v "${CODE_DIR}:/home/weaver/output" \
    -u "1000" \
    otel/weaver:$OTEL_WEAVER_IMG_VERSION \
    registry generate --registry /home/weaver/model php
}

# Upstream never removes a convention, it deprecates it, and the templates keep deprecated ones.
# A constant that disappears is a template or generator bug that would break users.
check_removed_constants () {
  local removed=0 file new missing
  while IFS= read -r file; do
    new="${CODE_DIR}/${file}"
    missing="$(comm -23 \
      <(grep -o 'public const [A-Z0-9_]* ' "${BEFORE_DIR}/${file}" | sort -u) \
      <( [[ -f "${new}" ]] && grep -o 'public const [A-Z0-9_]* ' "${new}" | sort -u))"
    if [[ -n "${missing}" ]]; then
      echo "Removed from ${file}:" >&2
      echo "${missing}" | sed 's/^public const /  /' >&2
      removed=1
    fi
  done < <(cd "${BEFORE_DIR}" && find . -name "*.php" | sort)
  return ${removed}
}

generate

echo "${SPEC_VERSION}" > "${CODE_DIR}/VERSION"
"${SCRIPT_DIR}/add-version.sh" "${SEMCONV_VERSION}" "${CODE_DIR}/Version.php"

set +x
if ! check_removed_constants; then
  echo "Generation removed public constants. Fix the templates, or add them back with a _deprecations.php.partial." >&2
  exit 1
fi
