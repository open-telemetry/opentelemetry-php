<?php

/** @noinspection PhpElementIsNotAvailableInCurrentPhpVersionInspection */

declare(strict_types=1);

namespace OpenTelemetry\Context\Swoole;

use Fiber;
use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\ContextStorageInterface;
use OpenTelemetry\Context\ContextStorageScopeInterface;
use OpenTelemetry\Context\ExecutionContextAwareInterface;
use OpenTelemetry\Context\FiberBoundContextStorage;
use function class_exists;

/**
 * @internal
 */
final class SwooleContextStorage implements ContextStorageInterface {

    private ContextStorageInterface $storage;
    private SwooleContextHandler $handler;

    /**
     * @param ContextStorageInterface&ExecutionContextAwareInterface $storage
     */
    public function __construct(ContextStorageInterface $storage) {
        $this->storage = class_exists(Fiber::class)
            ? new FiberBoundContextStorage($storage)
            : $storage;
        $this->handler = new SwooleContextHandler($storage);
    }

    public function scope(): ?ContextStorageScopeInterface {
        $this->handler->switchToActiveCoroutine();

        if (!$scope = $this->storage->scope()) {
            return null;
        }

        return new SwooleContextScope($scope, $this->handler);
    }

    public function current(): Context {
        $this->handler->switchToActiveCoroutine();

        return $this->storage->current();
    }

    public function attach(Context $context): ContextStorageScopeInterface {
        $this->handler->switchToActiveCoroutine();
        $this->handler->splitOffChildCoroutines();

        $scope = $this->storage->attach($context);

        return new SwooleContextScope($scope, $this->handler);
    }
}
