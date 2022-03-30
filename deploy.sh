#! /bin/bash
if find /srv/http/octopus/media -mindepth 1 | read; then
	mkdir /srv/http/temp
	cp -r /srv/http/octopus/media/. /srv/http/temp
	media_found="true"
else
	media_found="false"
fi

rm -rf build
mkdir build

cp -r admin/. build/admin-old
cp -r admin-new/. build/admin
cp -r api/. build/api
cp -r astronauth/. build/astronauth
cp -r Octopus/. build/Octopus
cp -r media/. build/media
cp -r resources/. build/resources
cp -r templates/. build/templates
cp -r vendor/. build/vendor
cp -r .htaccess build
cp -r index.php build
cp -r routes.php build

# TEMP
cp -r test/. build/test

rm -rf /srv/http/octopus
mkdir /srv/http/octopus
cp -r build/. /srv/http/octopus

if [ "$media_found" == "true" ]; then
	cp -r /srv/http/temp/. /srv/http/octopus/media
	rm -rf /srv/http/temp
fi
