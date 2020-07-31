#! /bin/bash

# clear build folder
rm -rf build

# create new build folder and subfolders
mkdir build
mkdir build/api
mkdir build/backend
mkdir build/config
mkdir build/share
mkdir build/libs

# copy files
cp -r src/frontend/. build
cp -r src/api/. build/api
cp -r src/backend/. build/backend
cp -r src/config/. build/config
cp -r src/share/. build/share
cp -r libs/. build/libs
