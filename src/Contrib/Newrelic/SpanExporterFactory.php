<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\Newrelic;

use OpenTelemetry\SDK\Common\Configuration\Configuration;
use OpenTelemetry\SDK\Common\Export\Http\PsrTransportFactory;
use OpenTelemetry\SDK\Trace\SpanExporter\SpanExporterFactoryInterface;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;

class SpanExporterFactory implements SpanExporterFactoryInterface
{
    public function create(): SpanExporterInterface
    {
        $licenseKey = Configuration::getString('NEW_RELIC_INSERT_KEY');
        $endpointUrl = Configuration::getString('NEW_RELIC_ENDPOINT', 'https://trace-api.newrelic.com/trace/v1');
        $dataFormatVersion = Exporter::DATA_FORMAT_VERSION_DEFAULT;

        $transport = PsrTransportFactory::discover()->create($endpointUrl, 'application/json', [
            'Api-Key' => $licenseKey,
            'Data-Format' => 'newrelic',
            'Data-Format-Version' => $dataFormatVersion,
        ]);

        return new Exporter('newrelic', $transport, $endpointUrl);
    }
}
