VERSION := $(shell git for-each-ref refs/tags --format='%(refname:short)' --sort=-taggerdate --count 1)

# Create a timestamp-tagged local version variable so when we reload Minikube clusters they are properly updated with
# fresh code.
LOCAL_VERSION := local-$(shell date +%s)

help: ## Prints this help
	@grep -E '^([a-zA-Z0-9_-]|\%|\/)+:.*?## .*$$' Makefile | sort | awk 'BEGIN {FS = ":.*?## "}; {sub(/\%/, "<blah>", $$1)}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

default:
	composer install

distclean: ## Distclean
	rm -rf vendor/
	make dev-stop
	make dev-distclean

test: ## Run PHPUnit tests
	./vendor/bin/phpunit

psalm: ## Run Psalm validation
	./vendor/bin/psalm

test: psalm phpunit

##
## Local development
##
dev-install: default dev ## Create a local development environment

dev-init:
	docker-compose build

dev: dev-init ## Start up your development environment (will create a new one if none present)
	docker-compose up -d

dev-stop: ## End the current development environment
	docker-compose down

dev-distclean: ## Wipe the current development environment
	docker-compose down --rmi all

dev-ssh: ## SSH into your current development environment
	docker exec -it spirit-php /bin/bash
