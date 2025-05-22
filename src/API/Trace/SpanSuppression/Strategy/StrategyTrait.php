<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Trace\SpanSuppression\Strategy;

use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\Context\ContextKeyInterface;
use OpenTelemetry\Context\ScopeInterface;

trait StrategyTrait
{
    public function activate(): ScopeInterface
    {
        return Context::storage()->attach($this->storeInContext(Context::getCurrent()));
    }

    public function storeInContext(ContextInterface $context): ContextInterface
    {
        return $context->with(self::contextKey(), $this);
    }

    /**
     * @internal OpenTelemetry
     */
    public static function current(?ContextInterface $context = null): self
    {
        $context ??= Context::getCurrent();
        $current = $context->get(self::contextKey());
        if ($current === null) {
            return self::default();
        }

        return $current;
    }

    /**
     * @internal OpenTelemetry
     */
    public static function contextKey(): ContextKeyInterface
    {
        static $key;
        $key ??= Context::createKey(self::class);

        return $key;
    }

    /**
     * @phan-suppress PhanUndeclaredMethod
     */
    public static function default(): self
    {
        static $instance;
        $instance ??= new self();

        return $instance;
    }
}
