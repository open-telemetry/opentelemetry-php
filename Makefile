PHP_VERSION ?= 7.4
DC_RUN_PHP = docker-compose run --rm php

all: update style deptrac phan psalm phpstan test
install:
	$(DC_RUN_PHP) env XDEBUG_MODE=off composer install
update:
	$(DC_RUN_PHP) env XDEBUG_MODE=off composer update
test: test-unit test-integration
test-unit:
	$(DC_RUN_PHP) env XDEBUG_MODE=coverage vendor/bin/phpunit --testsuite unit --colors=always --coverage-text --testdox --coverage-clover coverage.clover --coverage-html=tests/coverage/html --log-junit=junit.xml
test-integration:
	$(DC_RUN_PHP) env XDEBUG_MODE=off vendor/bin/phpunit --testsuite integration --colors=always
test-coverage:
	$(DC_RUN_PHP) env XDEBUG_MODE=coverage vendor/bin/phpunit --testsuite unit --coverage-html=tests/coverage/html
test-compliance:
	$(DC_RUN_PHP) env XDEBUG_MODE=coverage vendor/bin/phpunit --group compliance
test-trace-compliance:
	$(DC_RUN_PHP) env XDEBUG_MODE=coverage vendor/bin/phpunit --group trace-compliance
phan:
	$(DC_RUN_PHP) env XDEBUG_MODE=off env PHAN_DISABLE_XDEBUG_WARN=1 vendor/bin/phan
psalm:
	$(DC_RUN_PHP) env XDEBUG_MODE=off vendor/bin/psalm --threads=1 --no-cache
psalm-info:
	$(DC_RUN_PHP) env XDEBUG_MODE=off vendor/bin/psalm --show-info=true --threads=1
phpstan:
	$(DC_RUN_PHP) env XDEBUG_MODE=off vendor/bin/phpstan analyse --memory-limit=256M
packages-composer:
	$(DC_RUN_PHP) env XDEBUG_MODE=off vendor/bin/otel packages:composer:validate
benchmark:
	$(DC_RUN_PHP) env XDEBUG_MODE=off vendor/bin/phpbench run --report=default
phpmetrics:
	$(DC_RUN_PHP) env XDEBUG_MODE=off vendor/bin/phpmetrics --config=./phpmetrics.json --junit=junit.xml
trace examples: FORCE
	docker-compose up -d --remove-orphans
	$(DC_RUN_PHP) php ./examples/AlwaysOnZipkinExample.php
	$(DC_RUN_PHP) php ./examples/AlwaysOffTraceExample.php
	$(DC_RUN_PHP) php ./examples/AlwaysOnJaegerExample.php
        # The following examples do not use the DC_RUN_PHP global because they need environment variables.
	docker-compose run -e NEW_RELIC_ENDPOINT -e NEW_RELIC_INSERT_KEY --rm php php ./examples/AlwaysOnNewrelicExample.php
	docker-compose run -e NEW_RELIC_ENDPOINT -e NEW_RELIC_INSERT_KEY --rm php php ./examples/AlwaysOnZipkinToNewrelicExample.php
	docker-compose stop
collector:
	docker-compose -f docker-compose.collector.yaml up -d --remove-orphans
	docker-compose -f docker-compose.collector.yaml run -e OTEL_EXPORTER_OTLP_ENDPOINT=collector:4317 --rm php php ./examples/AlwaysOnOTLPGrpcExample.php
	docker-compose -f docker-compose.collector.yaml stop

fiber-ffi-example:
	@docker-compose -f docker-compose.fiber-ffi.yaml -p opentelemetry-php_fiber-ffi-example up -d web
metrics-prometheus-example:
	@docker-compose -f docker-compose.prometheus.yaml -p opentelemetry-php_metrics-prometheus-example up -d web
	@docker-compose -f docker-compose.prometheus.yaml -p opentelemetry-php_metrics-prometheus-example run --rm php php examples/prometheus/PrometheusMetricsExample.php
stop-prometheus:
	@docker-compose -f docker-compose.prometheus.yaml -p opentelemetry-php_metrics-prometheus-example stop
protobuf:
	./script/proto_gen.sh
thrift:
	./script/thrift_gen.sh
bash:
	$(DC_RUN_PHP) bash
style:
	$(DC_RUN_PHP) env XDEBUG_MODE=off vendor/bin/php-cs-fixer fix --config=.php-cs-fixer.php --using-cache=no -vvv
deptrac:
	$(DC_RUN_PHP) env XDEBUG_MODE=off vendor/bin/deptrac --formatter=table --report-uncovered
w3c-test-service:
	@docker-compose -f docker-compose.w3cTraceContext.yaml run --rm php ./tests/TraceContext/W3CTestService/symfony-setup
semconv:
	./script/semantic-conventions/semconv.sh
split:
	docker-compose -f docker/gitsplit/docker-compose.yaml --env-file ./.env up
FORCE:
