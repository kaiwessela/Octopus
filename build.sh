#! /bin/bash

# clear build folder
rm -rf build

# create new build folder and subfolders
mkdir build
mkdir build/api
mkdir build/backend
mkdir build/config

# copy frontend
cp -r src/frontend/. build

# copy api
cp -r src/api/. build/api

# copy backend files
cp -r src/backend/. build/backend

# copy config files
cp -r src/config/. build/config


# copy libs
cp -r libs/. build/libs
