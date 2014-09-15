# ex:ts=8:sw=8:noexpandtab

ROOT_CHILD := $(shell pwd)
ROOT_PARENT := $(shell grep __PARENT_ROOT__ core/main.inc.php |  cut -d ' ' -f6 | tr -d "'")

dev:
	@perl -pi -e "s/MODE',[0-1]/MODE',1/" "$(ROOT_CHILD)/config/dev.php";
	@echo "DEV MODE";

prod:
	@perl -pi -e "s/MODE',[0-1]/MODE',0/" "$(ROOT_CHILD)/config/dev.php";
	@echo "PROD MODE";

local:
	@perl -pi -e "s/ENV','.*'/ENV','local'/" "$(ROOT_CHILD)/config/dev.php";
	@echo "Local ENV";

remote:
	@perl -pi -e "s/ENV','.*'/ENV','remote'/" "$(ROOT_CHILD)/config/dev.php";
	@echo "Remote ENV";

frisby:
	jasmine-node test/frisby/*_spec.js;

validate_routes:
	@php -f $(ROOT_PARENT)/tool/_validate_routes.php $(ROOT_CHILD)/config/routes.yaml;

fork:
	@echo "backup...";
	-@mv $(ROOT_CHILD)/../${name} $(shell mktemp -d -t phponion)
	@echo "forking project structure...";
	mkdir -p $(ROOT_CHILD)/../${name}/core
	cp $(ROOT_CHILD)/../PhpOnion/core/main.inc.php $(ROOT_CHILD)/../${name}/core/main.inc.php
	mkdir -p $(ROOT_CHILD)/../${name}/config
	cp -r $(ROOT_CHILD)/../PhpOnion/config $(ROOT_CHILD)/../${name}/

	mkdir -p $(ROOT_CHILD)/../${name}/entry
	cp $(ROOT_CHILD)/../PhpOnion/entry/index.php $(ROOT_CHILD)/../${name}/entry/index.php

	mkdir -p $(ROOT_CHILD)/../${name}/htdoc
	cp $(ROOT_CHILD)/../PhpOnion/htdoc/.htaccess $(ROOT_CHILD)/../${name}/htdoc/.htaccess
	ln -s $(ROOT_CHILD)/../${name}/entry/index.php $(ROOT_CHILD)/../${name}/htdoc/index.php

	mkdir -p $(ROOT_CHILD)/../${name}/node_business
	mkdir -p $(ROOT_CHILD)/../${name}/node_common
	mkdir -p $(ROOT_CHILD)/../${name}/lib
	mkdir -p $(ROOT_CHILD)/../${name}/schema
	mkdir -p $(ROOT_CHILD)/../${name}/tool
	mkdir -p $(ROOT_CHILD)/../${name}/vendor

	cp $(ROOT_CHILD)/../PhpOnion/Makefile $(ROOT_CHILD)/../${name}/Makefile
	cp $(ROOT_CHILD)/../PhpOnion/.gitignore $(ROOT_CHILD)/../${name}/.gitignore

	@echo "configuring..."
	@perl -pi -e "s/___SITE___/${name}/" "$(ROOT_CHILD)/../${name}/config/prerequisite.php";
	@perl -pi -e "s/___SITE___/${name}/" "$(ROOT_CHILD)/../${name}/htdoc/.htaccess";
	@perl -pi -e "s|/[*][*]/ //__PARENT_PROJECT__|/** //__PARENT_PROJECT__|" "$(ROOT_CHILD)/../${name}/core/main.inc.php";
	@perl -pi -e "s|/[*][*] //__CHILD_PROJECT__|/**/ //__CHILD_PROJECT__|" "$(ROOT_CHILD)/../${name}/core/main.inc.php";

# refs: https://algorithms.rdio.com/post/make/