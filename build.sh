#! /bin/bash

rm -rf build

cp -r web/. build

mkdir build/Blog

cp -r src/. build/Blog

# copy parsedown
mkdir -p build/libs/parsedown
cp libs/parsedown/Parsedown.php build/libs/parsedown/Parsedown.php

# copy astronauth
mkdir -p build/libs/astronauth

cd libs/astronauth
./build.sh
cd ../..
cp -r libs/astronauth/build/. build/libs/astronauth
