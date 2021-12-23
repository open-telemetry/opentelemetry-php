<?php

/** @noinspection PhpElementIsNotAvailableInCurrentPhpVersionInspection */
/** @phan-file-suppress PhanUndeclaredClassReference */

declare(strict_types=1);

namespace OpenTelemetry\Context;

use Fiber;

if (!class_exists(Fiber::class)) {
    return;
}

$observer = new ZendObserverFiber();

if ($observer->isEnabled() && $observer->init()) {
    // ffi fiber support enabled
} else {
    Context::setStorage(new FiberNotSupportedContextStorage(Context::storage()));
}
