#!/bin/bash -e
# to run with a headless browser, call this script with MOZ_HEADLESS=1
set -o pipefail

if [ -L "$0" ] && [ -x "$(which readlink)" ]; then
	THIS_FILE="$(readlink -mn "$0")"
else
	THIS_FILE="$0"
fi
THIS_DIR="$(dirname "$THIS_FILE")"

"$THIS_DIR"/internal/dl_geckodriver.sh

"$THIS_DIR"/internal/dl_dw.sh
"$THIS_DIR"/internal/installTestEnvironment.sh

# Fix for the issue described on https://github.com/mozilla/geckodriver/issues/2010
# (needed to run on Ubuntu 2022-04 since Firefox is installed from Snap)
export TMPDIR="$HOME/temp-firefox-profile-for-nspages-tests"
echo "Using temporary dir $TMPDIR for firefox profile (you can delete it after the tests)"
mkdir -p $TMPDIR

# "grep -v" to discard some useless and noisy log
pushd "$THIS_DIR"
mvn test 2>&1 | grep -v "console.warn: LoginRecipes:"
popd

. "$THIS_DIR"/source.sh
SERVER_PID=$(cat "$THIS_DIR/internal/$LAST_SERVER_PID_FILE")
echo "Tests completed. We kill the server (PID $SERVER_PID)"
kill $SERVER_PID
