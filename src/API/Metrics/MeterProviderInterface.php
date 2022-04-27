<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Metrics;

interface MeterProviderInterface
{
    /**
     * @access	public
     * @param string $name - (required) - This name must identify the instrumentation scope
     * (e.g. io.opentelemetry.contrib.mongodb) and not the instrumented library.
     * In case an invalid name (null or empty string) is specified, a working default Meter implementation is returned
     * as a fallback rather than returning null or throwing an exception.
     * A MeterProvider could also return a no-op Meter here if application owners configure the SDK to suppress
     * telemetry produced by this library.
     * @param ?string $version - (optional) - Specifies the version of the instrumentation scope (e.g. semver:1.0.0)
     * @return MeterInterface
     */
    public function getMeter(string $name, ?string $version = null): MeterInterface;
}
