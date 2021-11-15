<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\Jaeger;

use Http\Discovery\HttpClientDiscovery;
use Http\Discovery\Psr17FactoryDiscovery;
use OpenTelemetry\Contrib\Zipkin;

class Exporter extends Zipkin\Exporter
{
    /** @inheritDoc */
    public static function fromConnectionString(string $endpointUrl, string $name, $args = null)
    {
        return new Exporter(
            $name,
            $endpointUrl,
            HttpClientDiscovery::find(),
            Psr17FactoryDiscovery::findRequestFactory(),
            Psr17FactoryDiscovery::findStreamFactory()
        );
    }
}
