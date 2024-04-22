--TEST--
Context usage in fiber without fiber support triggers warning.
--SKIPIF--
<?php if (!class_exists(Fiber::class)) die('skip requires fibers'); ?>
--ENV--
OTEL_PHP_FIBERS_ENABLED=false
--FILE--
<?php
use OpenTelemetry\Context\Context;

require_once 'vendor/autoload.php';

$key = Context::createKey('-');
$scope = Context::getCurrent()
    ->with($key, 'main')
    ->activate();

$fiber = new Fiber(function() use ($key) {
    echo 'fiber(pre):' . Context::getCurrent()->get($key), PHP_EOL;

    $scope = Context::getCurrent()
        ->with($key, 'fiber')
        ->activate();

    echo 'fiber:' . Context::getCurrent()->get($key), PHP_EOL;

    Fiber::suspend();
    echo 'fiber:' . Context::getCurrent()->get($key), PHP_EOL;

    $scope->detach();

    echo 'fiber(post):' . Context::getCurrent()->get($key), PHP_EOL;
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

Warning: Access to not initialized OpenTelemetry context in fiber (id: %d), automatic forking not supported, must attach initial fiber context manually %s
fiber(pre):main

Warning: Access to not initialized OpenTelemetry context in fiber (id: %d), automatic forking not supported, must attach initial fiber context manually %s
fiber:fiber
main:main
fiber:fiber

Warning: Access to not initialized OpenTelemetry context in fiber (id: %d), automatic forking not supported, must attach initial fiber context manually %s
fiber(post):main
main:main
