#!/usr/bin/env bash
# Checks the PHP versions this repository supports against the support policy
# in docs/versioning.md ("PHP version support"): a PHP version is supported until
# 12 months after its security support ends. Opens an issue when
#   - a supported version is within 60 days of that date, or past it,
#   - a released PHP version is not tested in CI yet.
# The checklists in those issues say when to run --write.
# Data comes from https://php.watch/api/v1/versions. Needs GNU date, curl, jq and network access.
#
# Usage:
#   script/php-version-support.sh          open the issues above
#   script/php-version-support.sh --write  regenerate the blocks between the
#                                          <!-- php-support-*:start/end --> markers
#                                          in README.md and docs/versioning.md
#   script/php-version-support.sh --check  fail if those blocks are out of date
# The blocks list the versions in the blocking CI matrix of php.yml, from the
# minimum in composer.json.
#
# Environment:
#   DRY_RUN=1          print what would be opened, don't call `gh`
#   TODAY=YYYY-MM-DD   override the current date, for testing
#   GH_REPO            repository for `gh`, defaults to the current one
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "${ROOT_DIR}"

API_URL="https://php.watch/api/v1/versions"
LABEL="php-version-support"
WARN_DAYS=60
SUPPORT_MONTHS=12

mode="${1:-}"
case "${mode}" in
  "" | --write | --check) ;;
  *) echo "Usage: $0 [--write|--check]" >&2; exit 2 ;;
esac

today="${TODAY:-$(date -u +%F)}"
dry_run="${DRY_RUN:-0}"

json="$(curl --fail --silent --show-error --location --retry 3 "${API_URL}")"
if ! jq -e '.data | type == "object" and length > 0' <<<"${json}" >/dev/null; then
  echo "Unexpected response from ${API_URL}" >&2
  exit 1
fi

# Minimum PHP version, from the "php" constraint in composer.json, e.g. ^8.2 -> 8.2
min_version="$(jq -r '.require.php' composer.json | grep -oE '[0-9]+\.[0-9]+' | head -1 || true)"
# Versions tested (and blocking) in the CI matrix; the experimental `include` entry is not listed here
ci_versions="$(grep -m1 -oE "php-version: \[[^]]*\]" .github/workflows/php.yml | grep -oE '[0-9]+\.[0-9]+' || true)"
if [[ -z "${min_version}" || -z "${ci_versions}" ]]; then
  echo "Could not read the minimum PHP version or the CI matrix" >&2
  exit 1
fi
[[ -n "${mode}" ]] || echo "Today: ${today}; minimum PHP: ${min_version}; CI: $(echo "${ci_versions}" | paste -sd' ')"

ver_ge() { [[ "$(printf '%s\n%s\n' "$1" "$2" | sort -V | head -1)" == "$2" ]]; }

# Supported versions, oldest first: in the blocking CI matrix, not older than the minimum.
# Each entry is "<version> <security support ends> <supported until>", tab separated.
supported=()
while IFS=$'\t' read -r name eol _; do
  [[ "${eol}" != "null" ]] || continue
  ver_ge "${name}" "${min_version}" || continue
  grep -qxF "${name}" <<<"${ci_versions}" || continue
  supported+=("${name}"$'\t'"${eol}"$'\t'"$(date -u -d "${eol} +${SUPPORT_MONTHS} months" +%F)")
done < <(jq -r '[.data[]] | sort_by(.versionId) | .[] | [.name, (.eolDate // "null"), (.releaseDate // "null")] | @tsv' <<<"${json}")
if [[ ${#supported[@]} -eq 0 ]]; then
  echo "No supported PHP versions found in the API response" >&2
  exit 1
fi

render_badges() {
  local row version
  for row in "${supported[@]}"; do
    IFS=$'\t' read -r version _ <<<"${row}"
    printf '[![PHP %s](https://img.shields.io/badge/PHP-%s-777BB4?logo=php&logoColor=white)](docs/versioning.md#php-version-support)\n' "${version}" "${version}"
  done
}

render_summary() {
  local version until_date
  IFS=$'\t' read -r version _ until_date <<<"${supported[0]}"
  printf 'The lowest supported PHP version, %s, is supported until %s. See [PHP version support](docs/versioning.md#php-version-support) for the policy and the full schedule.\n' "${version}" "${until_date}"
}

render_table() {
  local row version eol until_date
  printf '| PHP | Security support ends | Supported until |\n|-----|-----------------------|-----------------|\n'
  for row in "${supported[@]}"; do
    IFS=$'\t' read -r version eol until_date <<<"${row}"
    printf '| %s | %s | %s |\n' "${version}" "${eol}" "${until_date}"
  done
}

# Prints a file with the text between the markers of a block replaced by $3
replace_block() {
  BLOCK="$3" awk -v s="<!-- $2:start -->" -v e="<!-- $2:end -->" '
    index($0, s) { print; print ENVIRON["BLOCK"]; skip = 1; next }
    index($0, e) { skip = 0 }
    !skip { print }' "$1"
}

# file:block:renderer
generated_blocks=(
  "README.md:php-support-badges:render_badges"
  "README.md:php-support-summary:render_summary"
  "docs/versioning.md:php-support-table:render_table"
)

# sync_blocks write|check. Returns 1 in check mode if a block is out of date.
sync_blocks() {
  local mode="$1" spec file name renderer tmp drift=0
  for spec in "${generated_blocks[@]}"; do
    IFS=: read -r file name renderer <<<"${spec}"
    if ! grep -qF "<!-- ${name}:start -->" "${file}" || ! grep -qF "<!-- ${name}:end -->" "${file}"; then
      echo "Markers for ${name} are missing in ${file}" >&2
      exit 1
    fi
    tmp="$(mktemp)"
    replace_block "${file}" "${name}" "$("${renderer}")" >"${tmp}"
    if ! cmp -s "${tmp}" "${file}"; then
      drift=1
      echo "${file}: ${name} is out of date"
      [[ "${mode}" == "write" ]] && cat "${tmp}" >"${file}"
    fi
    rm -f "${tmp}"
  done
  [[ "${mode}" == "write" ]] || return "${drift}"
}

case "${mode}" in
  --write) sync_blocks write; exit 0 ;;
  --check) sync_blocks check || { echo "Run script/php-version-support.sh --write" >&2; exit 1; }; exit 0 ;;
  *) ;;
esac

open_issue() {
  local title="$1" body="$2" existing
  if [[ "${dry_run}" == "1" ]]; then
    printf 'DRY RUN: would open issue: %s\n%s\n\n' "${title}" "${body}"
    return
  fi
  gh label create "${LABEL}" --description "PHP version support policy" --color 777BB4 >/dev/null 2>&1 || true
  # Not piped into `grep -q`: with pipefail, grep exiting early would make the pipeline fail (SIGPIPE)
  existing="$(gh issue list --state all --label "${LABEL}" --limit 200 --json title --jq '.[].title')"
  if grep -qxF -- "${title}" <<<"${existing}"; then
    echo "Issue already exists: ${title}"
    return
  fi
  gh issue create --title "${title}" --label "${LABEL}" --body "${body}"
}

deadline_epoch="$(date -u -d "${today} +${WARN_DAYS} days" +%s)"

while IFS=$'\t' read -r name eol release; do
  # Drop: the minimum version, when its 12 months after the end of security support are about to pass
  # (released only: php.watch also lists unreleased versions with projected dates)
  if [[ "${eol}" != "null" && "${release}" != "null" && ! "${release}" > "${today}" ]] && [[ "${name}" == "${min_version}" ]]; then
    until_date="$(date -u -d "${eol} +${SUPPORT_MONTHS} months" +%F)"
    if (( $(date -u -d "${until_date}" +%s) <= deadline_epoch )); then
      open_issue "Drop support for PHP ${name} (support ends ${until_date})" \
"PHP ${name} stopped receiving security fixes on ${eol}. Under the [PHP version support policy](https://github.com/open-telemetry/opentelemetry-php/blob/main/docs/versioning.md#php-version-support) it is supported until ${until_date}.

Checklist:
- [ ] Raise \`require.php\` in \`composer.json\` of every package except SemConv and proto (and the root one)
- [ ] Remove ${name} from the matrices in \`.github/workflows/php.yml\` (including \`matrix_php_version\`)
- [ ] Remove ${name} from the matrices in \`.github/workflows/publish-otel-php-base-docker-image.yml\`
- [ ] Remove the \`${name}\` dev container: \`.devcontainer/compose-php*.yml\` and its directory in \`.devcontainer/\`
- [ ] If ${name} was the default, update \`PHP_VERSION\` in \`Makefile\`, \`docker-compose*.yaml\`, \`docker/Dockerfile\` and \`.devcontainer\`
- [ ] Update rector and phan targets
- [ ] Update the supported versions listed in \`AGENTS.md\` and \`CONTRIBUTING.md\`
- [ ] Update the PHP version badges and the support summary in \`README.md\`, and the support table in \`docs/versioning.md\`: run \`script/php-version-support.sh --write\` (after \`composer.json\` is updated, it reads the minimum from there) and commit the result
- [ ] Add a \`Breaking changes\` entry to the changelogs of the affected packages
- [ ] Update the requirements on opentelemetry.io

Opened by \`script/php-version-support.sh\`."
    fi
  fi

  # Add: released version that CI doesn't test yet
  # (releaseDate, not isFutureVersion: the flag follows the real date, so TODAY couldn't test it)
  if [[ "${release}" != "null" && ! "${release}" > "${today}" ]] \
      && ver_ge "${name}" "${min_version}" && ! grep -qxF "${name}" <<<"${ci_versions}"; then
    open_issue "Add PHP ${name} to CI" \
"PHP ${name} was released on ${release} and is not in the blocking CI matrix yet.

Checklist:
- [ ] Add ${name} to the matrices in \`.github/workflows/php.yml\` (including \`matrix_php_version\`). If it runs as an experimental \`include\`, move it from there and drop its \`--ignore-platform-reqs\`
- [ ] Add ${name} to the matrices in \`.github/workflows/publish-otel-php-base-docker-image.yml\`
- [ ] Add a dev container for ${name}: \`.devcontainer/compose-php*.yml\` and its directory in \`.devcontainer/\`
- [ ] If ${name} becomes the newest, update the default \`PHP_VERSION\` in \`docker/Dockerfile\` and \`.devcontainer\`
- [ ] Update the supported versions listed in \`AGENTS.md\` and \`CONTRIBUTING.md\`
- [ ] Update the PHP version badges and the support summary in \`README.md\`, and the support table in \`docs/versioning.md\`: run \`script/php-version-support.sh --write\` and commit the result
- [ ] Add an \`Added\` entry (PHP ${name} support) to the changelogs of the affected packages
- [ ] Update the requirements on opentelemetry.io

Opened by \`script/php-version-support.sh\`."
  fi
done < <(jq -r '.data[] | [.name, (.eolDate // "null"), (.releaseDate // "null")] | @tsv' <<<"${json}")
