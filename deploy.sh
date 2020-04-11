#! /bin/bash

./build.sh

rm -rf /var/www/home.local/*
cp -r ./build/* /var/www/home.local
