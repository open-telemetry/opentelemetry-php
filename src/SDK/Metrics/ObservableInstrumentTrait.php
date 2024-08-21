<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

use ArrayAccess;
use function assert;
use OpenTelemetry\API\Metrics\MeterInterface;
use OpenTelemetry\API\Metrics\ObservableCallbackInterface;
use OpenTelemetry\API\Metrics\ObserverInterface;
use OpenTelemetry\SDK\Metrics\MetricRegistry\MetricWriterInterface;

/**
 * @internal
 */
trait ObservableInstrumentTrait
{
    public function __construct(
        private readonly MetricWriterInterface $writer,
        private readonly Instrument $instrument,
        private readonly ReferenceCounterInterface $referenceCounter,
        private readonly ArrayAccess $destructors,
        private readonly MeterInterface $meter,
    ) {
        assert($this instanceof InstrumentHandle);

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

    public function isEnabled(): bool
    {
        if (!$this->meter->isEnabled()) {
            return false;
        }

        return $this->writer->enabled($this->instrument);
    }
}
