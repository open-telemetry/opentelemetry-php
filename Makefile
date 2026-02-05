include .env

PHP_VERSION ?= 8.1
DOCKER_COMPOSE ?= docker compose
DC_RUN_PHP = $(DOCKER_COMPOSE) run --rm php

.DEFAULT_GOAL : help
.PHONY: deptrac

help: ## Show this help
	@printf "\033[33m%s:\033[0m\n" 'Available commands'
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z0-9_-]+:.*?## / {printf "  \033[32m%-18s\033[0m %s\n", $$1, $$2}' $(MAKEFILE_LIST)
all: update all-checks ## Update to latest and run all checks
all-lowest: update-lowest all-checks ## Update to lowest dependencies and run all checks
all-checks: rector style deptrac packages-composer phan psalm phpstan test spi ## Run all checks
pull: ## Pull latest developer image
	$(DOCKER_COMPOSE) pull php
build: ## Build developer image locally
	docker build docker/ --build-arg PHP_VERSION=${PHP_VERSION} -t ghcr.io/open-telemetry/opentelemetry-php/opentelemetry-php-base:${PHP_VERSION}
install: ## Install dependencies
	$(DC_RUN_PHP) env XDEBUG_MODE=off composer install
update: ## Update dependencies
	$(DC_RUN_PHP) env XDEBUG_MODE=off composer update
update-lowest: ## Update dependencies to lowest supported versions
	$(DC_RUN_PHP) env XDEBUG_MODE=off composer update --prefer-lowest
test: test-unit test-integration ## Run unit and integration tests
test-unit: ## Run unit tests
	$(DC_RUN_PHP) env XDEBUG_MODE=coverage vendor/bin/phpunit --testsuite unit --colors=always
test-integration: ## Run integration tests
	$(DC_RUN_PHP) env XDEBUG_MODE=off vendor/bin/phpunit --testsuite integration --colors=always
test-verbose: ## Run unit tests with verbose (testdox) output
	$(DC_RUN_PHP) env XDEBUG_MODE=coverage vendor/bin/phpunit --testsuite unit  --colors=always --testdox --coverage-clover coverage.clover --coverage-html=tests/coverage/html --log-junit=junit.xml
test-coverage: ## Run units tests and generate code coverage
	$(DC_RUN_PHP) env XDEBUG_MODE=coverage vendor/bin/phpunit --testsuite unit --coverage-html=tests/coverage/html
test-compliance: ## Run compliance tests
	$(DC_RUN_PHP) env XDEBUG_MODE=coverage vendor/bin/phpunit --group compliance
test-trace-compliance: ## Run trace compliance tests
	$(DC_RUN_PHP) env XDEBUG_MODE=coverage vendor/bin/phpunit --group trace-compliance
phan: ## Run phan
	$(DC_RUN_PHP) env XDEBUG_MODE=off env PHAN_DISABLE_XDEBUG_WARN=1 vendor-bin/phan/vendor/bin/phan
psalm: ## Run psalm
	$(DC_RUN_PHP) env XDEBUG_MODE=off vendor-bin/psalm/vendor/bin/psalm --threads=1 --no-cache
psalm-info: ## Run psalm and show info
	$(DC_RUN_PHP) env XDEBUG_MODE=off vendor-bin/psalm/vendor/bin/psalm --show-info=true --threads=1
phpdoc: ## Run phpdoc
	$(DOCKER_COMPOSE) -f docker-compose.phpDocumentor.yaml run --rm phpdoc
phpdoc-preview:
	$(DOCKER_COMPOSE) -f docker-compose.phpDocumentor.yaml run --service-ports --rm preview
phpstan: ## Run phpstan
	$(DC_RUN_PHP) env XDEBUG_MODE=off vendor/bin/phpstan analyse --memory-limit=256M
infection: ## Run infection (mutation testing)
	$(DC_RUN_PHP) env XDEBUG_MODE=coverage php -d memory_limit=1024M vendor-bin/infection/vendor/bin/infection --threads=max
packages-composer: ## Validate composer packages
	$(DC_RUN_PHP) env XDEBUG_MODE=off vendor/bin/otel packages:composer:validate
benchmark: ## Run phpbench
	$(DC_RUN_PHP) env XDEBUG_MODE=off vendor-bin/phpbench/vendor/bin/phpbench run --report=default
phpmetrics: ## Run php metrics
	$(DC_RUN_PHP) env XDEBUG_MODE=off vendor-bin/phpmetrics/vendor/bin/phpmetrics --config=./phpmetrics.json --junit=junit.xml
spi: ## Validate SPI package dependencies
	$(DC_RUN_PHP) composer spi:show-outdated-package-dependencies
smoke-test-examples: smoke-test-isolated-examples smoke-test-exporter-examples smoke-test-collector-integration smoke-test-prometheus-example ## Run smoke test examples
smoke-test-isolated-examples: ## Run smoke test isolated examples
	$(DC_RUN_PHP) php ./examples/traces/getting_started.php
	$(DC_RUN_PHP) php ./examples/traces/features/batch_exporting.php
	$(DC_RUN_PHP) php ./examples/traces/features/concurrent_spans.php
	$(DC_RUN_PHP) php ./examples/traces/features/configuration_from_environment.php
	$(DC_RUN_PHP) php ./examples/traces/features/creating_a_new_trace_in_the_same_process.php
	$(DC_RUN_PHP) php ./examples/traces/features/resource_detectors.php
	$(DC_RUN_PHP) php ./examples/traces/features/span_resources.php
	$(DC_RUN_PHP) php ./examples/traces/troubleshooting/air_gapped_trace_debugging.php
	$(DC_RUN_PHP) php ./examples/traces/troubleshooting/logging_of_span_data.php
	$(DC_RUN_PHP) php ./examples/traces/troubleshooting/setting_up_logging.php
smoke-test-exporter-examples: FORCE ## Run (some) exporter smoke test examples
# Note this does not include every exporter at the moment
	$(DOCKER_COMPOSE) up -d --remove-orphans
	$(DC_RUN_PHP) php ./examples/traces/exporters/zipkin.php
	$(DC_RUN_PHP) php ./examples/traces/features/parent_span_example.php
smoke-test-collector-integration: ## Run smoke test collector integration
	$(DOCKER_COMPOSE) -f docker-compose.collector.yaml up -d --remove-orphans
	sleep 5
	$(DOCKER_COMPOSE) -f docker-compose.collector.yaml run --rm php php ./examples/traces/exporters/otlp_grpc.php
	$(DOCKER_COMPOSE) -f docker-compose.collector.yaml run --rm php php ./examples/traces/exporters/otlp_http.php
	$(DOCKER_COMPOSE) -f docker-compose.collector.yaml stop
smoke-test-collector-metrics-integration:
	$(DOCKER_COMPOSE) -f docker-compose.collector.yaml up -d --force-recreate collector
	COMPOSE_IGNORE_ORPHANS=TRUE $(DOCKER_COMPOSE) -f docker-compose.yaml run --rm php php ./examples/metrics/features/exporters/otlp_http.php
	$(DOCKER_COMPOSE) -f docker-compose.collector.yaml logs collector
	$(DOCKER_COMPOSE) -f docker-compose.collector.yaml stop collector
smoke-test-prometheus-example: metrics-prometheus-example stop-prometheus
metrics-prometheus-example:
	@$(DOCKER_COMPOSE) -f docker-compose.prometheus.yaml -p opentelemetry-php_metrics-prometheus-example up -d web
# This is slow because it's building the image from scratch (and parts of that, like installing the gRPC extension, are slow)
	@$(DOCKER_COMPOSE) -f docker-compose.prometheus.yaml -p opentelemetry-php_metrics-prometheus-example run --rm php php examples/metrics/prometheus/prometheus_metrics_example.php
stop-prometheus:
	@$(DOCKER_COMPOSE) -f docker-compose.prometheus.yaml -p opentelemetry-php_metrics-prometheus-example stop
fiber-ffi-example:
	@$(DOCKER_COMPOSE) -f docker-compose.fiber-ffi.yaml -p opentelemetry-php_fiber-ffi-example up -d web
protobuf: ## Generate protobuf files
	./script/proto_gen.sh
bash: ## bash shell into container
	$(DC_RUN_PHP) bash
style: ## Run style check/fix
	$(DC_RUN_PHP) env XDEBUG_MODE=off env PHP_CS_FIXER_IGNORE_ENV=1 vendor-bin/php-cs-fixer/vendor/bin/php-cs-fixer fix --config=.php-cs-fixer.php --using-cache=no -vvv
rector-write: ## Run rector
	$(DC_RUN_PHP) env XDEBUG_MODE=off vendor-bin/rector/vendor/bin/rector process
rector: ## Run rector (dry-run)
	$(DC_RUN_PHP) env XDEBUG_MODE=off vendor-bin/rector/vendor/bin/rector process --dry-run
deptrac: ## Run deptrac
	$(DC_RUN_PHP) env XDEBUG_MODE=off vendor-bin/deptrac/vendor/bin/deptrac --formatter=table --report-uncovered --no-cache
w3c-test-service:
	@$(DOCKER_COMPOSE) -f docker-compose.w3cTraceContext.yaml run --rm php ./tests/TraceContext/W3CTestService/trace-context-test.sh
semconv: ## Generate semconv files
	./script/semantic-conventions/semconv.sh
split: ## Run git split
	$(DOCKER_COMPOSE) -f docker/gitsplit/docker-compose.yaml --env-file ./.env up
FORCE:
