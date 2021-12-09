<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\OtlpGrpc;

use grpc;
use Grpc\ChannelCredentials;
use InvalidArgumentException;
use OpenTelemetry\Contrib\Otlp\SpanConverter;
use Opentelemetry\Proto\Collector\Trace\V1\ExportTraceServiceRequest;
use Opentelemetry\Proto\Collector\Trace\V1\TraceServiceClient;
use OpenTelemetry\SDK\EnvironmentVariablesTrait;
use OpenTelemetry\SDK\Trace\Behavior\LoggerAwareTrait;
use OpenTelemetry\SDK\Trace\Behavior\SpanExporterTrait;
use OpenTelemetry\SDK\Trace\Behavior\UsesSpanConverterTrait;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;
use Psr\Log\LoggerAwareInterface;

class Exporter implements SpanExporterInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;
    use EnvironmentVariablesTrait;
    use SpanExporterTrait;
    use UsesSpanConverterTrait;

    // @todo: Please, check if this code is needed. It creates an error in phpstan, since it's not used
    // private $protocol;

    private bool $insecure;

    private string $certificateFile;

    private bool $compression;

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
        // @todo: Please, check if this code is needed. It creates an error in phpstan, since it's not used
        // $this->protocol = getenv('OTEL_EXPORTER_OTLP_PROTOCOL') ?: 'grpc'; // I guess this is redundant?
        $this->insecure = $this->getBooleanFromEnvironment('OTEL_EXPORTER_OTLP_INSECURE', $insecure);
        $this->certificateFile = $this->getStringFromEnvironment('OTEL_EXPORTER_OTLP_CERTIFICATE', $certificateFile);
        $this->compression = $this->getBooleanFromEnvironment('OTEL_EXPORTER_OTLP_COMPRESSION', $compression);
        $this->timeout = $this->getIntFromEnvironment('OTEL_EXPORTER_OTLP_TIMEOUT', $timeout);

        $this->setSpanConverter(new SpanConverter());

        $this->metadata = $this->metadataFromHeaders(
            $this->getStringFromEnvironment('OTEL_EXPORTER_OTLP_HEADERS', $headers)
        );

        $opts = $this->getClientOptions();

        $this->client = $client ?? new TraceServiceClient(
            $this->getStringFromEnvironment('OTEL_EXPORTER_OTLP_ENDPOINT', $endpointURL),
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

        if ($this->compression) {
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

        $this->error('Error exporting span', [
            'error' => $status->details ?? 'unknown grpc error',
            'code' => $status->code,
        ]);
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
            return self::STATUS_FAILED_RETRYABLE;
        }

        return self::STATUS_FAILED_NOT_RETRYABLE;
    }

    public function setHeader($key, $value): void
    {
        $this->metadata[$key] = [$value];
    }

    public function getHeaders(): array
    {
        return $this->metadata;
    }

    public function metadataFromHeaders($headers): array
    {
        if (is_array($headers)) {
            throw new InvalidArgumentException('Configuring Headers Via');
        }

        if (strlen($headers) <= 0) {
            return [];
        }

        $pairs = explode(',', $headers);

        $metadata = [];
        foreach ($pairs as $pair) {
            if (!strpos($pair, '=')) {
                continue;
            }
            [$key, $value] = explode('=', $pair, 2);
            $metadata[$key] = [$value];
        }

        return $metadata;
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

    /** @inheritDoc */
    public static function fromConnectionString(string $endpointUrl, string $name = null, $args = null)
    {
        return new Exporter($endpointUrl);
    }
}
