<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Trace;

use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\Context\ContextKeyInterface;
use OpenTelemetry\Context\ImplicitContextKeyedInterface;
use OpenTelemetry\Context\ScopeInterface;

class SpanSuppression implements ImplicitContextKeyedInterface
{
    private function __construct(
        private readonly array $suppressedSpanKinds = [],
    ) {
    }

    public static function suppressSpanKind(int $spanKind): self
    {
        $new = new self([$spanKind]);

        return self::current()->mergeWith($new);
    }

    public static function shouldSuppress(int $spanKind, ?ContextInterface $context = null): bool
    {
        return self::current($context)->shouldSuppressSpanKind($spanKind);
    }

    public function activate(): ScopeInterface
    {
        return Context::storage()->attach($this->storeInContext(Context::getCurrent()));
    }

    public function storeInContext(ContextInterface $context): ContextInterface
    {
        return $context->with(self::contextKey(), $this);
    }

    private static function default(): self
    {
        static $instance;
        $instance ??= new self();

        return $instance;
    }

    private function shouldSuppressSpanKind(int $spanKind): bool
    {
        return in_array($spanKind, $this->suppressedSpanKinds, true);
    }

    private static function current(?ContextInterface $context = null): self
    {
        $context ??= Context::getCurrent();
        $current = $context->get(self::contextKey());
        if ($current === null) {
            return self::default();
        }

        return $current;
    }

    private static function contextKey(): ContextKeyInterface
    {
        static $key;
        $key ??= Context::createKey(self::class);

        return $key;
    }

    private function mergeWith(self $other): self
    {
        return new self(
            array_unique(array_merge($this->suppressedSpanKinds, $other->suppressedSpanKinds))
        );
    }
}
