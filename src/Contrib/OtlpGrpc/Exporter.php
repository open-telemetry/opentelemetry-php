<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\OtlpGrpc;

use grpc;
use InvalidArgumentException;
use OpenTelemetry\Contrib\Otlp\ConfigOpts;
use Opentelemetry\Proto\Collector\Trace\V1\ExportTraceServiceRequest;
use Opentelemetry\Proto\Collector\Trace\V1\TraceServiceClient;
use OpenTelemetry\SDK\EnvironmentVariablesTrait;
use OpenTelemetry\SDK\Trace;
use OpenTelemetry\SDK\Trace\Behavior\SpanExporterTrait;

class Exporter implements Trace\SpanExporterInterface
{
    use EnvironmentVariablesTrait;
    use SpanExporterTrait;

    private $protocol;
    private bool $insecure;
    private string $certificateFile;
    private string $compression;
    private int $timeout;
    private SpanConverter $spanConverter;
    private array $metadata;
    private TraceServiceClient $client;

    /**
     * OTLP GRPC Exporter Constructor
     */
    public function __construct(
        ConfigOpts $config = null
    ) {
        if (null === $config) {
            $config = new ConfigOpts();
        }
        $this->insecure = $config->getInsecure();
        $this->certificateFile = $config->getCertificateFile();
        $this->compression = $config->getCompression();
        $this->timeout = $config->getTimeout();
        $this->setMetadata($config->getHeaders());
        $opts = $this->getClientOptions();
        $this->client = $config->getGrpcTraceServiceClient() ?? new TraceServiceClient($config->getEndpoint(), $opts);
        $this->protocol = $config->getProtocol();
        if ($this->protocol !== 'grpc') {
            throw new InvalidArgumentException('Invalid OTLP Protocol Specified');
        }
        $this->spanConverter = new SpanConverter();
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

        if ($this->compression === 'gzip') {
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
        $resourceSpans = [$this->spanConverter->as_otlp_resource_span($spans)];

        $request = new ExportTraceServiceRequest([
            'resource_spans' => $resourceSpans,
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

    private function setMetadata(array $headers): void
    {
        $metadata = [];
        foreach ($headers as $key => $value) {
            $metadata[$key] = [$value];
        }
        $this->metadata = $metadata;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }

    private function getCredentials(): ?\Grpc\ChannelCredentials
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
        return new Exporter((new ConfigOpts())->withEndpoint($endpointUrl)->withProtocol('grpc'));
    }
}
