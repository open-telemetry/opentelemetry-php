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
    private const SUPPRESS_NONE = 0;
    private const SUPPRESS_SPAN_KIND = 1;

    private function __construct(
        private readonly int $suppressionType,
        private readonly array $suppressedSpanKinds = [],
    ) {
    }

    public static function suppressSpanKind(array $spanKinds): self
    {
        $new = new self(self::SUPPRESS_SPAN_KIND, $spanKinds);

        // Automatically merge with any existing strategy in the current context
        $current = self::current();
        if ($current->suppressionType !== self::SUPPRESS_NONE) {
            return $current->mergeWith($new);
        }

        return $new;
    }

    public static function shouldSuppress(?int $spanKind): bool
    {
        return self::current()->shouldSuppressSpanKind($spanKind);
    }

    public function activate(): ScopeInterface
    {
        return Context::storage()->attach($this->storeInContext(Context::getCurrent()));
    }

    public function storeInContext(ContextInterface $context): ContextInterface
    {
        return $context->with(self::contextKey(), $this);
    }

    private static function suppressNone(): self
    {
        static $instance;
        $instance ??= new self(self::SUPPRESS_NONE);

        return $instance;
    }

    private function shouldSuppressSpanKind(?int $spanKind): bool
    {
        if ($this->suppressionType === self::SUPPRESS_NONE) {
            return false;
        }

        if ($this->suppressionType === self::SUPPRESS_SPAN_KIND && $spanKind !== null) {
            return in_array($spanKind, $this->suppressedSpanKinds, true);
        }

        return false;
    }

    private static function current(): self
    {
        $current = Context::getCurrent()->get(self::contextKey());
        if ($current === null) {
            return self::suppressNone();
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
        if ($this->suppressionType === self::SUPPRESS_NONE) {
            return $other;
        }

        if ($other->suppressionType === self::SUPPRESS_NONE) {
            return $this;
        }

        if ($this->suppressionType === self::SUPPRESS_SPAN_KIND &&
            $other->suppressionType === self::SUPPRESS_SPAN_KIND) {
            return new self(
                self::SUPPRESS_SPAN_KIND,
                array_unique(array_merge($this->suppressedSpanKinds, $other->suppressedSpanKinds))
            );
        }

        // Default case
        return $this;
    }
}
