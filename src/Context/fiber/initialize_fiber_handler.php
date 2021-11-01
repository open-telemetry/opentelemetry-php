<?php

/** @noinspection PhpElementIsNotAvailableInCurrentPhpVersionInspection */
/** @phan-file-suppress PhanUndeclaredClassReference */

declare(strict_types=1);

namespace OpenTelemetry\Context;

use Fiber;

if (!class_exists(Fiber::class)) {
    return;
}

if (require __DIR__ . '/zend_observer_fiber.php') {
    // ffi fiber support enabled
} else {
    Context::setStorage(new FiberNotSupportedContextStorage(Context::storage()));
}
