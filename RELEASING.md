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
- [Fix what the dashboard shows](#fix-what-the-dashboard-shows)
- [Use the dashboard boxes](#use-the-dashboard-boxes)
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

It shows what a release from `main` would hold now:

- the packages with unreleased changes, the version each would get, and a count of entries per
  section
- the order to tag them in
- under Check before releasing, each breaking entry, cut to its first sentence
- under Check the constraints between packages, each package whose `composer.json` or
  `#[PackageDependency]` excludes another package's new version
- under No version suggested, each package with changes whose latest tag is a pre-release or can't
  be parsed
- each package's entries, collapsed

The version comes from the sections under `Unreleased` in each package's `CHANGELOG.md`, as
described in
[Write changelog entries](https://github.com/opentelemetry-php/dev-tools#write-changelog-entries).

The dashboard is rewritten on each push to core's `main` and once a day. Contrib merges and new
releases don't trigger it, so after one the issue is behind until the next rewrite. Edits to the
issue are lost at the next rewrite. Only the boxes at its foot do anything.

## Fix what the dashboard shows

When a version is wrong because of the changelog, such as an entry under the wrong section or a
missing entry, fix the changelog in a pull request to `main`. The dashboard then shows the fix, and
so does every later release pull request.

Use an override in the release pull request for a version the changelog can't give, such as a major
with no breaking entries, or to hold a package back. See
[Change a version or skip a package](#change-a-version-or-skip-a-package). An override lasts only as
long as that pull request.

A package under No version suggested is not released until an override gives it a version. The
dashboard's `--set` hint is the command-line form of the same thing.

Read each warning before opening the release pull request:

- A constraint warning usually means a dependent's constraint must be widened on `main` first.
- Any warning puts a box in the release pull request. In core, the Release approval check fails
  until it is ticked. See [Approve breaking changes](#approve-breaking-changes).

## Use the dashboard boxes

The foot of each dashboard issue has two boxes. Ticking one starts a workflow, which unticks it.

| Box | What it does | When to tick it |
|---|---|---|
| Refresh now | Rewrites the issue | After a contrib merge or a release, to see the result before the daily rewrite |
| Open a release PR | Opens the release pull request, or rebuilds the open one from the tip of `main` | To start a release, and to rebuild one after `main` moves |

If a box is still ticked a few minutes later, its run failed, or gave way to another queued run.
Check the latest run of
[Release dashboard](https://github.com/open-telemetry/opentelemetry-php/actions/workflows/release-dashboard.yml)
or [Release PR](https://github.com/open-telemetry/opentelemetry-php/actions/workflows/release-pr.yml),
then untick it and tick it again. A tick that has nothing to do changes nothing. The next rewrite
unticks every box, so after that only the run shows the failure.

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

Read the warnings, then tick it. In core, the Release approval check fails until you do. Deleting
the box or the comment under it doesn't pass the check, as the release commit records that the
warnings need a tick. A rebuild keeps the tick while the warnings are the same, and clears it when
they change.

## Rebuild after main moves

Nothing rebuilds the pull request when `main` moves. Merged as it is, the release would ship the
changes merged since, with their changelog entries still under `Unreleased`. So the workflow adds a
note to the top of the description instead, on each push to core's `main` and daily for contrib.
In core, the Release approval check then fails.

To rebuild, tick Open a release PR on the dashboard issue again, or run the Release PR workflow.
Don't use Update branch or rebase the branch. Either brings in `main` without dating the new
entries, and the Release approval check fails on it.

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

The dashboard workflow fails unless exactly one open issue has the dashboard's label. To replace
one that was closed or deleted, open an issue titled `Release dashboard: core` or
`Release dashboard: contrib`, label it `release-dashboard:core` or `release-dashboard:contrib`, then
run the Release dashboard workflow to write it.

The release pull requests are opened by the `otelbot-php` GitHub App, so their checks run like any
other pull request's. This repository needs the `OTELBOT_PHP_CLIENT_ID` variable and the
`OTELBOT_PHP_PRIVATE_KEY` secret. The app needs contents and pull requests write on core and contrib.

The Release approval check blocks a merge only once it is a required status check on `main`, which a
repository administrator sets. It is skipped on other pull requests, which counts as passing.
Contrib has no copy of the check yet, as a workflow here can't see contrib's pull requests.
