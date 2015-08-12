#!/bin/bash -e

./internal/dl_dw.sh

pushd .. >/dev/null
STASH_NAME=$(git stash create)
git archive -o nspages.tgz ${STASH_NAME:-HEAD}
mkdir _tests/docker || true
mv nspages.tgz _tests/docker
popd >/dev/null

cp -r internal/installTestEnvironment.sh testEnvironment source.sh docker
mkdir docker/dw_dl_cache || true
cp dw_dl_cache/dokuwiki-*.tgz docker/dw_dl_cache

while IFS='' read -r line || [[ -n $line ]]; do
  export DOCKER_DEBIAN_TAG=$(echo $line | cut -d ' ' -f 1)
  export SERVER_FS_ROOT=$(echo $line | cut -d ' ' -f 2)

  cat internal/Dockerfile.template | envsubst > docker/Dockerfile
  pushd docker >/dev/null
  docker build -t nspages-test-$DOCKER_DEBIAN_TAG .
  popd >/dev/null
done < dockerFiles.dat
