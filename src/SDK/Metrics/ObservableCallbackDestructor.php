<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

use OpenTelemetry\SDK\Metrics\MetricRegistry\MetricWriterInterface;

/**
 * @internal
 */
final class ObservableCallbackDestructor
{
    /** @var array<int, int> */
    public array $callbackIds = [];
    private MetricWriterInterface $writer;
    private ReferenceCounterInterface $referenceCounter;

    public function __construct(MetricWriterInterface $writer, ReferenceCounterInterface $referenceCounter)
    {
        $this->writer = $writer;
        $this->referenceCounter = $referenceCounter;
    }

    public function __destruct()
    {
        foreach ($this->callbackIds as $callbackId) {
            $this->writer->unregisterCallback($callbackId);
            $this->referenceCounter->release();
        }
    }
}
