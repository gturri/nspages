#!/bin/bash -e

./internal/dl_geckodriver.sh

./internal/dl_dw.sh
sudo ./internal/installTestEnvironment.sh

# Fix for the issue described on https://github.com/mozilla/geckodriver/issues/2010
# (needed to run on Ubuntu 2022-04 since Firefox is installed from Snap)
export TMPDIR="$HOME/temp-firefox-profile-for-nspages-tests"
echo "Using temporary dir $TMPDIR for firefox profile (you can delete it after the tests)"
mkdir -p $TMPDIR

# "grep -v" to discard some useless and noisy log
mvn test 2>&1 | grep -v "console.warn: LoginRecipes:"
