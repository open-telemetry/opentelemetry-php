<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Trace\SpanSuppression\Strategy;

use OpenTelemetry\API\Trace\SpanSuppression\SpanSuppressionStrategyInterface;
use OpenTelemetry\Context\ContextInterface;

/**
 * @experimental
 */
class SemConvSuppressionStrategy implements SpanSuppressionStrategyInterface
{
    use StrategyTrait;

    /**
     * @todo Allow wildcards in value?
     */
    public static function suppressSemConv(string $key, mixed $value, ?ContextInterface $context = null): self
    {
        $new = new self([['key' => $key, 'value' => $value]]);

        return self::current($context)->mergeWith($new);
    }

    public function shouldSuppress(int $spanKind, array $attributes = [], ?ContextInterface $context = null): bool
    {
        foreach ($this->suppressedSemConvs as $entry) {
            if (isset($attributes[$entry['key']]) && $attributes[$entry['key']] === $entry['value']) {
                return true;
            }
        }

        return false;
    }

    private function __construct(
        private readonly array $suppressedSemConvs = [],
    ) {
    }

    private function mergeWith(self $other): self
    {
        return new self(
            array_merge($this->suppressedSemConvs, $other->suppressedSemConvs)
        );
    }
}
