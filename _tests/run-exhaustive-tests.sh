#!/bin/bash

./internal/dl_geckodriver.sh
set -e
./internal/createDockerImages.sh
. source.sh
set +e

function runTests {
  export DOCKER_DEBIAN_TAG=$(echo $1 | cut -d ' ' -f 1)
  export PHP_VERSION=$(echo $1 | cut -d ' ' -f 3)
  export NSPAGES_DOCKER_PORT=$(echo $1 | cut -d ' ' -f 4)
  echo Testing on Debian $DOCKER_DEBIAN_TAG with PHP $PHP_VERSION
  NSPAGES_DOCKER_ID=$(docker run -d -p $NSPAGES_DOCKER_PORT:80 nspages-test-$DOCKER_DEBIAN_TAG)
  mvn test
  RET_CODE=$?
  docker stop $NSPAGES_DOCKER_ID
  return $RET_CODE
}

export -f runTests

echo Going to run tests with parallel arg: $PARALLEL_JOB_ARG
parallel $PARALLEL_JOB_ARG -a dockerFiles.dat runTests;
RET_CODE=$?

if [ $RET_CODE -eq 0 ]; then
  echo SUCESS
else
  echo FAILURE
fi
exit $RET_CODE

