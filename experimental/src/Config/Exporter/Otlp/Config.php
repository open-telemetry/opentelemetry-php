<?php

declare(strict_types=1);

namespace OpenTelemetry\Experimental\Config\Exporter\Otlp;

use OpenTelemetry\Experimental\Config\ConfigInterface;
use OpenTelemetry\Experimental\Config\ExporterConfigInterface;

class Config implements ConfigInterface, ExporterConfigInterface
{
    public $endpoint;
    public $insecure;
    public $certificateFile;
    public $headers;
    public $compression;
    public $timeout;
    public $protocol;

    public function __construct(array $userConfig, array $environmentConfig)
    {
        $this->endpoint = $userConfig['exporter.otlp.endpoint'] ?? $environmentConfig['OTEL_EXPORTER_OTLP_ENDPOINT'] ?? null;
        $this->endpoint = $userConfig['exporter.otlp.insecure'] ?? $environmentConfig['OTEL_EXPORTER_OTLP_INSECURE'] ?? null;
        $this->endpoint = $userConfig['exporter.otlp.certificate_file'] ?? $environmentConfig['OTEL_EXPORTER_OTLP_CERTIFICATE'] ?? null;
        $this->endpoint = $userConfig['exporter.otlp.headers'] ?? $environmentConfig['OTEL_EXPORTER_OTLP_HEADERS'] ?? null;
        $this->endpoint = $userConfig['exporter.otlp.compression'] ?? $environmentConfig['OTEL_EXPORTER_OTLP_COMPRESSION'] ?? null;
        $this->endpoint = $userConfig['exporter.otlp.timeout'] ?? $environmentConfig['OTEL_EXPORTER_OTLP_TIMEOUT'] ?? null;
        $this->endpoint = $userConfig['exporter.otlp.protocol'] ?? $environmentConfig['OTEL_EXPORTER_OTLP_PROTOCOL'] ?? null;
    }

    public static function provides(string $exporterName): bool
    {
        return $exporterName === 'otlp';
    }

    public function getName(): string
    {
        return 'otlp';
    }
}
