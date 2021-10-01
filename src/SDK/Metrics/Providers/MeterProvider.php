<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\Providers;

use OpenTelemetry\API\Metrics as API;
use OpenTelemetry\SDK\Metrics\Meter;

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
        if (empty($this->meters[$name . $version])) {
            $this->meters[$name . $version] = $this->getCreatedMeter($name, $version);
        }

        return $this->meters[$name . $version];
    }

    /**
     * Creates a new Meter instance
     *
     * @access	protected
     * @param	string	$name
     * @param	string|null	$version Default: null
     * @return	API\Meter
     */
    protected function getCreatedMeter(string $name, string $version = null): API\Meter
    {
        // todo: once the Meter interface and an implementation are done, change this
        return new Meter($name, $version);
    }
}
