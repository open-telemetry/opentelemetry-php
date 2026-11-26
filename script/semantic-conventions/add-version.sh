#!/bin/bash
# Adds a semantic conventions version to the Version enum, keeping the cases
# newest first. Does nothing if a case already has that value.
#
# Usage: add-version.sh <major.minor.patch> <path to Version.php>
set -euo pipefail

VERSION="$1"
FILE="$2"

if [[ ! "${VERSION}" =~ ^[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
  echo "Not a MAJOR.MINOR.PATCH version: ${VERSION}" >&2
  exit 1
fi

if grep -q "^    case VERSION_[0-9_]* = '${VERSION}';$" "${FILE}"; then
  echo "Version ${VERSION} is already in ${FILE}"
  exit 0
fi

new_case="    case VERSION_${VERSION//./_} = '${VERSION}';"
case_pattern="^    case VERSION_[0-9_]+ = '([0-9.]+)';$"

newest() {
  printf '%s\n%s\n' "$1" "$2" | sort -V | tail -n 1
}

tmp="$(mktemp)"
inserted=0
last_case=0
while IFS= read -r line || [[ -n "${line}" ]]; do
  if [[ ${inserted} -eq 0 && "${line}" =~ ${case_pattern} ]]; then
    last_case=1
    if [[ "$(newest "${VERSION}" "${BASH_REMATCH[1]}")" == "${VERSION}" ]]; then
      echo "${new_case}" >> "${tmp}"
      inserted=1
    fi
  elif [[ ${inserted} -eq 0 && ${last_case} -eq 1 ]]; then
    echo "${new_case}" >> "${tmp}"
    inserted=1
  fi
  echo "${line}" >> "${tmp}"
done < "${FILE}"

if [[ ${inserted} -eq 0 ]]; then
  rm -f "${tmp}"
  echo "No Version cases found in ${FILE}" >&2
  exit 1
fi

cat "${tmp}" > "${FILE}"
rm -f "${tmp}"
echo "Added ${VERSION} to ${FILE}"
