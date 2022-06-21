<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\Providers;

use OpenTelemetry\API\Metrics as API;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScope;
use OpenTelemetry\SDK\Common\Instrumentation\KeyGenerator;
use OpenTelemetry\SDK\Metrics\Meter;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SDK\Resource\ResourceInfoFactory;

class MeterProvider implements API\MeterProviderInterface
{
    protected array $meters = [];

    protected ResourceInfo $resource;

    public function __construct(ResourceInfo $resource = null)
    {
        $this->resource = $resource ?? ResourceInfoFactory::defaultResource();
    }

    /**
     * {@inheritDoc}
     */
    public function getMeter(string $name, ?string $version = null, ?string $schemaUrl = null): API\MeterInterface
    {
        $key = KeyGenerator::generateInstanceKey($name, $version, $schemaUrl);

        if (isset($this->meters[$key]) && $this->meters[$key] instanceof API\MeterInterface) {
            return $this->meters[$key];
        }

        $instrumentationScope = new InstrumentationScope($name, $version, $schemaUrl);

        $meter = new Meter($this->resource, $instrumentationScope);

        return $this->meters[$key] = $meter;
    }

    /**
     * Creates a new Meter instance
     *
     * @access	protected
     * @param	string	$name
     * @param	string|null	$version Default: null
     * @param	string|null	$schemaUrl Default: null
     * @return	API\MeterInterface
     */
    protected function getCreatedMeter(string $name, ?string $version = null, ?string $schemaUrl = null): API\MeterInterface
    {
        // todo: once the Meter interface and an implementation are done, change this
        $instrumentationScope = new InstrumentationScope($name, $version, $schemaUrl);

        return new Meter($this->resource, $instrumentationScope);
    }
}
