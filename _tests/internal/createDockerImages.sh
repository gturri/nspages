#!/bin/bash -e

./internal/dl_dw.sh

function createImage {
  set -e
  export DOCKER_DEBIAN_TAG=$(echo $1 | cut -d ' ' -f 1)
  export SERVER_FS_ROOT=$(echo $1 | cut -d ' ' -f 2)

  cat internal/Dockerfile.template | envsubst > docker/Dockerfile
  pushd docker >/dev/null
  docker build -t nspages-test-$DOCKER_DEBIAN_TAG .
  popd >/dev/null
}
export -f createImage

pushd .. >/dev/null
STASH_NAME=$(git stash create)
git archive -o nspages.tgz ${STASH_NAME:-HEAD}
mkdir _tests/docker 2>/dev/null || true
mv nspages.tgz _tests/docker
popd >/dev/null

cp -r internal/installTestEnvironment.sh testEnvironment source.sh docker
mkdir docker/dw_dl_cache 2>/dev/null || true
cp dw_dl_cache/dokuwiki-*.tgz docker/dw_dl_cache

. source.sh
echo Going to build the docker images. Parallel arg: $PARALLEL_JOB_ARG
parallel $PARALLEL_JOB_ARG -a dockerFiles.dat createImage;
