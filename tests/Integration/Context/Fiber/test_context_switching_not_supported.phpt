--TEST--
Context usage in fiber without fiber support triggers warning.
--SKIPIF--
<?php if (!class_exists(Fiber::class)) die('skip requires fibers'); ?>
--ENV--
OTEL_PHP_FIBERS_ENABLED=0
--FILE--
<?php
use OpenTelemetry\Context\Context;

require_once 'vendor/autoload.php';

$key = Context::createKey('-');
$scope = Context::getCurrent()
    ->with($key, 'main')
    ->activate();

$fiber = new Fiber(function() use ($key) {
    $scope = Context::getCurrent()
        ->with($key, 'fiber')
        ->activate();

    echo 'fiber:' . Context::getCurrent()->get($key), PHP_EOL;

    Fiber::suspend();
    echo 'fiber:' . Context::getCurrent()->get($key), PHP_EOL;

    $scope->detach();
});

echo 'main:' . Context::getCurrent()->get($key), PHP_EOL;

$fiber->start();
echo 'main:' . Context::getCurrent()->get($key), PHP_EOL;

$fiber->resume();
echo 'main:' . Context::getCurrent()->get($key), PHP_EOL;

$scope->detach();

?>
--EXPECTF--
main:main

Warning: Fiber context switching not supported in %s
fiber:fiber

Warning: Fiber context switching not supported in %s
main:fiber
fiber:fiber
main:main
