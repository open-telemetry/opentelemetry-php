<?php

declare(strict_types=1);

namespace OpenTelemetry\Context;

use function assert;
use const FILTER_VALIDATE_BOOLEAN;
use function filter_var;
use function spl_object_id;

/**
 * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/context/README.md#context
 */
final class Context implements ContextInterface
{
    private const OTEL_PHP_DEBUG_SCOPES_DISABLED = 'OTEL_PHP_DEBUG_SCOPES_DISABLED';

    private static ContextStorageInterface&ExecutionContextAwareInterface $storage;

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

    #[\Override]
    public static function createKey(string $key): ContextKeyInterface
    {
        return new ContextKey($key);
    }

    public static function setStorage(ContextStorageInterface&ExecutionContextAwareInterface $storage): void
    {
        self::$storage = $storage;
    }

    public static function storage(): ContextStorageInterface&ExecutionContextAwareInterface
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        return self::$storage ??= new FiberBoundContextStorageExecutionAwareBC();
    }

    /**
     * @internal OpenTelemetry
     */
    public static function resolve(ContextInterface|false|null $context, ?ContextStorageInterface $contextStorage = null): ContextInterface
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

    #[\Override]
    public static function getCurrent(): ContextInterface
    {
        return self::storage()->current();
    }

    #[\Override]
    public function activate(): ScopeInterface
    {
        $scope = self::storage()->attach($this);
        /** @psalm-suppress RedundantCondition @phpstan-ignore-next-line */
        assert(self::debugScopesDisabled() || $scope = new DebugScope($scope));

        return $scope;
    }

    private static function debugScopesDisabled(): bool
    {
        return filter_var(
            $_SERVER[self::OTEL_PHP_DEBUG_SCOPES_DISABLED] ?? \getenv(self::OTEL_PHP_DEBUG_SCOPES_DISABLED) ?: \ini_get(self::OTEL_PHP_DEBUG_SCOPES_DISABLED),
            FILTER_VALIDATE_BOOLEAN
        );
    }

    #[\Override]
    public function withContextValue(ImplicitContextKeyedInterface $value): ContextInterface
    {
        return $value->storeInContext($this);
    }

    #[\Override]
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

    #[\Override]
    public function get(ContextKeyInterface $key)
    {
        if ($key === self::$spanContextKey) {
            /** @psalm-suppress InvalidReturnStatement */
            return $this->span;
        }

        return $this->context[spl_object_id($key)] ?? null;
    }
}
