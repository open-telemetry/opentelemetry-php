<?php

/** @noinspection PhpElementIsNotAvailableInCurrentPhpVersionInspection */

declare(strict_types=1);

namespace OpenTelemetry\Context;

use function assert;
use function class_exists;
use const E_USER_WARNING;
use Fiber;
use function trigger_error;

/**
 * @internal
 *
 * @phan-file-suppress PhanUndeclaredClassReference
 * @phan-file-suppress PhanUndeclaredClassMethod
 */
final class FiberNotSupportedContextStorage implements ContextStorageInterface
{
    private ContextStorageInterface $storage;

    public function __construct(ContextStorageInterface $storage)
    {
        $this->storage = $storage;
    }

    public function fork(int $id): void
    {
        $this->storage->fork($id);
    }

    public function switch(int $id): void
    {
        $this->storage->switch($id);
    }

    public function destroy(int $id): void
    {
        $this->storage->destroy($id);
    }

    public function current(): Context
    {
        assert(class_exists(Fiber::class));
        if (Fiber::getCurrent()) {
            trigger_error('Fiber context switching not supported', E_USER_WARNING);
        }

        return $this->storage->current();
    }

    public function attach(Context $context): ScopeInterface
    {
        assert(class_exists(Fiber::class));
        if (Fiber::getCurrent()) {
            trigger_error('Fiber context switching not supported', E_USER_WARNING);
        }

        return $this->storage->attach($context);
    }
}
