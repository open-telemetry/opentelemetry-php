#!/usr/bin/env bash
DESTINATION_DIR=$(realpath "$(dirname "${BASH_SOURCE[0]}")")/../proto/otel
GPBMETA_DIR="GPBMetadata"
OTEL_DIR="Opentelemetry"
REPO_DIR=opentelemetry-proto

cd "${DESTINATION_DIR}" || exit
rm -rf ./${REPO_DIR}
git clone https://github.com/open-telemetry/${REPO_DIR}

(
  cd ${REPO_DIR}
  git config --add advice.detachedHead false
  TAG=$(
      TAG=$(git describe --tags `git rev-list --tags --max-count=1`)

      git checkout "${TAG}"

      echo "${TAG}"
  )

  echo "Generating protobuf files for version ${TAG} ..."
  make gen-php
  rm -rf ${GPBMETA_DIR} ${OTEL_DIR}
  echo "${TAG}" > "${DESTINATION_DIR}/VERSION"
)

echo "Copying generated source..."
cp -r ${REPO_DIR}/gen/php/${GPBMETA_DIR} .
cp -r ${REPO_DIR}/gen/php/${OTEL_DIR} .

echo "Cleaning up..."
rm -rf ./${REPO_DIR}
echo "Done!"
