<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\StalenessHandler;

use OpenTelemetry\SDK\Metrics\ReferenceCounterInterface;

/**
 * @internal
 */
final class MultiReferenceCounter implements ReferenceCounterInterface
{
    /**
     * @param list<ReferenceCounterInterface> $referenceCounters
     */
    public function __construct(private readonly array $referenceCounters)
    {
    }

    public function acquire(bool $persistent = false): void
    {
        foreach ($this->referenceCounters as $referenceCounter) {
            $referenceCounter->acquire($persistent);
        }
    }

    public function release(): void
    {
        foreach ($this->referenceCounters as $referenceCounter) {
            $referenceCounter->release();
        }
    }
}
