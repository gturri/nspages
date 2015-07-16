#!/bin/bash -e

./internal/dl_dw.sh
sudo ./internal/installTestEnvironment.sh
mvn test
