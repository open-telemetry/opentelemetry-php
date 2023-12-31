<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

use ArrayAccess;
use OpenTelemetry\SDK\Metrics\MetricRegistry\MetricWriterInterface;

/**
 * @internal
 */
final class BatchObservableCallbackDestructor
{
    public ArrayAccess $destructors;
    public array $callbackIds = [];
    private MetricWriterInterface $writer;

    /**
     * @param ArrayAccess<object, BatchObservableCallbackDestructor> $destructors
     * @param array<int, list<ReferenceCounterInterface>> $callbackIds
     */
    public function __construct(
        ArrayAccess $destructors,
        MetricWriterInterface $writer,
        array $callbackIds = []
    ) {
        $this->callbackIds = $callbackIds;
        $this->writer = $writer;
        $this->destructors = $destructors;
    }

    public function __destruct()
    {
        foreach ($this->callbackIds as $callbackId => $referenceCounters) {
            $this->writer->unregisterCallback($callbackId);
            foreach ($referenceCounters as $referenceCounter) {
                $referenceCounter->release();
            }
        }
    }
}
