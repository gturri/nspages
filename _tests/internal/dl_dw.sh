#!/bin/bash
if [ -L "$0" ] && [ -x "$(which readlink)" ]; then
	THIS_FILE="$(readlink -mn "$0")"
else
	THIS_FILE="$0"
fi
THIS_DIR="$(dirname "$THIS_FILE")"

. "$THIS_DIR"/../source.sh

mkdir -p "$THIS_DIR"/../$DW_DL_CACHE
pushd "$THIS_DIR"/../$DW_DL_CACHE

echo "Going to download $DW_VERSION"

#Avoid downloading the tarball again if we already have it
if [ ! -e $DW_VERSION.tgz ]; then
  echo " Starting to download $DW_VERSION.tgz"
  wget http://download.dokuwiki.org/src/dokuwiki/$DW_VERSION.tgz
  chmod a+r $DW_VERSION.tgz
else
  echo " $DW_VERSION.tgz found. No need to download it again."
fi
popd
