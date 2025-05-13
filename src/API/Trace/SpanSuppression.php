<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Trace;

use OpenTelemetry\API\Trace\SpanSuppression\Strategy\SemConvSuppressionStrategy;
use OpenTelemetry\API\Trace\SpanSuppression\Strategy\SpanKindSuppressionStrategy;
use OpenTelemetry\Context\ContextInterface;

/**
 * @experimental
 */
class SpanSuppression
{
    public const NOOP = 'noop';
    public const SPAN_KIND = 'span_kind';
    public const SEM_CONV = 'sem_conv';

    private static array $availableStrategies = [
        self::NOOP,
        self::SPAN_KIND,
        self::SEM_CONV,
    ];

    private static array $strategies = [self::NOOP];

    private function __construct()
    {
    }

    public static function setStrategies(array $strategies): void
    {
        self::$strategies = [];
        foreach ($strategies as $strategy) {
            if (!in_array($strategy, self::$availableStrategies)) {
                throw new \InvalidArgumentException("Unknown strategy: $strategy");
            }
        }
        self::$strategies = $strategies;
    }

    /**
     * A span should be suppressed if any of the strategies return true.
     */
    public static function shouldSuppress(int $spanKind, array $attributes = [], ?ContextInterface $context = null): bool
    {
        foreach (self::$strategies as $strategy) {
            switch ($strategy) {
                case self::NOOP:
                    break;
                case self::SPAN_KIND:
                    if (SpanKindSuppressionStrategy::current($context)->shouldSuppress($spanKind)) {
                        return true;
                    }

                    break;
                case self::SEM_CONV:
                    if (SemConvSuppressionStrategy::current($context)->shouldSuppress($spanKind, $attributes, $context)) {
                        return true;
                    }

                    break;
                default:
                    throw new \InvalidArgumentException("Unknown strategy: $strategy");
            }
        }

        return false;
    }
}
