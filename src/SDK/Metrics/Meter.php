<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

use OpenTelemetry\API\Metrics as API;

class Meter implements API\MeterInterface
{
    protected string $name;
    protected string $version;

    public function __construct(string $name, string $version = null)
    {
        $this->name = $name;
        $this->version = (string) $version;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * {@inheritdoc}
     */
    public function newCounter(string $name, string $description = ''): API\CounterInterface
    {
        return new Counter($name, $description);
    }

    /**
     * {@inheritdoc}
     */
    public function newUpDownCounter(string $name, string $description = ''): API\UpDownCounterInterface
    {
        return new UpDownCounter($name, $description);
    }

    /**
     * {@inheritdoc}
     */
    public function newValueRecorder(string $name, string $description = ''): API\ValueRecorderInterface
    {
        return new ValueRecorder($name, $description);
    }
}
