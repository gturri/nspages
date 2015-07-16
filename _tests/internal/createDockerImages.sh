#!/bin/bash -e

./internal/dl_dw.sh

pushd .. >/dev/null
git archive -o nspages.tgz HEAD
mv nspages.tgz _tests/docker
popd >/dev/null

cp -r internal/installTestEnvironment.sh testEnvironment dw_dl_cache/dokuwiki-*.tgz source.sh docker

while IFS='' read -r line || [[ -n $line ]]; do
  export DOCKER_DEBIAN_TAG=$(echo $line | cut -d ' ' -f 1)
  export SERVER_FS_ROOT=$(echo $line | cut -d ' ' -f 2)

  cat internal/Dockerfile.template | envsubst > docker/Dockerfile
  pushd docker >/dev/null
  docker build -t nspages-test-$DOCKER_DEBIAN_TAG .
  popd >/dev/null
done < dockerFiles.dat
