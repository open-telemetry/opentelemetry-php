<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

use OpenTelemetry\API\Metrics\ObservableCallbackInterface;
use OpenTelemetry\SDK\Metrics\MetricRegistry\MetricWriterInterface;

/**
 * @internal
 */
final class ObservableCallback implements ObservableCallbackInterface
{
    private MetricWriterInterface $writer;
    private ReferenceCounterInterface $referenceCounter;
    private ?int $callbackId;
    private ?ObservableCallbackDestructor $callbackDestructor;
    /** @phpstan-ignore-next-line */
    private ?object $target;

    public function __construct(MetricWriterInterface $writer, ReferenceCounterInterface $referenceCounter, int $callbackId, ?ObservableCallbackDestructor $callbackDestructor, ?object $target)
    {
        $this->writer = $writer;
        $this->referenceCounter =  $referenceCounter;
        $this->callbackId = $callbackId;
        $this->callbackDestructor = $callbackDestructor;
        $this->target = $target;
    }

    public function detach(): void
    {
        if ($this->callbackId === null) {
            return;
        }

        $this->writer->unregisterCallback($this->callbackId);
        $this->referenceCounter->release();
        if ($this->callbackDestructor !== null) {
            unset($this->callbackDestructor->callbackIds[$this->callbackId]);
        }

        $this->callbackId = null;
    }

    public function __destruct()
    {
        if ($this->callbackDestructor !== null) {
            return;
        }
        if ($this->callbackId === null) {
            return;
        }

        $this->referenceCounter->acquire(true);
        $this->referenceCounter->release();
    }
}
