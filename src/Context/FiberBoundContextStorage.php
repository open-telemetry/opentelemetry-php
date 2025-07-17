<?php

declare(strict_types=1);

namespace OpenTelemetry\Context;

use function assert;
use const E_USER_WARNING;
use Fiber;
use function spl_object_id;
use function sprintf;
use function trigger_error;
use WeakMap;

/**
 * @internal
 */
final class FiberBoundContextStorage implements ContextStorageInterface, ContextStorageHeadAware
{
    /** @var WeakMap<object, ContextStorageHead> */
    private WeakMap $heads;

    public function __construct()
    {
        $this->heads = new WeakMap();
        $this->heads[$this] = new ContextStorageHead($this);
    }

    #[\Override]
    public function head(): ?ContextStorageHead
    {
        return $this->heads[Fiber::getCurrent() ?? $this] ?? null;
    }

    /**
     * @psalm-suppress PossiblyNullPropertyFetch
     */
    #[\Override]
    public function scope(): ?ContextStorageScopeInterface
    {
        $head = $this->heads[Fiber::getCurrent() ?? $this] ?? null;

        if (!$head?->node && Fiber::getCurrent()) {
            self::triggerNotInitializedFiberContextWarning();

            return null;
        }

        // Starts with empty head instead of cloned parent -> no need to check for head mismatch
        return $head->node;
    }

    #[\Override]
    public function current(): ContextInterface
    {
        $head = $this->heads[Fiber::getCurrent() ?? $this] ?? null;

        if (!$head?->node && Fiber::getCurrent()) {
            self::triggerNotInitializedFiberContextWarning();

            // Fallback to {main} to preserve BC
            $head = $this->heads[$this];
        }

        return $head->node->context ?? Context::getRoot();
    }

    /**
     * @psalm-suppress PossiblyNullArgument,PossiblyNullPropertyFetch
     */
    #[\Override]
    public function attach(ContextInterface $context): ContextStorageScopeInterface
    {
        $head = $this->heads[Fiber::getCurrent() ?? $this] ??= new ContextStorageHead($this);

        return $head->node = new ContextStorageNode($context, $head, $head->node);
    }

    private static function triggerNotInitializedFiberContextWarning(): void
    {
        $fiber = Fiber::getCurrent();
        assert($fiber !== null);

        trigger_error(sprintf(
            'Access to not initialized OpenTelemetry context in fiber (id: %d), automatic forking not supported, must attach initial fiber context manually',
            spl_object_id($fiber),
        ), E_USER_WARNING);
    }
}
