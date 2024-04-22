--TEST--
Context usage in fiber without fiber support does not trigger warning if context is attached before usage.
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

$fiber = new Fiber(bindContext(function() use ($key) {
    echo 'fiber(pre):' . Context::getCurrent()->get($key), PHP_EOL;

    $scope = Context::getCurrent()
        ->with($key, 'fiber')
        ->activate();

    echo 'fiber:' . Context::getCurrent()->get($key), PHP_EOL;

    Fiber::suspend();
    echo 'fiber:' . Context::getCurrent()->get($key), PHP_EOL;

    $scope->detach();

    echo 'fiber(post):' . Context::getCurrent()->get($key), PHP_EOL;
}));

echo 'main:' . Context::getCurrent()->get($key), PHP_EOL;

$fiber->start();
echo 'main:' . Context::getCurrent()->get($key), PHP_EOL;

$fiber->resume();
echo 'main:' . Context::getCurrent()->get($key), PHP_EOL;

$scope->detach();


// see https://github.com/opentelemetry-php/context?tab=readme-ov-file#event-loops
function bindContext(Closure $closure): Closure {
    $context = Context::getCurrent();
    return static function (mixed ...$args) use ($closure, $context): mixed {
        $scope = $context->activate();
        try {
            return $closure(...$args);
        } finally {
            $scope->detach();
        }
    };
}

?>
--EXPECTF--
main:main
fiber(pre):main
fiber:fiber
main:main
fiber:fiber
fiber(post):main
main:main
