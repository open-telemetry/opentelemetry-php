<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\Providers;

use OpenTelemetry\API\Metrics as API;
use OpenTelemetry\SDK\Metrics\Meter;

class MeterProvider implements API\MeterProviderInterface
{
    /**
     * @var array $meters
     */
    protected $meters = [];

    /**
     * {@inheritDoc}
     */
    public function getMeter(string $name, ?string $version = null): API\MeterInterface
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
     * @return	API\MeterInterface
     */
    protected function getCreatedMeter(string $name, string $version = null): API\MeterInterface
    {
        // todo: once the Meter interface and an implementation are done, change this
        return new Meter($name, $version);
    }
}
