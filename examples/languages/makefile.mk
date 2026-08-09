APP := alto
SOURCES := $(wildcard src/*.php)
.PHONY: test build
test:
	@vendor/bin/phpunit
build: $(SOURCES)
	@echo "Building $(APP)"
	@php tools/build.php
