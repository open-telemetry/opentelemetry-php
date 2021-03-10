DC_RUN_PHP = docker-compose run --rm php

install:
	$(DC_RUN_PHP) composer install
update:
	$(DC_RUN_PHP) composer update
test:
	$(DC_RUN_PHP) php ./vendor/bin/phpunit --colors=always --coverage-text --testdox --coverage-clover coverage.clover
phan:
	$(DC_RUN_PHP) env PHAN_DISABLE_XDEBUG_WARN=1 php ./vendor/bin/phan
psalm:
	$(DC_RUN_PHP) php ./vendor/bin/psalm
psalm-info:
	$(DC_RUN_PHP) php ./vendor/bin/psalm --show-info=true
phpstan: 
	$(DC_RUN_PHP) php ./vendor/bin/phpstan analyse
trace examples: FORCE
	docker-compose up -d
	$(DC_RUN_PHP) php ./examples/AlwaysOnZipkinExample.php
	$(DC_RUN_PHP) php ./examples/AlwaysOffTraceExample.php
	$(DC_RUN_PHP) php ./examples/AlwaysOnJaegerExample.php
        # The following examples do not use the DC_RUN_PHP global because they need environment variables.
	docker-compose run -e NEW_RELIC_ENDPOINT -e NEW_RELIC_INSERT_KEY --rm php php ./examples/AlwaysOnNewrelicExample.php
	docker-compose run -e NEW_RELIC_ENDPOINT -e NEW_RELIC_INSERT_KEY --rm php php ./examples/AlwaysOnZipkinToNewrelicExample.php

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
	$(DC_RUN_PHP) php ./vendor/bin/php-cs-fixer fix
FORCE:
