#!/bin/bash

#directory where Dokuwiki should be installed in order to be reachable at http://localhost
SERVER_FS_ROOT=${SERVER_FS_ROOT:-/var/www/html}
echo Using server fs root at: $SERVER_FS_ROOT

#Owner of the files (to make sure the instance of dokuwiki can edit its pages)
serverFileSystemOwner=www-data
#Shouldn't be changed since itests try to connect to this url
baseUrl=http://localhost
dirNamePrefix=dokuwikiITestsForNsPages

. source.sh
relativeTestFileDir=testEnvironment

pushd $DW_DL_CACHE >/dev/null

echo "Going to install $DW_VERSION"

tar -xzf $DW_VERSION.tgz


echo " Copying files to the server"
dirName=${dirNamePrefix}${DW_VERSION}
destDir=$SERVER_FS_ROOT/$dirName

rm -rf $destDir 
cp -r $DW_VERSION $destDir

echo " Configuring the wiki"
cp -r ../$relativeTestFileDir/data/* $destDir/data

echo " Installing the plugin"
pluginDir=$destDir/lib/plugins/nspages
mkdir $pluginDir
for item in $(find ../.. -maxdepth 1 -mindepth 1 | grep -v _test | grep -v .git); do
  cp -r $item $pluginDir
done

echo " Reseting some mtimes"
touch -t201504010020.00 $destDir/data/pages/ns1/a.txt
touch -t201504011020.00 $destDir/data/pages/ns1/b2.txt
touch -t201504012020.00 $destDir/data/pages/ns1/c.txt
touch -t201504012320.00 $destDir/data/pages/ns1/b1.txt

chown -R $serverFileSystemOwner $destDir

echo " Running the indexer"
cd ../testEnvironment/data/pages
for f in $(find . -name "*txt"); do
  f=$(echo $f | cut -d '.' -f 2 | tr / :)
  wget -O /dev/null -q $baseUrl/$dirName/lib/exe/indexer.php?id=$f
done
echo " Installed $DW_VERSION"
popd >/dev/null

echo Done.
