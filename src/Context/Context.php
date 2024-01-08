<?php

declare(strict_types=1);

namespace OpenTelemetry\Context;

use function assert;
use const FILTER_VALIDATE_BOOLEAN;
use function filter_var;
use function ini_get;
use function spl_object_id;

/**
 * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/context/README.md#context
 */
final class Context implements ContextInterface
{
    /** @var ContextStorageInterface&ExecutionContextAwareInterface */
    private static ContextStorageInterface $storage;

    // Optimization for spans to avoid copying the context array.
    private static ContextKeyInterface $spanContextKey;
    private ?object $span = null;
    /** @var array<int, mixed> */
    private array $context = [];
    /** @var array<int, ContextKeyInterface> */
    private array $contextKeys = [];

    private function __construct()
    {
        self::$spanContextKey = ContextKeys::span();
    }

    public static function createKey(string $key): ContextKeyInterface
    {
        return new ContextKey($key);
    }

    /**
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
     * @param ContextInterface|false|null $context
     *
     * @internal OpenTelemetry
     */
    public static function resolve($context, ?ContextStorageInterface $contextStorage = null): ContextInterface
    {
        return $context
            ?? ($contextStorage ?? self::storage())->current()
            ?: self::getRoot();
    }

    /**
     * @internal
     */
    public static function getRoot(): ContextInterface
    {
        static $empty;

        return $empty ??= new self();
    }

    public static function getCurrent(): ContextInterface
    {
        return self::storage()->current();
    }

    public function activate(): ScopeInterface
    {
        $scope = self::storage()->attach($this);
        /** @psalm-suppress RedundantCondition @phpstan-ignore-next-line */
        assert(self::debugScopesDisabled() || $scope = new DebugScope($scope));

        return $scope;
    }

    private static function debugScopesDisabled(): bool
    {
        $disabled = $_SERVER['OTEL_PHP_DEBUG_SCOPES_DISABLED'] ?? ini_get('OTEL_PHP_DEBUG_SCOPES_DISABLED');

        return filter_var($disabled, FILTER_VALIDATE_BOOLEAN);
    }

    public function withContextValue(ImplicitContextKeyedInterface $value): ContextInterface
    {
        return $value->storeInContext($this);
    }

    public function with(ContextKeyInterface $key, $value): self
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

    public function get(ContextKeyInterface $key)
    {
        if ($key === self::$spanContextKey) {
            /** @psalm-suppress InvalidReturnStatement */
            return $this->span;
        }

        return $this->context[spl_object_id($key)] ?? null;
    }
}
