<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\OtlpGrpc;

use grpc;
use Grpc\ChannelCredentials;
use OpenTelemetry\Contrib\Otlp\MetricConverter;
use Opentelemetry\Proto\Collector\Metrics\V1\ExportMetricsServiceRequest;
use Opentelemetry\Proto\Collector\Metrics\V1\MetricsServiceClient;
use OpenTelemetry\SDK\Behavior\LogsMessagesTrait;
use OpenTelemetry\SDK\Common\Environment\EnvironmentVariablesTrait;
use OpenTelemetry\SDK\Common\Environment\KnownValues as Values;
use OpenTelemetry\SDK\Common\Environment\Variables as Env;
use OpenTelemetry\SDK\Metrics\Exceptions\ExportException;
use OpenTelemetry\SDK\Metrics\Exceptions\RetryableExportException;
use OpenTelemetry\SDK\Metrics\Exporters\AbstractExporter;

class MetricsExporter extends AbstractExporter
{
    use EnvironmentVariablesTrait;
    use LogsMessagesTrait;

    private bool $insecure;

    private string $certificateFile = '';

    private string $compression;

    private int $timeout;

    private array $metadata;

    private MetricsServiceClient $client;

    /**
     * OTLP GRPC Exporter Constructor
     */
    public function __construct(
        string $endpointURL = 'localhost:4317',
        bool $insecure = true,
        string $certificateFile = '',
        string $headers = '',
        bool $compression = false,
        int $timeout = 10,
        MetricsServiceClient $client = null
    ) {
        $this->insecure = $this->hasEnvironmentVariable(Env::OTEL_EXPORTER_OTLP_METRICS_INSECURE) ?
            $this->getBooleanFromEnvironment(Env::OTEL_EXPORTER_OTLP_METRICS_INSECURE, $insecure) :
            $this->getBooleanFromEnvironment(Env::OTEL_EXPORTER_OTLP_INSECURE, $insecure);

        if (!empty($certificateFile) || $this->hasEnvironmentVariable(Env::OTEL_EXPORTER_OTLP_CERTIFICATE)) {
            $this->certificateFile = $this->hasEnvironmentVariable(Env::OTEL_EXPORTER_OTLP_METRICS_CERTIFICATE) ?
                $this->getStringFromEnvironment(Env::OTEL_EXPORTER_OTLP_METRICS_CERTIFICATE) :
                $this->getStringFromEnvironment(Env::OTEL_EXPORTER_OTLP_CERTIFICATE, $certificateFile);
        }

        $this->compression = $this->hasEnvironmentVariable(Env::OTEL_EXPORTER_OTLP_METRICS_COMPRESSION) ?
            $this->getEnumFromEnvironment(Env::OTEL_EXPORTER_OTLP_METRICS_COMPRESSION) :
            $this->getEnumFromEnvironment(
                Env::OTEL_EXPORTER_OTLP_COMPRESSION,
                $compression ? Values::VALUE_GZIP : Values::VALUE_NONE
            );

        $this->timeout = $this->hasEnvironmentVariable(Env::OTEL_EXPORTER_OTLP_METRICS_TIMEOUT) ?
            $this->getIntFromEnvironment(Env::OTEL_EXPORTER_OTLP_METRICS_TIMEOUT, $timeout) :
            $this->getIntFromEnvironment(Env::OTEL_EXPORTER_OTLP_TIMEOUT, $timeout);

        $this->metadata = $this->hasEnvironmentVariable(Env::OTEL_EXPORTER_OTLP_METRICS_HEADERS) ?
            $this->getMapFromEnvironment(Env::OTEL_EXPORTER_OTLP_METRICS_HEADERS, $headers) :
            $this->getMapFromEnvironment(Env::OTEL_EXPORTER_OTLP_HEADERS, $headers);

        $opts = $this->getClientOptions();

        $this->client = $client ?? new MetricsServiceClient(
            $this->hasEnvironmentVariable(Env::OTEL_EXPORTER_OTLP_METRICS_ENDPOINT) ?
                    $this->getStringFromEnvironment(Env::OTEL_EXPORTER_OTLP_METRICS_ENDPOINT) :
                    $this->getStringFromEnvironment(Env::OTEL_EXPORTER_OTLP_ENDPOINT, $endpointURL),
            $opts
        );
    }

    /**
     * @psalm-suppress UndefinedClass
     * @psalm-suppress UndefinedConstant
     */
    public function getClientOptions(): array
    {
        $opts = [
            'update_metadata' => fn () => $this->metadata,
            'timeout' => $this->timeout,
            'credentials' => $this->getCredentials(),
        ];

        if ($this->compression === Values::VALUE_GZIP) {
            // gzip is the only specified compression method for now
            $opts['grpc.default_compression_algorithm'] = 2;
        }

        return $opts;
    }

    /**
     * @inheritDoc
     * @throws ExportException
     * @throws RetryableExportException
     */
    protected function doExport(iterable $metrics): void
    {
        $resourceMetrics = (new MetricConverter())->convert($metrics);
        $request = new ExportMetricsServiceRequest([
            'resource_metrics' => $resourceMetrics,
        ]);

        [$response, $status] = $this->client->Export($request)->wait();

        if ($status->code === \Grpc\STATUS_OK) {
            self::logDebug('Exported metric(s)', ['metrics' => $metrics]);

            return;
        }

        $error = [
            'error' => $status->details ?? 'unknown grpc error',
            'code' => $status->code,
        ];

        if (in_array($status->code, [
            \Grpc\STATUS_CANCELLED,
            \Grpc\STATUS_DEADLINE_EXCEEDED,
            \Grpc\STATUS_PERMISSION_DENIED,
            \Grpc\STATUS_RESOURCE_EXHAUSTED,
            \Grpc\STATUS_ABORTED,
            \Grpc\STATUS_OUT_OF_RANGE,
            \Grpc\STATUS_UNAVAILABLE,
            \Grpc\STATUS_DATA_LOSS,
            \Grpc\STATUS_UNAUTHENTICATED,
        ], true)) {
            self::logWarning('Retryable error exporting grpc metric', ['error' => $error]);

            throw new RetryableExportException($error['error'], $error['code']);
        }

        self::logError('Error exporting grpc metric', ['error' => $error]);

        throw new ExportException($error['error'], $error['code']);
    }

    public function setHeader($key, $value): void
    {
        // metadata is supposed to be key-value pairs
        // @see https://grpc.io/docs/what-is-grpc/core-concepts/#metadata
        $this->metadata[$key] = $value;
    }

    public function getHeaders(): array
    {
        return $this->metadata;
    }

    private function getCredentials(): ?ChannelCredentials
    {
        if (!$this->insecure) {
            return $this->certificateFile !== ''
                ? Grpc\ChannelCredentials::createSsl(file_get_contents($this->certificateFile))
                : Grpc\ChannelCredentials::createSsl('');
        }

        return Grpc\ChannelCredentials::createInsecure();
    }

    public function getCertificateFile(): string
    {
        return $this->certificateFile;
    }

    public static function fromConnectionString(string $endpointUrl = null, string $name = null, $args = null): MetricsExporter
    {
        return is_string($endpointUrl) ? new MetricsExporter($endpointUrl) :  new MetricsExporter();
    }
}
