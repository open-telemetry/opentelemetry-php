#!/usr/bin/env bash
# Fails if a split package changed without a change to its CHANGELOG.md.
# Arguments are passed to `git diff`. Without them, it checks everything since
# origin/main, including uncommitted and untracked files.
# Packages without a CHANGELOG.md, and changes to Markdown files only, are ignored.
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "${ROOT_DIR}"

if [[ $# -eq 0 ]]; then
  changed="$(git diff --name-only "$(git merge-base origin/main HEAD)"; git ls-files --others --exclude-standard)"
else
  changed="$(git diff --name-only "$@")"
fi
missing=0

while read -r prefix; do
  [[ -f "${prefix}/CHANGELOG.md" ]] || continue
  source_changed=false
  changelog_changed=false
  while read -r file; do
    [[ "${file}" == "${prefix}/"* ]] || continue
    if [[ "${file}" == "${prefix}/CHANGELOG.md" ]]; then
      changelog_changed=true
    elif [[ "${file}" != *.md ]]; then
      source_changed=true
    fi
  done <<<"${changed}"

  if [[ "${source_changed}" == true && "${changelog_changed}" == false ]]; then
    missing=1
    message="${prefix} changed, but its CHANGELOG.md didn't. Add an entry under Unreleased if the change affects users. If it doesn't, a maintainer can add the skip-changelog label."
    if [[ -n "${GITHUB_ACTIONS:-}" ]]; then
      echo "::error file=${prefix}/CHANGELOG.md::${message}"
    else
      echo "${message}"
    fi
  fi
done < <(sed -n 's/^ *- prefix: "\(.*\)"$/\1/p' .gitsplit.yml)

exit "${missing}"
