--TEST--
Fiber handler has to be loaded before fibers are used.
--SKIPIF--
<?php if (PHP_VERSION_ID < 80100 || !extension_loaded('ffi')) die('skip requires PHP8.1 and FFI'); ?>
--FILE--
<?php
use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\ContextKey;

require_once 'vendor/autoload.php';

$key = new ContextKey();

$fiber = new Fiber(function() use ($key) {
    $scope = Context::getCurrent()
        ->with($key, 'fiber')
        ->activate();

    echo 'fiber:' . Context::getCurrent()->get($key), PHP_EOL;

    Fiber::suspend();
    echo 'fiber:' . Context::getCurrent()->get($key), PHP_EOL;

    $scope->detach();
});

$fiber->start();
echo 'main:' . Context::getCurrent()->get($key), PHP_EOL;

$scope = Context::getCurrent()
    ->with($key, 'main')
    ->activate();

$fiber->resume();
echo 'main:' . Context::getCurrent()->get($key), PHP_EOL;

$scope->detach();

?>
--EXPECT--
fiber:fiber
main:
fiber:fiber
main:main
