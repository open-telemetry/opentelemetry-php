<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Metrics;

use OpenTelemetry\Metrics as API;

class Meter implements API\Meter
{
    /**
     * @var string $name
     * @var string $version
     */
    protected $name;
    protected $version;

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
    public function newCounter(string $name, string $description = ''): API\Counter
    {
        return new Counter($name, $description);
    }

    /**
     * {@inheritdoc}
     */
    public function newUpDownCounter(string $name, string $description = ''): API\UpDownCounter
    {
        return new UpDownCounter($name, $description);
    }

    /**
     * {@inheritdoc}
     */
    public function newValueRecorder(string $name, string $description = ''): API\ValueRecorder
    {
        return new ValueRecorder($name, $description);
    }
}
