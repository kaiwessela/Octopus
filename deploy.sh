#! /bin/bash
if find /srv/http/octopus/media -mindepth 1 | read; then
	rm -rf /srv/http/temp
	mkdir /srv/http/temp
	cp -r /srv/http/octopus/media/. /srv/http/temp
	media_found="true"
else
	media_found="false"
fi

rm -rf build
mkdir build

cp -r Blog/. build
cp -r Octopus/. build/Octopus
cp -r vendor/. build/vendor

rm -rf /srv/http/octopus
mkdir /srv/http/octopus
cp -r build/. /srv/http/octopus

if [ "$media_found" == "true" ]; then
	cp -r /srv/http/temp/. /srv/http/octopus/media
	rm -rf /srv/http/temp
fi

chgrp -R http /srv/http/octopus
chmod -R 770 /srv/http/octopus
