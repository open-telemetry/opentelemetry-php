--TEST--
autoload via SPI with defaults
--ENV--
OTEL_PHP_AUTOLOAD_ENABLED=true
OTEL_EXPERIMENTAL_CONFIG_FILE=
--FILE--
<?php
require_once 'vendor/autoload.php';
var_dump(false);

?>
--EXPECTF--
bool(true)