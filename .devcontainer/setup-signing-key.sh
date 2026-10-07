#!/usr/bin/env bash
# Materialises the public half of an SSH signing key inside the container.
#
# VS Code forwards your SSH agent and copies your ~/.gitconfig in, but it never copies key
# files. So a config with `gpg.format=ssh` arrives pointing `user.signingkey` at a path that
# does not exist, and every commit fails with:
#
#   error: Couldn't load public key /home/php/.ssh/id_ed25519.pub: No such file or directory
#
# Signing needs the public key on disk even though the private half stays in the agent on the
# host. This writes it, deriving both the filename and which key to use from the developer's
# own git config, and does nothing at all for the majority of contributors who do not sign.
set -uo pipefail

# Never fail the container start over commit signing: a non-signing contributor should not
# see an error, and a signer is better off with a clear message than a broken window.
note() { echo "signing key: $*"; }

[[ "$(git config --get gpg.format || true)" == "ssh" ]] || exit 0
[[ "$(git config --get --type=bool commit.gpgsign || true)" == "true" ]] || exit 0

signingkey="$(git config --get user.signingkey || true)"
[[ -n "${signingkey}" ]] || exit 0

# A `key::ssh-ed25519 AAAA...` literal is passed straight to the agent and needs no file.
[[ "${signingkey}" == key::* ]] && exit 0

# git expands a leading ~ itself, so the same path has to be expanded here to find the file.
target="${signingkey/#\~/${HOME}}"

# ssh-keygen signs with `-f <path>`, reading the public half from <path>.pub when the config
# names the private key instead. Writing the .pub beside it satisfies both spellings.
[[ "${target}" == *.pub ]] || target="${target}.pub"

[[ -f "${target}" ]] && exit 0

# A lifecycle hook does not reliably inherit SSH_AUTH_SOCK, so fall back to the socket that
# VS Code binds into /tmp. Newest wins: a reconnect leaves the stale socket behind.
if ! ssh-add -l >/dev/null 2>&1; then
  sock="$(ls -t /tmp/vscode-ssh-auth-*.sock 2>/dev/null | head -1)"
  [[ -n "${sock}" ]] && export SSH_AUTH_SOCK="${sock}"
fi

if ! keys="$(ssh-add -L 2>/dev/null)" || [[ -z "${keys}" ]]; then
  note "no SSH agent key found, so ${target} was not written. Commits will fail to sign."
  note "check that your agent is running and holds a key on the host, then rebuild."
  exit 0
fi

# Signing with the wrong key is worse than not signing: git produces a signature happily and
# the commit then shows as unverified, which is a confusing failure to trace back to here.
# So pick only when the choice is unambiguous, matching the key comment against user.email.
if [[ "$(wc -l <<<"${keys}")" -eq 1 ]]; then
  key="${keys}"
else
  email="$(git config --get user.email || true)"
  key="$(grep -F " ${email}" <<<"${keys}" | head -1)"
  if [[ -z "${key}" ]]; then
    note "the agent holds several keys and none names <${email}>, so none was chosen."
    note "write the right one yourself, then restart the container:"
    note "  ssh-add -L | grep <your-key-comment> > ${target}"
    exit 0
  fi
fi

mkdir -p "$(dirname "${target}")" && chmod 700 "$(dirname "${target}")" || exit 0
# Write via a temporary file so an interrupted run cannot leave a half-written key in place.
tmp="$(mktemp "${target}.XXXXXX")" || exit 0
printf '%s\n' "${key}" >"${tmp}" && chmod 644 "${tmp}" && mv "${tmp}" "${target}" || {
  rm -f "${tmp}"
  note "could not write ${target}."
  exit 0
}

note "wrote ${target} (${key##* }) from the forwarded agent."
