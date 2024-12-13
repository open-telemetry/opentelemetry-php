--TEST--
Host detector with custom error handler and open_basedir
--DESCRIPTION--
An error handler is installed which converts PHP warnings to exceptions, and open_basedir
is configured such that /etc/machine-id and friends cannot be read
--SKIPIF--
<?php if (!in_array(PHP_OS_FAMILY, ['Linux', 'BSD'])) die('skip requires Linux or BSD'); ?>
--ENV--
OTEL_PHP_FIBERS_ENABLED=0
--INI--
open_basedir=${PWD}
--FILE--
<?php
use OpenTelemetry\SDK\Resource\Detectors\Host;

require_once 'vendor/autoload.php';

function warningToException($errno, $errstr, $errfile, $errline)
{
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
}
set_error_handler('warningToException');

$detector = new Host();
$resource = $detector->getResource();
var_dump($resource->getAttributes()->toArray());
?>
--EXPECTF--
array(2) {
  ["host.name"]=>
  string(%d) "%s"
  ["host.arch"]=>
  string(%d) "%s"
}
