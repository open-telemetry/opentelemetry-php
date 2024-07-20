<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

use ArrayAccess;
use function assert;
use OpenTelemetry\API\Metrics\ObservableCallbackInterface;
use OpenTelemetry\API\Metrics\ObserverInterface;
use OpenTelemetry\SDK\Common\InstrumentationScope\Config;
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
        private Config $config,
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

    public function enabled(): bool
    {
        return $this->writer->enabled($this->instrument);
    }

    public function updateConfig(Config $config): void
    {
        $this->config = $config;
    }
}
