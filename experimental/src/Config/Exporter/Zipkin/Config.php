<?php

declare(strict_types=1);

namespace OpenTelemetry\Experimental\Config\Exporter\Zipkin;

use OpenTelemetry\Experimental\Config\ConfigInterface;
use OpenTelemetry\Experimental\Config\ExporterConfigInterface;

class Config implements ConfigInterface, ExporterConfigInterface
{
    public ?string $endpoint;
    public ?int $timeout;

    public function __construct(array $userConfig, array $environmentConfig)
    {
        $this->endpoint = $userConfig['exporter.zipkin.endpoint'] ?? $environmentConfig['OTEL_EXPORTER_ZIPKIN_ENDPOINT'] ?? null;
        $this->timeout = (int) ($userConfig['exporter.zipkin.timeout'] ?? $environmentConfig['OTEL_EXPORTER_ZIPKIN_TIMEOUT'] ?? null);
    }

    public static function provides(string $exporterName): bool
    {
        return $exporterName === 'zipkin';
    }

    public function getName(): string
    {
        return 'zipkin';
    }
}
