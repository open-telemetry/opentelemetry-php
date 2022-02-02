<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\OtlpGrpc;

use grpc;
use Grpc\ChannelCredentials;
use OpenTelemetry\Contrib\Otlp\ExporterTrait;
use OpenTelemetry\Contrib\Otlp\SpanConverter;
use Opentelemetry\Proto\Collector\Trace\V1\ExportTraceServiceRequest;
use Opentelemetry\Proto\Collector\Trace\V1\TraceServiceClient;
use OpenTelemetry\SDK\Common\Environment\KnownValues as Values;
use OpenTelemetry\SDK\Common\Environment\Resolver as EnvResolver;
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
        // Set default values based on presence of env variable
        $this->insecure = $this->getBooleanFromEnvironment(Env::OTEL_EXPORTER_OTLP_INSECURE, $insecure);
        if (!empty($certificateFile) || EnvResolver::hasVariable(Env::OTEL_EXPORTER_OTLP_CERTIFICATE)) {
            $this->certificateFile = $this->getStringFromEnvironment(Env::OTEL_EXPORTER_OTLP_CERTIFICATE, $certificateFile);
        }
        $this->compression = $this->getEnumFromEnvironment(
            Env::OTEL_EXPORTER_OTLP_COMPRESSION,
            $compression ? Values::VALUE_GZIP : Values::VALUE_NONE
        );
        $this->timeout = $this->getIntFromEnvironment(Env::OTEL_EXPORTER_OTLP_TIMEOUT, $timeout);

        $this->setSpanConverter(new SpanConverter());

        $this->metadata = $this->getMapFromEnvironment(Env::OTEL_EXPORTER_OTLP_HEADERS, $headers);

        $opts = $this->getClientOptions();

        $this->client = $client ?? new TraceServiceClient(
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
     * @psalm-suppress UndefinedConstant
     */
    protected function doExport(iterable $spans): int
    {
        $resourceSpans = $this->getSpanConverter()->convert($spans);

        $request = new ExportTraceServiceRequest([
            'resource_spans' => $resourceSpans,
        ]);

        [$response, $status] = $this->client->Export($request)->wait();

        if ($status->code === \Grpc\STATUS_OK) {
            return self::STATUS_SUCCESS;
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
            $this->logWarning('Retryable error exporting grpc span', ['error' => $error]);

            return self::STATUS_FAILED_RETRYABLE;
        }

        $this->logError('Error exporting grpc span', ['error' => $error]);

        return self::STATUS_FAILED_NOT_RETRYABLE;
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

    private function getCredentials(): ChannelCredentials
    {
        if (!$this->insecure) {
            return $this->certificateFile !== ''
                ? Grpc\ChannelCredentials::createSsl(file_get_contents($this->certificateFile))
                : Grpc\ChannelCredentials::createSsl('');
        }

        return Grpc\ChannelCredentials::createInsecure();
    }

    public static function fromConnectionString(string $endpointUrl = null, string $name = null, $args = null): Exporter
    {
        return is_string($endpointUrl) ? new Exporter($endpointUrl) :  new Exporter();
    }
}
