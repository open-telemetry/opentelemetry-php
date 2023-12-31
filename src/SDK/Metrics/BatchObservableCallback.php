<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

use function assert;
use OpenTelemetry\API\Metrics\ObservableCallbackInterface;
use OpenTelemetry\SDK\Metrics\MetricRegistry\MetricWriterInterface;

/**
 * @internal
 */
final class BatchObservableCallback implements ObservableCallbackInterface
{
    private MetricWriterInterface $writer;
    private array $referenceCounters;
    private ?int $callbackId;
    private ?BatchObservableCallbackDestructor $callbackDestructor;
    private ?object $target;

    /**
     * @param list<ReferenceCounterInterface> $referenceCounters
     */
    public function __construct(
        MetricWriterInterface $writer,
        array $referenceCounters,
        ?int $callbackId,
        ?BatchObservableCallbackDestructor $callbackDestructor,
        ?object $target
    ) {
        $this->target = $target;
        $this->callbackDestructor = $callbackDestructor;
        $this->callbackId = $callbackId;
        $this->referenceCounters = $referenceCounters;
        $this->writer = $writer;
    }

    public function detach(): void
    {
        if ($this->callbackId === null) {
            return;
        }

        $this->writer->unregisterCallback($this->callbackId);
        foreach ($this->referenceCounters as $referenceCounter) {
            $referenceCounter->release();
        }
        if ($this->callbackDestructor !== null) {
            unset($this->callbackDestructor->callbackIds[$this->callbackId]);
            if (!$this->callbackDestructor->callbackIds) {
                assert($this->target !== null);
                unset($this->callbackDestructor->destructors[$this->target]);
            }
        }

        $this->callbackId = null;
        $this->target = null;
    }

    public function __destruct()
    {
        if ($this->callbackDestructor !== null) {
            return;
        }
        if ($this->callbackId === null) {
            return;
        }

        foreach ($this->referenceCounters as $referenceCounter) {
            $referenceCounter->acquire(true);
            $referenceCounter->release();
        }
    }
}
