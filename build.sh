#! /bin/bash

# clear build folder
rm -rf build

# create new build folder and subfolders
mkdir build
mkdir build/backend
mkdir build/config

# copy frontend
cp -r src/frontend/. build

# copy backend files
cp -r src/backend/. build/backend

# copy config files
cp -r src/config/. build/config

# set permissions for image upload folder
chmod 0777 build/resources/images/dynamic
