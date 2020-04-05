DC_RUN_PHP = docker-compose run --rm php

install:
	$(DC_RUN_PHP) composer install
update:
	$(DC_RUN_PHP) composer update
test:
	$(DC_RUN_PHP) php ./vendor/bin/phpunit --colors=always --coverage-text
phan:
	$(DC_RUN_PHP) env PHAN_DISABLE_XDEBUG_WARN=1 php ./vendor/bin/phan
examples: FORCE
	docker-compose up -d
	$(DC_RUN_PHP) php ./examples/AlwaysOnTraceExample.php
	$(DC_RUN_PHP) php ./examples/AlwaysOffTraceExample.php
	$(DC_RUN_PHP) php ./examples/JaegerExporterExample.php
bash:
	$(DC_RUN_PHP) bash
style:
	$(DC_RUN_PHP) php ./vendor/bin/php-cs-fixer fix
FORCE:
