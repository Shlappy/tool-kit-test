include .env
ifneq ("$(wildcard .env.local)", "")
	include .env.local
endif

ifndef INSIDE_DOCKER_CONTAINER
	INSIDE_DOCKER_CONTAINER = 0
endif

HOST_UID := $(shell id -u)
HOST_GID := $(shell id -g)
INTERACTIVE := $(shell [ -t 0 ] && echo 1)
PHP_USER := -u www-data
PROJECT_NAME := -p ${COMPOSE_PROJECT_NAME}
ERROR_ONLY_FOR_HOST = @printf "\033[33mThis command for host machine\033[39m\n"
ifneq ($(INTERACTIVE), 1)
	OPTION_T := -T
endif

start: ## Build dev environment
ifeq ($(INSIDE_DOCKER_CONTAINER), 0)
	@HOST_UID=$(HOST_UID) HOST_GID=$(HOST_GID) WEB_PORT_HTTP=$(WEB_PORT_HTTP) WEB_PORT_SSL=$(WEB_PORT_SSL) POSTGRES_PASSWORD=$(POSTGRES_PASSWORD) POSTGRES_PORT=$(POSTGRES_PORT) docker compose -f compose.yaml build
	@HOST_UID=$(HOST_UID) HOST_GID=$(HOST_GID) WEB_PORT_HTTP=$(WEB_PORT_HTTP) WEB_PORT_SSL=$(WEB_PORT_SSL) POSTGRES_PASSWORD=$(POSTGRES_PASSWORD) POSTGRES_PORT=$(POSTGRES_PORT) docker compose -f compose.yaml $(PROJECT_NAME) up -d
	@make exec-bash cmd="COMPOSER_MEMORY_LIMIT=-1 composer install --optimize-autoloader"
else
	$(ERROR_ONLY_FOR_HOST)
endif

start-prod: load-prod-env ## Build prod environment
ifeq ($(INSIDE_DOCKER_CONTAINER), 0)
	@HOST_UID=$(HOST_UID) HOST_GID=$(HOST_GID) WEB_PORT_HTTP=$(WEB_PORT_HTTP) WEB_PORT_SSL=$(WEB_PORT_SSL) POSTGRES_PASSWORD=$(POSTGRES_PASSWORD) POSTGRES_PORT=$(POSTGRES_PORT) docker compose -f compose-prod.yaml build
	@HOST_UID=$(HOST_UID) HOST_GID=$(HOST_GID) WEB_PORT_HTTP=$(WEB_PORT_HTTP) WEB_PORT_SSL=$(WEB_PORT_SSL) POSTGRES_PASSWORD=$(POSTGRES_PASSWORD) POSTGRES_PORT=$(POSTGRES_PORT) docker compose -f compose-prod.yaml $(PROJECT_NAME) up -d
else
	$(ERROR_ONLY_FOR_HOST)
endif

stop: ## Stop dev environment containers
ifeq ($(INSIDE_DOCKER_CONTAINER), 0)
	@HOST_UID=$(HOST_UID) HOST_GID=$(HOST_GID) WEB_PORT_HTTP=$(WEB_PORT_HTTP) WEB_PORT_SSL=$(WEB_PORT_SSL) POSTGRES_PASSWORD=$(POSTGRES_PASSWORD) POSTGRES_PORT=$(POSTGRES_PORT) docker compose -f compose.yaml $(PROJECT_NAME) stop
else
	$(ERROR_ONLY_FOR_HOST)
endif

stop-prod: load-prod-env ## Stop prod environment containers
ifeq ($(INSIDE_DOCKER_CONTAINER), 0)
	@HOST_UID=$(HOST_UID) HOST_GID=$(HOST_GID) WEB_PORT_HTTP=$(WEB_PORT_HTTP) WEB_PORT_SSL=$(WEB_PORT_SSL) POSTGRES_PASSWORD=$(POSTGRES_PASSWORD) POSTGRES_PORT=$(POSTGRES_PORT) docker compose -f compose-prod.yaml $(PROJECT_NAME) stop
else
	$(ERROR_ONLY_FOR_HOST)
endif

restart: stop start ## Stop and start dev environment
restart-prod: stop-prod start-prod ## Stop and start prod environment

migrate: ## Runs all migrations for main/test databases
	@make exec cmd="php bin/console doctrine:migrations:migrate --no-interaction"
	@make exec cmd="php bin/console doctrine:migrations:migrate --no-interaction --env=test"

fixtures: ## Runs all fixtures for test database without --append option (tables will be dropped and recreated)
	@make exec cmd="php bin/console doctrine:fixtures:load --env=test"

phpunit: ## Runs PhpUnit tests
	@make exec-bash cmd="rm -rf ./var/cache/test* && bin/console cache:warmup --env=test && ./vendor/bin/phpunit -c phpunit.xml.dist --coverage-html reports/coverage $(PHPUNIT_OPTIONS) --coverage-clover reports/clover.xml --log-junit reports/junit.xml"

phpstan: ## Runs PhpStan static analysis tool
ifeq ($(INSIDE_DOCKER_CONTAINER), 1)
	@echo "\033[32mRunning PHPStan - PHP Static Analysis Tool\033[39m"
	@bin/console cache:clear --env=test
	@./vendor/bin/phpstan --version
	@./vendor/bin/phpstan analyze src tests
else
	@make exec cmd="make phpstan"
endif

exec-bash:
ifeq ($(INSIDE_DOCKER_CONTAINER), 1)
	@bash -c "$(cmd)"
else
	@HOST_UID=$(HOST_UID) HOST_GID=$(HOST_GID) WEB_PORT_HTTP=$(WEB_PORT_HTTP) WEB_PORT_SSL=$(WEB_PORT_SSL) POSTGRES_PASSWORD=$(POSTGRES_PASSWORD) POSTGRES_PORT=$(POSTGRES_PORT) docker compose $(PROJECT_NAME) exec $(OPTION_T) $(PHP_USER) symfony bash -c "$(cmd)"
endif

exec:
ifeq ($(INSIDE_DOCKER_CONTAINER), 1)
	@$$cmd
else
	@HOST_UID=$(HOST_UID) HOST_GID=$(HOST_GID) WEB_PORT_HTTP=$(WEB_PORT_HTTP) WEB_PORT_SSL=$(WEB_PORT_SSL) POSTGRES_PASSWORD=$(POSTGRES_PASSWORD) POSTGRES_PORT=$(POSTGRES_PORT) docker compose $(PROJECT_NAME) exec $(OPTION_T) $(PHP_USER) symfony $$cmd
endif