<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Metrics\Providers;

use OpenTelemetry\Metrics as API;
use OpenTelemetry\Sdk\Metrics\Meter;

class MeterProvider implements API\MeterProvider
{
    /**
     * @var array $meters
     */
    protected $meters = [];

    /**
     * {@inheritDoc}
     */
    public function getMeter(string $name, ?string $version = null): API\Meter
    {
        if (empty($this->meters[$name])) {
            $this->meters[$name] = $this->getCreatedMeter();
        }

        return $this->meters[$name];
    }

    /**
     * Creates a new Meter instance
     *
     * @access	protected
     * @return	API\Meter
     */
    protected function getCreatedMeter(): API\Meter
    {
        return new Meter();
    }
}
