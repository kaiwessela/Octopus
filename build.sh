#! /bin/bash

rm -rf build

cp -r web/. build

mkdir build/Blog

cp -r src/. build/Blog

# copy parsedown
mkdir -p build/libs/Parsedown

cd libs/ParsedownForBlog
./build.sh
cd ../..
cp -r libs/ParsedownForBlog/build/. build


# copy astronauth
mkdir -p build/libs/Astronauth

cd libs/astronauth
./build.sh
cd ../..
cp -r libs/astronauth/build/. build
