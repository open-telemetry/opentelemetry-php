#!/bin/bash

# Syncs tests/Integration/Config/configurations/upstream/ with a tagged
# opentelemetry-configuration release, and reports what changed. See README.md.
#
# Source repository:
#  - https://github.com/open-telemetry/opentelemetry-configuration/releases
set -e

SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
ROOT_DIR="${SCRIPT_DIR}/../.."
SPEC_DIR="${ROOT_DIR}/var/opentelemetry-configuration"
UPSTREAM_DIR="${ROOT_DIR}/tests/Integration/Config/configurations/upstream"

# the upstream release to sync from; bump this to upgrade
CONFIG_VERSION=v1.0.0

rm -rf "${SPEC_DIR}"
mkdir -p "${SPEC_DIR}"
cd "${SPEC_DIR}"

git init -q -b main
git remote add origin https://github.com/open-telemetry/opentelemetry-configuration.git
git fetch -q --depth 1 origin "${CONFIG_VERSION}"
git reset -q --hard FETCH_HEAD

cd "${ROOT_DIR}"

upstream_ls () {
    find "${SPEC_DIR}/$1" -maxdepth 1 -name '*.yaml' -exec basename {} \; | sort
}

report () {
    local dir="$1" ours="$2"

    echo "=== ${dir}: added upstream in ${CONFIG_VERSION} (new components may need providers) ==="
    diff <(upstream_ls "${dir}") <(ls "${ours}") | grep '^<' || echo "(none)"

    echo
    echo "=== ${dir}: here but not upstream (removed upstream? delete them) ==="
    diff <(upstream_ls "${dir}") <(ls "${ours}") | grep '^>' || echo "(none)"

    echo
    echo "=== ${dir}: content changes to files we already have ==="
    local f
    while read -r f; do
        [ -f "${ours}/${f}" ] && diff -u "${SPEC_DIR}/${dir}/${f}" "${ours}/${f}" || true
    done < <(upstream_ls "${dir}")
    echo
}

copy () {
    local dir="$1" ours="$2" f
    rm -f "${ours}"/*.yaml
    while read -r f; do
        cp "${SPEC_DIR}/${dir}/${f}" "${ours}/${f}"
    done < <(upstream_ls "${dir}")
    echo "copied $(ls "${ours}"/*.yaml | wc -l | tr -d ' ') file(s) to ${ours#${ROOT_DIR}/}"
}

report snippets "${UPSTREAM_DIR}/snippets"
report examples "${UPSTREAM_DIR}/examples"

echo "=== copying ==="
copy snippets "${UPSTREAM_DIR}/snippets"
copy examples "${UPSTREAM_DIR}/examples"

echo
echo "upstream file_format, which src/Config/SDK/ComponentProvider/OpenTelemetrySdk.php must accept:"
grep -rh '^file_format:' "${UPSTREAM_DIR}" | sort -u

echo
echo "Done. Now run: make test-integration"
echo "Every upstream file must parse, unless listed in ConfigurationTest::knownParseFailures();"
echo "add a no-op provider for any component the SDK lacks."
