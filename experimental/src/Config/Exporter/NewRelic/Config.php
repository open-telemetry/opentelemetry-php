<?php

declare(strict_types=1);

namespace OpenTelemetry\Experimental\Config\Exporter\NewRelic;

use OpenTelemetry\Experimental\Config\ConfigInterface;
use OpenTelemetry\Experimental\Config\ExporterConfigInterface;

class Config implements ConfigInterface, ExporterConfigInterface
{
    public ?string $endpoint;
    public ?string $licenseKey;

    public function __construct(array $userConfig, array $environmentConfig)
    {
        $this->endpoint = $userConfig['exporter.new_relic.endpoint'] ?? $environmentConfig['OTEL_EXPORTER_NEWRELIC_ENDPOINT'] ?? null;
        $this->licenseKey = $userConfig['exporter.new_relic.license_key'] ?? $environmentConfig['OTEL_EXPORTER_NEWRELIC_LICENSE_KEY'] ?? null;
    }

    public static function provides(string $exporterName): bool
    {
        return $exporterName === 'newrelic';
    }

    public function getName(): string
    {
        return 'newrelic';
    }
}
