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

load-prod-env:
	$(eval include .env.prod)

build-start-dev: ## Build dev environment
ifeq ($(INSIDE_DOCKER_CONTAINER), 0)
	@HOST_UID=$(HOST_UID) HOST_GID=$(HOST_GID) WEB_PORT_HTTP=$(WEB_PORT_HTTP) WEB_PORT_SSL=$(WEB_PORT_SSL) POSTGRES_PASSWORD=$(POSTGRES_PASSWORD) POSTGRES_PORT=$(POSTGRES_PORT) docker compose -f compose.yaml build
	@HOST_UID=$(HOST_UID) HOST_GID=$(HOST_GID) WEB_PORT_HTTP=$(WEB_PORT_HTTP) WEB_PORT_SSL=$(WEB_PORT_SSL) POSTGRES_PASSWORD=$(POSTGRES_PASSWORD) POSTGRES_PORT=$(POSTGRES_PORT) docker compose -f compose.yaml $(PROJECT_NAME) up -d
	@make exec-bash cmd="bash ./docker/scripts/wait_for_db.sh"
	@make exec-bash cmd="COMPOSER_MEMORY_LIMIT=-1 composer install --optimize-autoloader"
	@make exec cmd="php bin/console doctrine:database:create --if-not-exists --env=dev --no-interaction"
	@make initialize-program
else
	$(ERROR_ONLY_FOR_HOST)
endif

start-dev: ## Start dev environment
ifeq ($(INSIDE_DOCKER_CONTAINER), 0)
	@HOST_UID=$(HOST_UID) HOST_GID=$(HOST_GID) WEB_PORT_HTTP=$(WEB_PORT_HTTP) WEB_PORT_SSL=$(WEB_PORT_SSL) POSTGRES_PASSWORD=$(POSTGRES_PASSWORD) POSTGRES_PORT=$(POSTGRES_PORT) docker compose -f compose.yaml $(PROJECT_NAME) up -d
	@make exec-bash cmd="bash ./docker/scripts/wait_for_db.sh"
	@make exec-bash cmd="COMPOSER_MEMORY_LIMIT=-1 composer install --optimize-autoloader"
else
	$(ERROR_ONLY_FOR_HOST)
endif

build-start-prod: load-prod-env ## Build prod environment
ifeq ($(INSIDE_DOCKER_CONTAINER), 0)
	@HOST_UID=$(HOST_UID) HOST_GID=$(HOST_GID) WEB_PORT_HTTP=$(WEB_PORT_HTTP) WEB_PORT_SSL=$(WEB_PORT_SSL) POSTGRES_PASSWORD=$(POSTGRES_PASSWORD) POSTGRES_PORT=$(POSTGRES_PORT) docker compose -f compose-prod.yaml build
	@HOST_UID=$(HOST_UID) HOST_GID=$(HOST_GID) WEB_PORT_HTTP=$(WEB_PORT_HTTP) WEB_PORT_SSL=$(WEB_PORT_SSL) POSTGRES_PASSWORD=$(POSTGRES_PASSWORD) POSTGRES_PORT=$(POSTGRES_PORT) docker compose -f compose-prod.yaml $(PROJECT_NAME) up -d
	@make exec-bash cmd="bash ./docker/scripts/wait_for_db.sh"
	@make exec cmd="php bin/console doctrine:database:create --if-not-exists --env=prod --no-interaction"
	@make initialize-program
else
	$(ERROR_ONLY_FOR_HOST)
endif

start-prod: load-prod-env ## Start prod environment
ifeq ($(INSIDE_DOCKER_CONTAINER), 0)
	@HOST_UID=$(HOST_UID) HOST_GID=$(HOST_GID) WEB_PORT_HTTP=$(WEB_PORT_HTTP) WEB_PORT_SSL=$(WEB_PORT_SSL) POSTGRES_PASSWORD=$(POSTGRES_PASSWORD) POSTGRES_PORT=$(POSTGRES_PORT) docker compose -f compose-prod.yaml $(PROJECT_NAME) up -d
	@make exec-bash cmd="bash ./docker/scripts/wait_for_db.sh"
else
	$(ERROR_ONLY_FOR_HOST)
endif

stop-dev: ## Stop dev environment containers
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

down-dev: ## Stop and remove dev environment containers, networks
ifeq ($(INSIDE_DOCKER_CONTAINER), 0)
	@HOST_UID=$(HOST_UID) HOST_GID=$(HOST_GID) WEB_PORT_HTTP=$(WEB_PORT_HTTP) WEB_PORT_SSL=$(WEB_PORT_SSL) POSTGRES_PASSWORD=$(POSTGRES_PASSWORD) POSTGRES_PORT=$(POSTGRES_PORT) docker compose -f compose.yaml $(PROJECT_NAME) down
else
	$(ERROR_ONLY_FOR_HOST)
endif

down-prod: ## Stop and remove prod environment containers, networks
ifeq ($(INSIDE_DOCKER_CONTAINER), 0)
	@HOST_UID=$(HOST_UID) HOST_GID=$(HOST_GID) WEB_PORT_HTTP=$(WEB_PORT_HTTP) WEB_PORT_SSL=$(WEB_PORT_SSL) POSTGRES_PASSWORD=$(POSTGRES_PASSWORD) POSTGRES_PORT=$(POSTGRES_PORT) docker compose -f compose-prod.yaml $(PROJECT_NAME) down
else
	$(ERROR_ONLY_FOR_HOST)
endif

restart-dev: stop start ## Stop and start dev environment
restart-prod: stop-prod start-prod ## Stop and start prod environment

initialize-program: ## Initialize program by running required commands
	@make exec cmd="php bin/console lexik:jwt:generate-keypair --skip-if-exists"
	@make migrate
	@make exec cmd="php bin/console asset-map:compile"

migrate: ## Runs all migrations for main/test databases
	@make exec cmd="php bin/console doctrine:migrations:migrate --no-interaction"
## @make exec cmd="php bin/console doctrine:migrations:migrate --no-interaction --env=test"

test:
	@make exec cmd="php bin/phpunit"

fixtures-dev:
	@make exec cmd="php bin/console doctrine:fixtures:load --group=dev --no-interaction"

phpstan: ## Runs PhpStan static analysis tool
ifeq ($(INSIDE_DOCKER_CONTAINER), 1)
	@echo "\033[32mRunning PHPStan - PHP Static Analysis Tool\033[39m"
	@make exec cmd="php bin/console cache:clear --env=test"
	@make exec cmd="php ./vendor/bin/phpstan --version"
	@make exec cmd="php ./vendor/bin/phpstan analyze src tests"
else
	@make exec cmd="make phpstan"
endif

phpcs: ## Runs PHP CodeSniffer
	@make exec-bash cmd="./vendor/bin/phpcs --version && ./vendor/bin/phpcs --standard=PSR12 --colors -p src tests"

phpcs-fix:
	@make exec-bash cmd="./vendor/bin/php-cs-fixer fix src tests"

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
