<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\Jaeger;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\HttpFactory;
use OpenTelemetry\Contrib\Zipkin;
use OpenTelemetry\Sdk\Trace;

class Exporter extends Zipkin\Exporter implements Trace\Exporter
{
    public static function fromConnectionString(string $endpointUrl, string $name, $args = null)
    {
        $factory = new HttpFactory();
        $exporter = new Exporter(
            $name,
            $endpointUrl,
            new Client(),
            $factory,
            $factory
        );

        return $exporter;
    }
}
