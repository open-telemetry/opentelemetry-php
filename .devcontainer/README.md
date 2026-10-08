# Devcontainers

Development containers for working on opentelemetry-php in VS Code, one per supported PHP
version. They are built on the same `opentelemetry-php-base` image that CI publishes, so the
PHP version and extension set match what CI runs.

## Usage

Run **Dev Containers: Reopen in Container** and pick a PHP version.
On first start, `make update` installs dependencies. This takes a few minutes.

### Rebuilding

Extensions and `postCreateCommand` only run when a container is first created, so a change to either
needs a rebuild rather than a reopen. From inside a devcontainer, run **Dev Containers: Rebuild
Container**; from a local window, **Dev Containers: Rebuild and Reopen in Container**. Both are in
the command palette and the Remote menu in the bottom-left corner. Add *Without Cache* to the
command name to also rebuild the image from scratch.

Rebuilding keeps the dependency volumes, so `make update` finds `vendor/` already populated and
finishes quickly. Each PHP version is a separate container and must be rebuilt separately.

### Your own extensions

The committed `devcontainer.json` files only list extensions relevant to working on this project.
To have your own editor extensions installed in every devcontainer without changing the project
files, add them to `dev.containers.defaultExtensions` in your personal VS Code `settings.json`:

```jsonc
"dev.containers.defaultExtensions": [
  "your.extension-id"
]
```

This is applied when a container is created, so an existing container needs a rebuild (see above)
before it picks up a change here — reopening it will not install anything new.

### Your own environment variables

The devcontainers read the repository's `.env`, which is not committed, so anything you put there is
passed into the container. This is a place to configure tools of your own that expect to find
credentials or settings in the environment.

Variables set in `.devcontainer/docker-compose.yml` take precedence, so `.env` cannot change the
PHP version or `XDEBUG_MODE` for a devcontainer.

Keep your own variables out of `.env.dist`: it is committed, and is copied to `.env` for anyone who
does not have one yet.

## Git over SSH

VS Code forwards your SSH agent into the container, so pushing to an `ssh://` or `git@github.com:`
remote works without copying any private key in. The first push asks you to confirm GitHub's host
key, as the container starts with an empty `known_hosts`.

### Signed commits

VS Code copies your `~/.gitconfig` in but never copies key files, so a config that signs commits
with an SSH key (`gpg.format=ssh`) arrives pointing `user.signingkey` at a file that does not
exist. Signing needs the *public* half on disk even though the private half stays in the agent on
the host, so without it every commit fails with:

```
error: Couldn't load public key /home/you/.ssh/id_ed25519.pub: No such file or directory
```

`setup-signing-key.sh` runs on container start and writes that file for you, taking the filename
from your own `user.signingkey` and the key from the forwarded agent. It does nothing if you do not
sign commits, or if `user.signingkey` is a `key::ssh-ed25519 AAAA...` literal — git passes that
form straight to the agent, so no file is needed.

If the agent holds several keys, it picks the one whose comment matches your `user.email`. When
nothing matches it writes no key rather than guess, because signing with the wrong key still
produces a commit — one that GitHub then shows as unverified, which is a confusing thing to trace
back. In that case write it yourself and restart the container:

```bash
mkdir -p ~/.ssh && chmod 700 ~/.ssh
ssh-add -L | grep 'your-key-comment' > ~/.ssh/id_ed25519.pub
```

Either way the key lives in the container's own filesystem, so it is written again after a rebuild.
For GitHub to show your commits as verified, the key must also be registered as a *signing* key on
your account — an authentication key alone is not enough.

## Running the tooling

Inside a devcontainer, the usual `make` targets run the tool directly instead of starting
another container:

```bash
make test-unit
make test-integration
make phpstan
make style
make deptrac
make rector
make all-checks
```

This works because the container sets `MAKEFLAGS=DC_RUN_PHP=`, which overrides the Makefile's
`DC_RUN_PHP` so that `$(DC_RUN_PHP)` expands to nothing. The Makefile itself is unchanged, and
running `make` on the host still goes through `docker compose run --rm php` as before.

Targets that drive other Compose stacks (`make smoke-test-*`, `make phpdoc`,
`make w3c-test-service`, `make split`) still expect a Docker daemon and are meant to be run
from the host.

### Tests from the editor

`recca0120.vscode-phpunit` is installed and configured, so tests can also be run without the
terminal: the Test Explorer lists the whole suite, and each test and test class gets run and
debug icons in the gutter. The devcontainer points it at the project's own config, so it sees
the same tests `make test` does:

```jsonc
"phpunit.php": "/usr/bin/php",
"phpunit.phpunit": "vendor/bin/phpunit",
"phpunit.args": ["--configuration", "phpunit.xml.dist"]
```

"Debug Test" needs no setup and no launch configuration. The extension picks a free port and
passes `XDEBUG_MODE=debug` with `-dxdebug.mode=debug -dxdebug.start_with_request=1
-dxdebug.client_port=<port>` for that run, then attaches to it — so breakpoints work even
though `XDEBUG_MODE` is `off` container-wide. Its coverage mode works the same way.

Unlike `make test`, which runs the two suites as separate processes, the Test Explorer's "Run
All Tests" runs them in one. That makes it sensitive to tests leaking static state, which
`executionOrder="random"` then surfaces intermittently. If a test passes alone but fails in a
full run, suspect shared state rather than the test: `use OpenTelemetry\Tests\TestState` resets
the common globals (`Globals`, `Clock`, `LoggerHolder`, `Logging`, `Discovery`,
`InMemoryStorageManager`) after each test. Reproduce a specific ordering with
`vendor/bin/phpunit --random-order-seed=<seed>`, using the seed printed in the failing run.

## Dependencies are per version

Dependencies resolve differently per PHP version — `composer.lock` is not committed, and some
dev dependencies cap the PHP versions they support. Each devcontainer therefore keeps its own
`vendor/` and `vendor-bin/*/vendor` in named volumes (`otel-php-dev-<version>-*`), isolated from
each other and from the host's directories. Switching versions does not require reinstalling,
and the host's `vendor/` is left alone.

Because the volumes are not visible on the host, use `make update` from inside the container to
change dependencies.

## Debugging

Xdebug is installed, and `XDEBUG_MODE` is `off` so tests run at full speed. It is set explicitly
rather than left unset: the base image's ini defaults `xdebug.mode` to `develop`, which costs
about 30% on a test run for features a normal run has no use for. Enable what you need per
command instead.

Debugging a test needs nothing from this section — use the gutter icons described above. The two
committed launch configurations cover the other cases.

To debug a single script, open it and run **Debug current script**. It launches the file in the
active editor with `XDEBUG_MODE=debug` and `xdebug.start_with_request=yes` set for that run only,
so breakpoints are hit without any terminal command. This one only works inside a devcontainer,
where PHP and the editor are on the same machine.

For anything the editor does not launch itself — a long pipeline, a script needing arguments —
start **Listen for Xdebug** instead and run PHP with debugging on yourself:

```bash
XDEBUG_MODE=debug XDEBUG_TRIGGER=1 php your-script.php
XDEBUG_MODE=debug XDEBUG_TRIGGER=1 vendor/bin/phpunit --filter SomeTest
```

Both variables are needed. `XDEBUG_MODE=debug` only loads the debugger; with
`xdebug.start_with_request` left at its `default`, Xdebug then waits for a trigger before it
connects, so `XDEBUG_MODE=debug` on its own runs the script straight through as if Xdebug were off.
`XDEBUG_TRIGGER=1` is that trigger. If a session is not starting, add `-d xdebug.log=/tmp/xdebug.log`
and look in the log: `Trigger value for 'XDEBUG_SESSION' not found, so not activating` means the
trigger is missing, as opposed to a `Could not connect` line, which means nothing is listening yet.

Set both on the command rather than exporting them, so only the runs you want are slowed down.
Note that `php -i` reports the ini value rather than the environment override, so `xdebug.mode`
there keeps showing `develop` even when debugging is active; `xdebug_info()` shows what is really
enabled.

The PHP Debug extension runs inside the devcontainer, so it listens on port 9003 there and
Xdebug's default `client_host=localhost` already reaches it. No path mapping is needed either,
because the workspace is at `/usr/src/myapp` in both the editor and the debugger.

**Listen for Xdebug** is also what works from a local window, where tooling runs via
`docker compose run --rm php`: there the extension listens on the host, so it needs the
`/usr/src/myapp` to `${workspaceFolder}` mapping it carries, plus
`XDEBUG_CONFIG=client_host=host.docker.internal` on the run, for example:

```bash
XDEBUG_MODE=debug XDEBUG_TRIGGER=1 XDEBUG_CONFIG=client_host=host.docker.internal \
  docker compose run --rm php vendor/bin/phpunit --filter SomeTest
```

## Services for the examples

The examples expect a collector and Zipkin (`collector:4317`, `collector:4318`, `zipkin:9411`),
which the devcontainers do not start. Everything in `make all-checks` runs without them. To run
the examples, start those services from the host with the existing Compose files, for example
`docker compose -f docker-compose.collector.yaml up -d`.
