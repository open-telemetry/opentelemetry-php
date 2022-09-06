PHP_VERSION ?= 7.4
DC_RUN_PHP = docker-compose run --rm php

.DEFAULT_GOAL : help

help: ## Show this help
	@printf "\033[33m%s:\033[0m\n" 'Available commands'
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z0-9_-]+:.*?## / {printf "  \033[32m%-18s\033[0m %s\n", $$1, $$2}' $(MAKEFILE_LIST)
all: update rector style deptrac packages-composer phan psalm phpstan test ## Run all tests, linting, checks
pull: ## Pull latest developer image
	docker-compose pull php
install: ## Install dependencies
	$(DC_RUN_PHP) env XDEBUG_MODE=off composer install
update: ## Update dependencies
	$(DC_RUN_PHP) env XDEBUG_MODE=off composer update
test: test-unit test-integration ## Run unit and integration tests
test-unit: ## Run unit tests
	$(DC_RUN_PHP) env XDEBUG_MODE=coverage vendor/bin/phpunit --testsuite unit --colors=always --coverage-text --testdox --coverage-clover coverage.clover --coverage-html=tests/coverage/html --log-junit=junit.xml
test-integration: ## Run integration tests
	$(DC_RUN_PHP) env XDEBUG_MODE=off vendor/bin/phpunit --testsuite integration --colors=always
test-coverage: ## Run units tests and generate code coverage
	$(DC_RUN_PHP) env XDEBUG_MODE=coverage vendor/bin/phpunit --testsuite unit --coverage-html=tests/coverage/html
test-compliance: ## Run compliance tests
	$(DC_RUN_PHP) env XDEBUG_MODE=coverage vendor/bin/phpunit --group compliance
test-trace-compliance: ## Run trace compliance tests
	$(DC_RUN_PHP) env XDEBUG_MODE=coverage vendor/bin/phpunit --group trace-compliance
phan: ## Run phan
	$(DC_RUN_PHP) env XDEBUG_MODE=off env PHAN_DISABLE_XDEBUG_WARN=1 vendor/bin/phan
psalm: ## Run psalm
	$(DC_RUN_PHP) env XDEBUG_MODE=off vendor/bin/psalm --threads=1 --no-cache
psalm-info: ## Run psalm and show info
	$(DC_RUN_PHP) env XDEBUG_MODE=off vendor/bin/psalm --show-info=true --threads=1
phpstan: ## Run phpstan
	$(DC_RUN_PHP) env XDEBUG_MODE=off vendor/bin/phpstan analyse --memory-limit=256M
packages-composer: ## Validate composer packages
	$(DC_RUN_PHP) env XDEBUG_MODE=off vendor/bin/otel packages:composer:validate
benchmark: ## Run phpbench
	$(DC_RUN_PHP) env XDEBUG_MODE=off vendor/bin/phpbench run --report=default
phpmetrics: ## Run php metrics
	$(DC_RUN_PHP) env XDEBUG_MODE=off vendor/bin/phpmetrics --config=./phpmetrics.json --junit=junit.xml
smoke-test-examples: smoke-test-isolated-examples smoke-test-exporter-examples smoke-test-collector-integration smoke-test-prometheus-example ## Run smoke test examples
smoke-test-isolated-examples: ## Run smoke test isolated examples
	$(DC_RUN_PHP) php ./examples/traces/getting_started.php
	$(DC_RUN_PHP) php ./examples/traces/features/always_off_trace_example.php
	$(DC_RUN_PHP) php ./examples/traces/features/batch_exporting.php
	$(DC_RUN_PHP) php ./examples/traces/features/concurrent_spans.php
	$(DC_RUN_PHP) php ./examples/traces/features/configuration_from_environment.php
	$(DC_RUN_PHP) php ./examples/traces/features/creating_a_new_trace_in_the_same_process.php
	$(DC_RUN_PHP) php ./examples/traces/features/parent_span_example.php
	$(DC_RUN_PHP) php ./examples/traces/features/resource_detectors.php
	$(DC_RUN_PHP) php ./examples/traces/features/span_resources.php
	$(DC_RUN_PHP) php ./examples/traces/troubleshooting/air_gapped_trace_debugging.php
	$(DC_RUN_PHP) php ./examples/traces/troubleshooting/logging_of_span_data.php
	$(DC_RUN_PHP) php ./examples/traces/troubleshooting/setting_up_logging.php
smoke-test-exporter-examples: FORCE ## Run (some) exporter smoke test examples
# Note this does not include every exporter at the moment
	docker-compose up -d --remove-orphans
	$(DC_RUN_PHP) php ./examples/traces/features/exporters/zipkin.php
	$(DC_RUN_PHP) php ./examples/traces/features/always_off_trace_example.php
	$(DC_RUN_PHP) php ./examples/traces/features/exporters/jaeger.php
# The following examples do not use the DC_RUN_PHP global because they need environment variables.
	docker-compose run -e NEW_RELIC_ENDPOINT -e NEW_RELIC_INSERT_KEY --rm php php ./examples/traces/features/exporters/newrelic.php
	docker-compose run -e NEW_RELIC_ENDPOINT -e NEW_RELIC_INSERT_KEY --rm php php ./examples/traces/features/exporters/zipkin_to_newrelic.php
	docker-compose stop
smoke-test-collector-integration: ## Run smoke test collector integration
	docker-compose -f docker-compose.collector.yaml up -d --remove-orphans
	sleep 5
# This is slow because it's building the image from scratch (and parts of that, like installing the gRPC extension, are slow)
# This can be sped up by switching to the pre-built images hosted on ghcr.io (and referenced in other docker-compose**.yaml files)
	docker-compose -f docker-compose.collector.yaml run --rm php php ./examples/traces/features/exporters/otlp_grpc.php
	docker-compose -f docker-compose.collector.yaml run --rm php php ./examples/traces/features/exporters/otlp.php
	docker-compose -f docker-compose.collector.yaml stop
smoke-test-collector-metrics-integration:
	docker-compose -f docker-compose.collector.yaml up -d --force-recreate collector
	COMPOSE_IGNORE_ORPHANS=TRUE docker-compose -f docker-compose.yaml run --rm php php ./examples/metrics/features/exporters/otlp.php
	docker-compose -f docker-compose.collector.yaml logs collector
	docker-compose -f docker-compose.collector.yaml stop collector
smoke-test-prometheus-example: metrics-prometheus-example stop-prometheus
metrics-prometheus-example:
	@docker-compose -f docker-compose.prometheus.yaml -p opentelemetry-php_metrics-prometheus-example up -d web
# This is slow because it's building the image from scratch (and parts of that, like installing the gRPC extension, are slow)
	@docker-compose -f docker-compose.prometheus.yaml -p opentelemetry-php_metrics-prometheus-example run --rm php php examples/metrics/prometheus/prometheus_metrics_example.php
stop-prometheus:
	@docker-compose -f docker-compose.prometheus.yaml -p opentelemetry-php_metrics-prometheus-example stop
fiber-ffi-example:
	@docker-compose -f docker-compose.fiber-ffi.yaml -p opentelemetry-php_fiber-ffi-example up -d web
protobuf: ## Generate protobuf files
	./script/proto_gen.sh
thrift: ## Generate thrift files
	./script/thrift_gen.sh
bash: ## bash shell into container
	$(DC_RUN_PHP) bash
style: ## Run style check/fix
	$(DC_RUN_PHP) env XDEBUG_MODE=off vendor/bin/php-cs-fixer fix --config=.php-cs-fixer.php --using-cache=no -vvv
rector: ## Run rector
	$(DC_RUN_PHP) env XDEBUG_MODE=off vendor/bin/rector process src
rector-dry: ## Run rector (dry-run)
	$(DC_RUN_PHP) env XDEBUG_MODE=off vendor/bin/rector process src --dry-run
deptrac: ## Run deptrac
	$(DC_RUN_PHP) env XDEBUG_MODE=off vendor/bin/deptrac --formatter=table --report-uncovered --no-cache
w3c-test-service:
	@docker-compose -f docker-compose.w3cTraceContext.yaml run --rm php ./tests/TraceContext/W3CTestService/trace-context-test.sh
semconv: ## Generate semconv files
	./script/semantic-conventions/semconv.sh
split: ## Run git split
	docker-compose -f docker/gitsplit/docker-compose.yaml --env-file ./.env up
FORCE:
