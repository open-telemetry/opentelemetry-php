DC_RUN_PHP = docker-compose run php

install:
	$(DC_RUN_PHP) composer install
update:
	$(DC_RUN_PHP) composer update
test:
	$(DC_RUN_PHP) php ./vendor/bin/phpunit --colors=always
phan:
	$(DC_RUN_PHP) php ./vendor/bin/phan
examples: FORCE
	$(DC_RUN_PHP) php ./examples/AlwaysOnTraceExample.php
bash:
	$(DC_RUN_PHP) bash
FORCE: