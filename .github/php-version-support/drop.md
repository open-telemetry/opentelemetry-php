---
title: "Drop support for PHP {{ version }} (support ends {{ supported_until }})"
labels: [php-version-support]
---
PHP {{ version }} stopped receiving security fixes on {{ security_support_end }}. Under the [PHP version support policy](https://github.com/open-telemetry/opentelemetry-php/blob/main/docs/versioning.md#php-version-support) it is supported until {{ supported_until }}.

Checklist:
- [ ] Raise `require.php` in `composer.json` of every package except SemConv and proto (and the root one)
- [ ] Remove {{ version }} from the matrices in `.github/workflows/php.yml` (including `matrix_php_version`)
- [ ] Remove {{ version }} from the matrices in `.github/workflows/publish-otel-php-base-docker-image.yml`
- [ ] Remove the {{ version }} dev container: `.devcontainer/compose-php*.yml` and its directory in `.devcontainer/`
- [ ] If {{ version }} was the default, update `PHP_VERSION` in `Makefile`, `docker-compose*.yaml`, `docker/Dockerfile` and `.devcontainer`
- [ ] Update the rector and phan targets
- [ ] Update the supported versions listed in `AGENTS.md` and `CONTRIBUTING.md`
- [ ] Update the PHP version badges and the summary in `README.md`, and the table in `docs/versioning.md`: run `make php-support` once `composer.json` is updated, as it reads the minimum from there, and commit the result
- [ ] Add a `Breaking changes` entry to the changelogs of the affected packages
- [ ] Update the requirements on opentelemetry.io
