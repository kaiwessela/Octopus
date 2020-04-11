#! /bin/bash

# empty build folder
rm -rf build

# create new build folder and subfolders
mkdir build
mkdir build/api
mkdir build/backend
mkdir build/config
mkdir build/libs

# copy frontend
cp -rf src/frontend/* build

# copy api files
cp -rf src/api/* build/api

# copy backend files
cp -rf src/backend/* build/backend

# copy config files
cp -rf src/config/* build/config

# copy libraries
cp -rf libs/* build/libs
