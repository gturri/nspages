#!/bin/bash

#directory where Dokuwiki should be installed in order to be reachable at http://localhost
serverFileSystemRoot=/var/www/html
#Owner of the files (to make sure the instance of dokuwiki can ediable its pages)
serverFileSystemOwner=www-data
#Shouldn't be changed since itests try to connect to this url
baseUrl=http://localhost
dirNamePrefix=dokuwikiITestsForNsPages

dwVersions="dokuwiki-2014-09-29a"
installDir=tmpForInstallation
relativeTestFileDir=testEnvironment

mkdir -p $installDir
cd $installDir

function installFakeWiki {
#Argument 1 is the name of the version of Dokuwiki to install
  dwVersion=$1
  echo "Going to install $dwVersion"
  pushd . >/dev/null
  
  #Avoid downloading the tarball again if we already have it
  if [ ! -e $dwVersion.tgz ]; then
    echo " Starting to download $dwVersion.tgz"
    wget http://download.dokuwiki.org/src/dokuwiki/$dwVersion.tgz
  else
    echo " $dwVersion.tgz found. No need to download it again."
  fi
  
  rm -rf $dwVersion
  tar -xzf $dwVersion.tgz
  
  
  echo " Copying files to the server"
  dirName=${dirNamePrefix}${dwVersion}
  destDir=$serverFileSystemRoot/$dirName

  rm -rf $destDir 
  cp -r $dwVersion $destDir
  
  echo " Configuring the wiki"
  cp -r ../$relativeTestFileDir/data/* $destDir/data

  echo " Installing the plugin"
  pluginDir=$destDir/lib/plugins/nspages
  mkdir $pluginDir
  for item in $(find ../.. -maxdepth 1 -mindepth 1 | grep -v _test | grep -v .git); do
    cp -r $item $pluginDir
  done

  chown -R $serverFileSystemOwner $destDir
  
  echo " Running the indexer"
  cd ../testEnvironment/data/pages
  for f in $(find . -name "*txt"); do
    f=$(echo $f | cut -d '.' -f 2 | tr / :)
    wget -O /dev/null -q $baseUrl/$dirName/lib/exe/indexer.php?id=$f
  done
  echo " Installed $dwVersion"
  popd >/dev/null
}

for dwVersion in $dwVersions; do
  installFakeWiki $dwVersion
done

echo Done.
