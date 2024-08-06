<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

use function assert;
use OpenTelemetry\API\Metrics\MeterInterface;
use OpenTelemetry\SDK\Metrics\MetricRegistry\MetricWriterInterface;

/**
 * @internal
 */
trait SynchronousInstrumentTrait
{
    private MetricWriterInterface $writer;
    private Instrument $instrument;
    private ReferenceCounterInterface $referenceCounter;
    private MeterInterface $meter;

    public function __construct(MetricWriterInterface $writer, Instrument $instrument, ReferenceCounterInterface $referenceCounter, MeterInterface $meter)
    {
        assert($this instanceof InstrumentHandle);

        $this->writer = $writer;
        $this->instrument = $instrument;
        $this->referenceCounter = $referenceCounter;
        $this->meter = $meter;

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

    public function write($amount, iterable $attributes = [], $context = null): void
    {
        if ($this->isEnabled()) {
            $this->writer->record($this->instrument, $amount, $attributes, $context);
        }
    }

    public function isEnabled(): bool
    {
        if (!$this->meter->isEnabled()) {
            return false;
        }

        return $this->writer->enabled($this->instrument);
    }
}
