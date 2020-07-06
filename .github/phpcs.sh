#!/bin/bash
CHANGED_FILES=$(git diff --name-only --diff-filter=ACMRTUXB "${TRAVIS_COMMIT_RANGE}")
GIT_ROOT_DIR=$(git rev-parse --show-toplevel)

if ! echo "${CHANGED_FILES}" | grep -qE "^(\\.php_cs(\\.dist)?|composer\\.lock)$"; then
  EXTRA_ARGS=$(printf -- '--path-mode=intersection\n--\n%s' "${CHANGED_FILES}");
else
  EXTRA_ARGS='';
fi

echo php-cs-fixer binary     :  ${GIT_ROOT_DIR}/vendor/bin/php-cs-fixer
echo php-cs-fixer config file:  ${GIT_ROOT_DIR}/.php_cs
${GIT_ROOT_DIR}/vendor/bin/php-cs-fixer fix --config=${GIT_ROOT_DIR}/.php_cs -v --dry-run --stop-on-violation --using-cache=no ${EXTRA_ARGS}
