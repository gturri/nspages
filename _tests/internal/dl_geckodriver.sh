#!/bin/bash -e
if [ -L "$0" ] && [ -x $(which readlink) ]; then
  thisFile="$(readlink -mn "$0")"
else
  thisFile="$0"
fi
thisDir="$(dirname "$thisFile")"
RESOURCE_DIR=$thisDir/../src/test/resources
if [ ! -e $RESOURCE_DIR/geckodriver ]; then
  echo Downloading geckodriver
  ARCHIVE=geckodriver-v0.13.0-linux64.tar.gz
  rm -f $ARCHIVE
  wget https://github.com/mozilla/geckodriver/releases/download/v0.13.0/$ARCHIVE
  tar xf $ARCHIVE
  mkdir -p $RESOURCE_DIR
  mv geckodriver $RESOURCE_DIR
  rm $ARCHIVE
else
  echo Geckodriver already present
fi
