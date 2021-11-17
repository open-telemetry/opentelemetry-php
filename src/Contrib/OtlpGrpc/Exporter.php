<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\OtlpGrpc;

use grpc;
use InvalidArgumentException;
use Opentelemetry\Proto\Collector\Trace\V1\ExportTraceServiceRequest;
use Opentelemetry\Proto\Collector\Trace\V1\TraceServiceClient;
use OpenTelemetry\SDK\EnvironmentVariablesTrait;
use OpenTelemetry\SDK\Trace;

class Exporter implements Trace\SpanExporterInterface
{
    use EnvironmentVariablesTrait;

    /**
     * @var string
     */
    private $endpointURL;

    /**
     * @var string
     */
    // @todo: Please, check if this code is needed. It creates an error in phpstan, since it's not used
    // private $protocol;

    /**
     * @var bool|string
     */
    private $insecure;

    /**
     * @var string
     */
    private $certificateFile;

    /**
     * @var string
     */
    private $headers;

    /**
     * @var bool|string
     */
    private $compression;

    /**
     * @var int
     */
    private $timeout;
    /**
     * @var SpanConverter
     */
    private $spanConverter;

    private $metadata;

    /**
    * @var bool
    */
    private $running = true;

    /**
     * @var TraceServiceClient
     */

    private $client;

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
        $this->endpointURL = $this->getStringFromEnvironment('OTEL_EXPORTER_OTLP_ENDPOINT', $endpointURL);
        // @todo: Please, check if this code is needed. It creates an error in phpstan, since it's not used
        // $this->protocol = getenv('OTEL_EXPORTER_OTLP_PROTOCOL') ?: 'grpc'; // I guess this is redundant?
        $this->insecure = $this->getBooleanFromEnvironment('OTEL_EXPORTER_OTLP_INSECURE', $insecure);
        $this->certificateFile = $this->getStringFromEnvironment('OTEL_EXPORTER_OTLP_CERTIFICATE', $certificateFile);
        $this->headers = $this->getStringFromEnvironment('OTEL_EXPORTER_OTLP_HEADERS', $headers);
        $this->compression = $this->getBooleanFromEnvironment('OTEL_EXPORTER_OTLP_COMPRESSION', $compression);
        $this->timeout = $this->getIntFromEnvironment('OTEL_EXPORTER_OTLP_TIMEOUT', $timeout);

        $this->spanConverter = new SpanConverter();

        $this->metadata = $this->metadataFromHeaders($this->headers);

        $opts = $this->getClientOptions();

        $this->client = $client ?? new TraceServiceClient($this->endpointURL, $opts);
    }

    /**
     * @psalm-suppress UndefinedClass
     * @psalm-suppress UndefinedConstant
     */
    public function getClientOptions(): array
    {
        $opts = [
            'update_metadata' => function () {
                return $this->metadata;
            },
            'timeout' => $this->timeout,
        ];

        if (!$this->insecure && !$this->certificateFile) {
            // Assumed default
            $opts['credentials'] = Grpc\ChannelCredentials::createSsl('');
        } elseif (!$this->insecure && $this->certificateFile !== '') {
            // Should we validate more?
            $opts['credentials'] = Grpc\ChannelCredentials::createSsl(file_get_contents($this->certificateFile));
        } else {
            $opts['credentials'] = Grpc\ChannelCredentials::createInsecure();
        }

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
    public function export(iterable $spans): int
    {
        if (!$this->running) {
            return self::STATUS_FAILED_NOT_RETRYABLE;
        }

        if (empty($spans)) {
            return self::STATUS_SUCCESS;
        }

        $resourcespans = [$this->spanConverter->as_otlp_resource_span($spans)];

        $request = new ExportTraceServiceRequest([
            'resource_spans' => $resourcespans,
        ]);

        [$response, $status] = $this->client->Export($request)->wait();

        if ($status->code === \Grpc\STATUS_OK) {
            return self::STATUS_SUCCESS;
        }

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

    public function setHeader($key, $value)
    {
        $this->metadata[$key] = [$value];
    }

    public function getHeaders()
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

    /** @inheritDoc */
    public function shutdown(): bool
    {
        $this->running = false;

        return true;
    }

    /** @inheritDoc */
    public function forceFlush(): bool
    {
        return true;
    }

    /** @inheritDoc */
    public static function fromConnectionString(string $endpointUrl = null, string $name = null, $args = null)
    {
        return new Exporter();
    }
}
