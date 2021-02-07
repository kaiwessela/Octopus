#! /bin/bash

rm -rf build

cp -r web/. build

mkdir build/Blog

cp -r src/. build/Blog

# copy parsedown
mkdir -p build/libs/Parsedown

cd vendor/kaiwessela/parsedownforblog
./build.sh
cd ../../..
cp -r vendor/kaiwessela/parsedownforblog/build/. build

# copy astronauth
mkdir -p build/libs/Astronauth

cd vendor/kaiwessela/astronauth
./build.sh
cd ../../..
cp -r vendor/kaiwessela/astronauth/build/. build
