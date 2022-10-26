<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\OtlpGrpc;

use Grpc;
use Grpc\ChannelCredentials;
use OpenTelemetry\Contrib\Otlp\ExporterTrait;
use OpenTelemetry\Contrib\Otlp\SpanConverter;
use Opentelemetry\Proto\Collector\Trace\V1\TraceServiceClient;
use OpenTelemetry\SDK\Common\Environment\EnvironmentVariables;
use OpenTelemetry\SDK\Common\Environment\KnownValues as Values;
use OpenTelemetry\SDK\Common\Environment\Variables as Env;
use OpenTelemetry\SDK\Trace\Behavior\SpanExporterTrait;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;

class Exporter implements SpanExporterInterface
{
    use ExporterTrait;
    use SpanExporterTrait;

    // @todo: Please, check if this code is needed. It creates an error in phpstan, since it's not used
    // private $protocol;

    private bool $insecure;

    private string $certificateFile = '';

    private string $compression;

    private int $timeout;

    private array $metadata;

    private TraceServiceClient $client;

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
        TraceServiceClient $client = null
    ) {
        $this->insecure = EnvironmentVariables::has(Env::OTEL_EXPORTER_OTLP_TRACES_INSECURE) ?
            EnvironmentVariables::getBoolean(Env::OTEL_EXPORTER_OTLP_TRACES_INSECURE, $insecure) :
            EnvironmentVariables::getBoolean(Env::OTEL_EXPORTER_OTLP_INSECURE, $insecure);

        if (!empty($certificateFile) || EnvironmentVariables::has(Env::OTEL_EXPORTER_OTLP_CERTIFICATE)) {
            $this->certificateFile = EnvironmentVariables::has(Env::OTEL_EXPORTER_OTLP_TRACES_CERTIFICATE) ?
                    EnvironmentVariables::getString(Env::OTEL_EXPORTER_OTLP_TRACES_CERTIFICATE) :
                    EnvironmentVariables::getString(Env::OTEL_EXPORTER_OTLP_CERTIFICATE, $certificateFile);
        }

        $this->compression = EnvironmentVariables::has(Env::OTEL_EXPORTER_OTLP_TRACES_COMPRESSION) ?
            EnvironmentVariables::getEnum(Env::OTEL_EXPORTER_OTLP_TRACES_COMPRESSION) :
            EnvironmentVariables::getEnum(
                Env::OTEL_EXPORTER_OTLP_COMPRESSION,
                $compression ? Values::VALUE_GZIP : Values::VALUE_NONE
            );

        $this->timeout = EnvironmentVariables::has(Env::OTEL_EXPORTER_OTLP_TRACES_TIMEOUT) ?
            EnvironmentVariables::getInt(Env::OTEL_EXPORTER_OTLP_TRACES_TIMEOUT, $timeout) :
            EnvironmentVariables::getInt(Env::OTEL_EXPORTER_OTLP_TIMEOUT, $timeout);

        $this->metadata = self::transformToGrpcMetadata(EnvironmentVariables::has(Env::OTEL_EXPORTER_OTLP_TRACES_HEADERS) ?
            EnvironmentVariables::getMap(Env::OTEL_EXPORTER_OTLP_TRACES_HEADERS, $headers) :
            EnvironmentVariables::getMap(Env::OTEL_EXPORTER_OTLP_HEADERS, $headers));

        $opts = $this->getClientOptions();

        $this->client = $client ?? new TraceServiceClient(
            EnvironmentVariables::has(Env::OTEL_EXPORTER_OTLP_TRACES_ENDPOINT) ?
                EnvironmentVariables::getString(Env::OTEL_EXPORTER_OTLP_TRACES_ENDPOINT) :
                EnvironmentVariables::getString(Env::OTEL_EXPORTER_OTLP_ENDPOINT, $endpointURL),
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
     * @psalm-suppress UndefinedConstant
     */
    protected function doExport(iterable $spans): bool
    {
        $request = (new SpanConverter())->convert($spans);

        // @var \Opentelemetry\Proto\Collector\Trace\V1\ExportTraceServiceResponse|null $response
        [$response, $status] = $this->client->Export($request)->wait();
        $fullSuccessWithPartial = false;
        if ($response && $response->hasPartialSuccess()) {
            //@see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/protocol/otlp.md#partial-success
            if ($response->getPartialSuccess()->getRejectedSpans()) {
                self::logError('Partial success exporting span(s)', [
                    'dropped' => $response->getPartialSuccess()->getRejectedSpans(),
                    'error' => $response->getPartialSuccess()->getErrorMessage(),
                ]);

                return false;
            } elseif ($response->getPartialSuccess()->getErrorMessage()) {
                self::logWarning('Export warning', ['server_message' => $response->getPartialSuccess()->getErrorMessage()]);
            }
        }

        if ($status->code === \Grpc\STATUS_OK) {
            self::logDebug('Exported span(s)', ['spans' => $request->getResourceSpans()]);

            return true;
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
            self::logWarning('Retryable error exporting grpc span', ['error' => $error]);

            return false;
        }

        self::logError('Error exporting grpc span', ['error' => $error]);

        return false;
    }

    public function setHeader($key, $value): void
    {
        // metadata is supposed to be key-value pairs and the value must be an array
        // @see https://grpc.io/docs/what-is-grpc/core-concepts/#metadata
        if (!is_array($value)) {
            $value = [$value];
        }

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

    public static function fromConnectionString(string $endpointUrl = null, string $name = null, $args = null): Exporter
    {
        return is_string($endpointUrl) ? new Exporter($endpointUrl) :  new Exporter();
    }

    private static function transformToGrpcMetadata(array $metadata): array
    {
        foreach ($metadata as $key => $value) {
            if (!is_array($value)) {
                $metadata[$key] = [$value];
            }
        }

        return $metadata;
    }
}
