#!/usr/bin/env bash
set -e

DESTINATION_DIR=$(realpath "$(dirname "${BASH_SOURCE[0]}")")/../proto/otel
GPBMETA_DIR="GPBMetadata"
OTEL_DIR="Opentelemetry"
REPO_DIR=opentelemetry-proto
# Optional opentelemetry-proto tag, such as v1.11.0. Defaults to the latest tag.
REQUESTED_TAG="${1:-}"

# otel/build-protobuf is published for amd64 only, so arm64 hosts need emulation.
export DOCKER_DEFAULT_PLATFORM="${DOCKER_DEFAULT_PLATFORM:-linux/amd64}"

cd "${DESTINATION_DIR}" || exit
[[ -d ./${REPO_DIR} ]] && rm -rf ./${REPO_DIR}
git clone https://github.com/open-telemetry/${REPO_DIR}

(
  cd ${REPO_DIR}
  git config --add advice.detachedHead false
  TAG=$(
      TAG=${REQUESTED_TAG:-$(git describe --tags "`git rev-list --tags --max-count=1`")}

      git checkout "${TAG}"

      echo "${TAG}"
  )

  echo "Generating protobuf files for version ${TAG} ..."
  make gen-php
  [[ -d ${GPBMETA_DIR} ]] && rm -rf ${GPBMETA_DIR}
  [[ -d ${OTEL_DIR} ]] && rm -rf ${OTEL_DIR}
  echo "${TAG}" > "${DESTINATION_DIR}/VERSION"
)

echo "Copying generated source..."
cp -r ${REPO_DIR}/gen/php/${GPBMETA_DIR} .
cp -r ${REPO_DIR}/gen/php/${OTEL_DIR} .

echo "Cleaning up..."
rm -rf ./${REPO_DIR}
echo "Done!"
