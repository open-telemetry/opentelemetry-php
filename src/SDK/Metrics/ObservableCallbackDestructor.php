<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

use ArrayAccess;
use OpenTelemetry\SDK\Metrics\MetricRegistry\MetricWriterInterface;

/**
 * @internal
 */
final class ObservableCallbackDestructor
{
    public ArrayAccess $destructors;
    /** @var array<int, ReferenceCounterInterface> */
    public array $callbackIds = [];
    private MetricWriterInterface $writer;

    /**
     * @param ArrayAccess<object, ObservableCallbackDestructor> $destructors
     */
    public function __construct(ArrayAccess $destructors, MetricWriterInterface $writer)
    {
        $this->destructors = $destructors;
        $this->writer = $writer;
    }

    public function __destruct()
    {
        foreach ($this->callbackIds as $callbackId => $referenceCounter) {
            $this->writer->unregisterCallback($callbackId);
            $referenceCounter->release();
        }
    }
}
