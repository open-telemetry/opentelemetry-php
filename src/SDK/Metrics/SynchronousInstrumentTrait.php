<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

use function assert;
use OpenTelemetry\SDK\Common\InstrumentationScope\Config;
use OpenTelemetry\SDK\Metrics\MetricRegistry\MetricWriterInterface;

/**
 * @internal
 */
trait SynchronousInstrumentTrait
{
    private MetricWriterInterface $writer;
    private Instrument $instrument;
    private ReferenceCounterInterface $referenceCounter;
    private Config $config;

    public function __construct(MetricWriterInterface $writer, Instrument $instrument, ReferenceCounterInterface $referenceCounter, Config $config)
    {
        assert($this instanceof InstrumentHandle);

        $this->writer = $writer;
        $this->instrument = $instrument;
        $this->referenceCounter = $referenceCounter;
        $this->config = $config;

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
        if ($this->enabled()) {
            $this->writer->record($this->instrument, $amount, $attributes, $context);
        }
    }

    public function enabled(): bool
    {
        return $this->config->isEnabled();
    }

    public function updateConfig(Config $config): void
    {
        $this->config = $config;
    }
}
