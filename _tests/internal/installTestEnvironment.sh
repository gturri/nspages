#!/bin/bash -ex

if [ -L "$0" ] && [ -x "$(which readlink)" ]; then
	THIS_FILE="$(readlink -mn "$0")"
else
	THIS_FILE="$0"
fi
THIS_DIR="$(readlink -f "$(dirname "$THIS_FILE")")"

#directory where Dokuwiki should be installed in order to be reachable at http://localhost
SERVER_FS_ROOT="$THIS_DIR"/temp_for_test_server_root
echo Using server fs root at: $SERVER_FS_ROOT

#Shouldn't be changed since itests try to connect to this url
host=localhost:8000
baseUrl=http://$host
dirNamePrefix=dokuwikiITestsForNsPages

. "$THIS_DIR"/../source.sh

echo "Going to install $DW_VERSION"
pushd "$THIS_DIR/../$DW_DL_CACHE"
tar -xzf "$DW_VERSION.tgz"
popd


echo " Copying files to the server"
dirName=${dirNamePrefix}
rm -rf "$SERVER_FS_ROOT"
mkdir -p "$SERVER_FS_ROOT"
destDir=$SERVER_FS_ROOT/$dirName

rm -rf $destDir
cp -r "$THIS_DIR/../$DW_DL_CACHE/$DW_VERSION" $destDir

echo " Configuring the wiki"
cp -r "$THIS_DIR"/../testEnvironment/data/* $destDir/data

echo " Installing the plugin"
pluginDir=$destDir/lib/plugins/nspages
mkdir $pluginDir

pushd "$THIS_DIR/../.." # pushd to make sure we won't have "_test" in the path (it would "grep -v" everything)
for item in $(find . -maxdepth 1 -mindepth 1 | grep -v _test | grep -v .git); do
  cp -r $item $pluginDir
done
popd

echo " Reseting some mtimes"
touch -t201504010020.00 $destDir/data/pages/ns1/a.txt
touch -t201504011020.00 $destDir/data/pages/ns1/b2.txt
touch -t201504012020.00 $destDir/data/pages/ns1/c.txt
touch -t201504012320.00 $destDir/data/pages/ns1/b1.txt
touch -t201504022220.00 $destDir/data/pages/simpleline/p1.txt
touch -t201504032220.00 $destDir/data/pages/simpleline/p2.txt

# launching the server
pushd "$SERVER_FS_ROOT"
nohup php -S $host &
SERVER_PID=$!
popd
sleep 3 # make sure the server started
echo $SERVER_PID > "$THIS_DIR/$LAST_SERVER_PID_FILE"

echo " Running the indexer"
pushd "$THIS_DIR"/../testEnvironment/data/pages
for f in $(find . -name "*txt"); do
  f=$(echo $f | cut -d '.' -f 2 | tr / :)
  wget -O /dev/null -q $baseUrl/$dirName/lib/exe/taskrunner.php?id=$f
done
echo " Installed $DW_VERSION"
popd >/dev/null

echo Done.
