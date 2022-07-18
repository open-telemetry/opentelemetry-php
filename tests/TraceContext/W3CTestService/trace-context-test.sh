#!/usr/bin/env bash
set -x

function install_symfony() {
    curl -1sLf 'https://dl.cloudsmith.io/public/symfony/stable/setup.alpine.sh' | bash >/dev/null
    apk add symfony-cli
}

cd "tests/TraceContext/W3CTestService" || { echo "error: could not cd into the W3CTestService directory."; exit 1; }

# Install Symfony: we will use the Symfony server as the built-in PHP server doesn't play well with duplicate headers
install_symfony

# Start the test service in the background
symfony server:start -d --port=8001 --no-tls

# Setup dependencies for the trace-context test
apk add --no-cache py3-pip py3-aiohttp

# Fetch the latest trace-context tests
rm -rf trace-context
git clone https://github.com/w3c/trace-context.git

# Run the test
python3 "trace-context/test/test.py" http://127.0.0.1:8001/test
