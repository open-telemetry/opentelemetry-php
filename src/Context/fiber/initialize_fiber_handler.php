<?php

/** @noinspection PhpElementIsNotAvailableInCurrentPhpVersionInspection */

declare(strict_types=1);

use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\FiberNotSupportedContextStorage;

if (!class_exists(Fiber::class)) {
    return;
}

if (require __DIR__ . '/zend_observer_fiber.php') {
    // ffi fiber support enabled
} else {
    Context::setStorage(new FiberNotSupportedContextStorage(Context::storage()));
}
