#!/bin/bash -e

./internal/dl_geckodriver.sh

./internal/dl_dw.sh
sudo ./internal/installTestEnvironment.sh

# "grep -v" to discard some useless and noisy log
mvn test 2>&1 | grep -v "console.warn: LoginRecipes:"
