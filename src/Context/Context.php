<?php

declare(strict_types=1);

namespace OpenTelemetry\Context;

use function spl_object_id;

/**
 * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/context/README.md#context
 */
final class Context
{
    /** @var ContextStorageInterface&ExecutionContextAwareInterface */
    private static ContextStorageInterface $storage;

    // Optimization for spans to avoid copying the context array.
    private static ContextKey $spanContextKey;
    private ?object $span = null;
    /** @var array<int, mixed> */
    private array $context = [];
    /** @var array<int, ContextKey> */
    private array $contextKeys = [];

    private function __construct()
    {
        self::$spanContextKey = ContextKeys::span();
    }

    public static function createKey(string $key): ContextKey
    {
        return new ContextKey($key);
    }

    /**
     * @internal
     *
     * @param ContextStorageInterface&ExecutionContextAwareInterface $storage
     */
    public static function setStorage(ContextStorageInterface $storage): void
    {
        self::$storage = $storage;
    }

    /**
     * @return ContextStorageInterface&ExecutionContextAwareInterface
     */
    public static function storage(): ContextStorageInterface
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        return self::$storage ??= new ContextStorage();
    }

    /**
     * @internal
     */
    public static function getRoot(): Context
    {
        static $empty;

        return $empty ??= new self();
    }

    public static function getCurrent(): Context
    {
        return self::storage()->current();
    }

    public function activate(): ScopeInterface
    {
        $scope = self::storage()->attach($this);
        /** @psalm-suppress RedundantCondition */
        assert((bool) $scope = new DebugScope($scope));

        return $scope;
    }

    public function withContextValue(ImplicitContextKeyedInterface $value): Context
    {
        return $value->storeInContext($this);
    }

    public function with(ContextKey $key, $value): self
    {
        if ($this->get($key) === $value) {
            return $this;
        }

        $self = clone $this;

        if ($key === self::$spanContextKey) {
            $self->span = $value; // @phan-suppress-current-line PhanTypeMismatchPropertyReal

            return $self;
        }

        $id = spl_object_id($key);
        if ($value !== null) {
            $self->context[$id] = $value;
            $self->contextKeys[$id] ??= $key;
        } else {
            unset(
                $self->context[$id],
                $self->contextKeys[$id],
            );
        }

        return $self;
    }

    public function get(ContextKey $key)
    {
        if ($key === self::$spanContextKey) {
            return $this->span;
        }

        return $this->context[spl_object_id($key)] ?? null;
    }
}
