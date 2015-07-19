#!/bin/bash

. source.sh

mkdir -p $DW_DL_CACHE
cd $DW_DL_CACHE

echo "Going to download $DW_VERSION"

#Avoid downloading the tarball again if we already have it
if [ ! -e $DW_VERSION.tgz ]; then
  echo " Starting to download $DW_VERSION.tgz"
  wget http://download.dokuwiki.org/src/dokuwiki/$DW_VERSION.tgz
  chmod a+r $DW_VERSION.tgz
else
  echo " $DW_VERSION.tgz found. No need to download it again."
fi
