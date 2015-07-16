#!/bin/bash -e

./internal/createDockerImages.sh

export NSPAGES_DOCKER_PORT=5000

while IFS='' read -r line || [[ -n $line ]]; do
  export DOCKER_DEBIAN_TAG=$(echo $line | cut -d ' ' -f 1)
  export PHP_VERSION=$(echo $line | cut -d ' ' -f 3)
  echo Testing on Debian $DOCKER_DEBIAN_TAG with PHP $PHP_VERSION
  NSPAGES_DOCKER_ID=$(docker run -d -p $NSPAGES_DOCKER_PORT:80 nspages-test-$DOCKER_DEBIAN_TAG)
  mvn test
  docker stop $NSPAGES_DOCKER_ID
done < dockerFiles.dat
