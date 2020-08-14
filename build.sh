#! /bin/bash

# clear build folder
rm -rf build

# create new build folder and subfolders
mkdir build
mkdir build/libs

# copy
cp -r src/. build
cp -r libs/. build/libs


cd libs/astronauth
./build.sh
cd ../..
cp -r libs/astronauth/build/. build/astronauth
