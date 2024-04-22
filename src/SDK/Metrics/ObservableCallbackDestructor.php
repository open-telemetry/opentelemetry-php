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
    /** @var array<int, ReferenceCounterInterface> */
    public array $callbackIds = [];

    /**
     * @param ArrayAccess<object, ObservableCallbackDestructor> $destructors
     */
    public function __construct(
        public ArrayAccess $destructors,
        private readonly MetricWriterInterface $writer,
    ) {
    }

    public function __destruct()
    {
        foreach ($this->callbackIds as $callbackId => $referenceCounter) {
            $this->writer->unregisterCallback($callbackId);
            $referenceCounter->release();
        }
    }
}
