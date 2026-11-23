---
title: "Add PHP {{ version }} to CI"
labels: [php-version-support]
---
PHP {{ version }} was released on {{ released }} and is not in the blocking CI matrix yet.

Checklist:
- [ ] Add {{ version }} to the `php-version` matrix in `.github/workflows/php.yml`. If it runs as an experimental `include`, move it from there and drop its `--ignore-platform-reqs`
- [ ] Add {{ version }} to the matrices in `.github/workflows/publish-otel-php-base-docker-image.yml`
- [ ] Add a dev container for {{ version }}: `.devcontainer/compose-php*.yml` and its directory in `.devcontainer/`
- [ ] If {{ version }} becomes the newest, update the default `PHP_VERSION` in `docker/Dockerfile` and `.devcontainer`
- [ ] Update the supported versions listed in `AGENTS.md` and `CONTRIBUTING.md`
- [ ] Update the PHP version badges and the summary in `README.md`, and the table in `docs/versioning.md`: run `make php-support` and commit the result
- [ ] Add an `Added` entry (PHP {{ version }} support) to the changelogs of the affected packages
- [ ] Update the requirements on opentelemetry.io
