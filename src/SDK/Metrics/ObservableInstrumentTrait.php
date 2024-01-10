<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

use ArrayAccess;
use function assert;
use OpenTelemetry\API\Metrics\ObservableCallbackInterface;
use OpenTelemetry\API\Metrics\ObserverInterface;
use OpenTelemetry\SDK\Metrics\MetricRegistry\MetricWriterInterface;

/**
 * @internal
 */
trait ObservableInstrumentTrait
{
    private MetricWriterInterface $writer;
    private Instrument $instrument;
    private ReferenceCounterInterface $referenceCounter;
    private ArrayAccess $destructors;

    public function __construct(
        MetricWriterInterface $writer,
        Instrument $instrument,
        ReferenceCounterInterface $referenceCounter,
        ArrayAccess $destructors
    ) {
        assert($this instanceof InstrumentHandle);

        $this->writer = $writer;
        $this->instrument = $instrument;
        $this->referenceCounter = $referenceCounter;
        $this->destructors = $destructors;

        $this->referenceCounter->acquire();
    }

    public function __destruct()
    {
        $this->referenceCounter->release();
    }

    public function getHandle(): Instrument
    {
        return $this->instrument;
    }

    /**
     * @param callable(ObserverInterface): void $callback
     */
    public function observe(callable $callback): ObservableCallbackInterface
    {
        return AsynchronousInstruments::observe(
            $this->writer,
            $this->destructors,
            $callback,
            [$this->instrument],
            $this->referenceCounter,
        );
    }
}
