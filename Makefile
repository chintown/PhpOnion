# ex:ts=8:sw=8:noexpandtab

SRCTOP := $(shell pwd)

dev:
	@perl -pi -e "s/MODE',[0-1]/MODE',1/" "$(SRCTOP)/config/dev.php";
	@echo "DEV MODE";

prod:
	@perl -pi -e "s/MODE',[0-1]/MODE',0/" "$(SRCTOP)/config/dev.php";
	@echo "PROD MODE";

local:
	@perl -pi -e "s/ENV','.*'/ENV','local'/" "$(SRCTOP)/config/dev.php";
	@echo "Local ENV";

remote:
	@perl -pi -e "s/ENV','.*'/ENV','remote'/" "$(SRCTOP)/config/dev.php";
	@echo "Remote ENV";

frisby:
	jasmine-node test/frisby/*_spec.js;

validate_routes:
	@php -f tool/validate_routes.php