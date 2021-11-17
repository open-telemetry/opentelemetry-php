DC_RUN_PHP = docker-compose run --rm php

install:
	$(DC_RUN_PHP) composer install
update:
	$(DC_RUN_PHP) composer update
test:
	$(DC_RUN_PHP) php ./vendor/bin/phpunit --colors=always --coverage-text --testdox --coverage-clover coverage.clover
test-coverage:
	$(DC_RUN_PHP) php ./vendor/bin/phpunit --colors=always --testdox --coverage-html=tests/coverage/html
phan:
	$(DC_RUN_PHP) env PHAN_DISABLE_XDEBUG_WARN=1 php ./vendor/bin/phan
psalm:
	$(DC_RUN_PHP) php ./vendor/bin/psalm --threads=1
psalm-info:
	$(DC_RUN_PHP) php ./vendor/bin/psalm --show-info=true --threads=1
phpstan:
	$(DC_RUN_PHP) php ./vendor/bin/phpstan analyse
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
	docker-compose -f docker-compose-collector.yaml up -d --remove-orphans
	docker-compose -f docker-compose-collector.yaml run -e OTEL_EXPORTER_OTLP_ENDPOINT=otel-collector:4317 --rm php php ./examples/AlwaysOnOTLPGrpcExample2.php
	docker-compose -f docker-compose-collector.yaml stop

metrics-prometheus-example:
	@docker-compose -f docker-compose.prometheus.yaml up -d web
	@docker-compose -f docker-compose.prometheus.yaml run php-prometheus php /var/www/public/examples/prometheus/PrometheusMetricsExample.php
stop-prometheus:
	@docker-compose -f docker-compose.prometheus.yaml stop
proto:
	@docker-compose -f docker-compose.proto.yaml up proto
bash:
	$(DC_RUN_PHP) bash
style:
	$(DC_RUN_PHP) php ./vendor/bin/php-cs-fixer fix --config=.php-cs-fixer.php --using-cache=no -vvv
deptrac:
	$(DC_RUN_PHP) vendor/bin/deptrac --formatter=table
w3c-test-service:
	@docker-compose -f docker-compose.w3cTraceContext.yaml run --rm php ./tests/TraceContext/W3CTestService/symfony-setup
FORCE:
