# Releasing

Packages are released by tagging their read-only split repositories in the
[opentelemetry-php](https://github.com/opentelemetry-php) organization. This repository's workflows
prepare releases of both monorepos, this one (core) and
[contrib](https://github.com/open-telemetry/opentelemetry-php-contrib). Contrib has issues turned off,
and only this repository can use the bot's key, so contrib's release starts here too.

The workflows run [dev-tools](https://github.com/opentelemetry-php/dev-tools). Its README covers each
command in detail.

## Contents

- [See what is ready to release](#see-what-is-ready-to-release)
- [Open a release pull request](#open-a-release-pull-request)
- [Change a version or skip a package](#change-a-version-or-skip-a-package)
- [Approve breaking changes](#approve-breaking-changes)
- [Rebuild after main moves](#rebuild-after-main-moves)
- [Merge and tag](#merge-and-tag)
- [Release without the workflows](#release-without-the-workflows)
- [What the workflows need](#what-the-workflows-need)

## See what is ready to release

Each monorepo has a release dashboard issue in this repository:

- [core](https://github.com/open-telemetry/opentelemetry-php/issues?q=is%3Aissue+is%3Aopen+label%3Arelease-dashboard%3Acore)
- [contrib](https://github.com/open-telemetry/opentelemetry-php/issues?q=is%3Aissue+is%3Aopen+label%3Arelease-dashboard%3Acontrib)

It lists the packages with unreleased changes, the version each would get, the order to tag them in,
and any warnings. The version comes from the sections under `Unreleased` in each package's
`CHANGELOG.md`, as described in
[Write changelog entries](https://github.com/opentelemetry-php/dev-tools#write-changelog-entries).

The dashboard is rewritten on each push to core's `main` and once a day. Contrib merges don't
trigger it, so tick Refresh now at the foot of contrib's issue to see a merge straight away. Edits
to a dashboard issue are lost at the next rewrite.

## Open a release pull request

Tick Open a release PR at the foot of the dashboard issue. Or run the
[Release PR](https://github.com/open-telemetry/opentelemetry-php/actions/workflows/release-pr.yml)
workflow and choose the monorepo. A tick needs write access to that monorepo, and running the
workflow needs write access to core.

The workflow runs `release:prep` on `main`, which dates each changelog section and moves the branch
aliases. It commits the result as `otelbot-php[bot]` to the branch `otelbot/release`, and opens a pull
request in the monorepo. Contrib's pull request is in contrib. If one is already open, it is rebuilt
instead. When there is nothing left to release, the open pull request is closed.

The description shows the versions, the tag order and the warnings. Review it, and the changelogs in
the diff, as you would any release.

To fix a changelog entry, change it in a pull request to `main`, then rebuild. A commit pushed to
`otelbot/release` is lost at the next rebuild.

## Change a version or skip a package

The description ends with an `overrides` block. Add one line per package, as
`<package>=<version|skip>`. The package is the composer name or the split repository name:

````markdown
```overrides
sdk=1.16.0
open-telemetry/exporter-zipkin=skip
```
````

In core, saving the description rebuilds the pull request with the overrides. Edits to contrib's
pull request start no workflow, so save, then rebuild. A rebuild keeps the overrides.

An invalid line fails the rebuild, and the run names it.

## Approve breaking changes

When a package records breaking changes, gets a new major version, or breaks a constraint between
packages, the description has a Breaking changes section with a box:

```markdown
- [ ] I have read the breaking changes
```

Read the warnings, then tick it. In core, the Release approval check fails until you do. A rebuild
keeps the tick while the warnings are the same, and clears it when they change.

## Rebuild after main moves

Nothing rebuilds the pull request when `main` moves. Merged as it is, the release would ship the
changes merged since, with their changelog entries still under `Unreleased`. So the workflow adds a
note to the top of the description instead, on each push to core's `main` and daily for contrib.
In core, the Release approval check then fails.

To rebuild, tick Open a release PR on the dashboard issue again, or run the Release PR workflow.
Don't use Update branch. It merges `main` into the release without dating the new entries, and
the Release approval check fails on it.

## Merge and tag

Merge the release pull request once it is approved and its checks pass. gitsplit then pushes each
package to its split repository.

Tagging is not automated yet. Run
[release:run](https://github.com/opentelemetry-php/dev-tools#tag-a-release) by hand, from a full
checkout of the monorepo at the release's merge commit. It needs a token that can create releases in
the opentelemetry-php organization, which only its administrators have. See
[Create a GitHub token](https://github.com/opentelemetry-php/dev-tools#create-a-github-token).

```shell
git fetch origin
git checkout <merge commit>
composer install
export GITHUB_TOKEN=<fine-grained-token>
vendor/bin/otel release:run --path=. --sha=<merge commit> --dry-run
vendor/bin/otel release:run --path=. --sha=<merge commit>
```

`--sha` tags the merge commit, not the tip of `main`, so a later merge is not released by mistake.
Packages are tagged in the order the description shows. If the run stops because gitsplit hasn't
pushed yet, wait and run it again. Packages already tagged are skipped.

## Release without the workflows

The workflows run the same commands a maintainer can run from a checkout. If they are unavailable,
follow
[Release a monorepo, step by step](https://github.com/opentelemetry-php/dev-tools#release-a-monorepo-step-by-step).

## What the workflows need

| Workflow | What it does |
|---|---|
| `release-dashboard.yml` | Rewrites the dashboard issues |
| `release-pr.yml` | Opens and rebuilds the release pull requests, and adds the note when `main` moves |
| `release-approval.yml` | The Release approval check on core's release pull request |

The release pull requests are opened by the `otelbot-php` GitHub App, so their checks run like any
other pull request's. This repository needs the `OTELBOT_PHP_CLIENT_ID` variable and the
`OTELBOT_PHP_PRIVATE_KEY` secret. The app needs contents and pull requests write on core and contrib.

The Release approval check blocks a merge only once it is a required status check on `main`, which a
repository administrator sets. It is skipped on other pull requests, which counts as passing.
Contrib has no copy of the check yet, as a workflow here can't see contrib's pull requests.
