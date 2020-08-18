<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Metrics;

use OpenTelemetry\Metrics as API;

class Meter implements API\Meter
{
    /**
     * @var string            $name
     * @var string            $version
     * @var API\Counter       $counter
     * @var API\UpDownCounter $upDownCounter
     * @var API\ValueRecorder $valueRecorder
     */
    private $name;
    private $version;
    private $counter;
    private $upDownCounter;
    private $valueRecorder;

    public function __construct(string $name, ?string $version = null)
    {
        $this->name = $name;
        $this->version = $version !== null ? $version : '';
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
    public function getVersion(): ?string
    {
        return $this->version;
    }

    /**
     * {@inheritdoc}
     */
    public function newCounter(string $name, string $description = null): API\Counter
    {
        $this->counter = new Counter($name, $description);

        return $this->counter;
    }

    /**
     * {@inheritdoc}
     */
    public function newUpDownCounter(string $name, string $description = null): API\UpDownCounter
    {
        $this->upDownCounter = new UpDownCounter($name, $description);

        return $this->upDownCounter;
    }

    /**
     * {@inheritdoc}
     */
    public function newValueRecorder(string $name, string $description = null): API\ValueRecorder
    {
        $this->valueRecorder = new ValueRecorder($name, $description);

        return $this->valueRecorder;
    }
}
