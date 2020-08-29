#! /bin/bash

# clear build folder
rm -rf build

# create new build folder and subfolders
mkdir build
mkdir build/libs

# copy
cp -r src/. build

# copy parsedown
mkdir build/libs/parsedown
cp libs/parsedown/Parsedown.php build/libs/parsedown/Parsedown.php

# copy astronauth
cd libs/astronauth
./build.sh
cd ../..
cp -r libs/astronauth/build/. build/astronauth
