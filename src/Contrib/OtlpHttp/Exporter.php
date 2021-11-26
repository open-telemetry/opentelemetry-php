<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\OtlpHttp;

use InvalidArgumentException;
use Nyholm\Dsn\DsnParser;
use OpenTelemetry\Contrib\Otlp\ConfigOpts;
use Opentelemetry\Proto\Collector\Trace\V1\ExportTraceServiceRequest;
use OpenTelemetry\SDK\EnvironmentVariablesTrait;
use OpenTelemetry\SDK\Trace;
use OpenTelemetry\SDK\Trace\Behavior\HttpSpanExporterTrait;
use OpenTelemetry\SDK\Trace\Behavior\UsesSpanConverterTrait;
use Psr\Http\Message\RequestInterface;

class Exporter implements Trace\SpanExporterInterface
{
    use EnvironmentVariablesTrait;
    use UsesSpanConverterTrait;
    use HttpSpanExporterTrait;

    private const REQUEST_METHOD = 'POST';
    private const HEADER_CONTENT_TYPE = 'content-type';
    private const HEADER_CONTENT_ENCODING = 'Content-Encoding';
    private const VALUE_CONTENT_TYPE = 'application/x-protobuf';
    private const VALUE_CONTENT_ENCODING = 'gzip';

    // @todo: Please, check if this code is needed. It creates an error in phpstan, since it's not used
    // private string $certificateFile;

    private array $headers;
    private string $compression;
    private string $protocol;

    // @todo: Please, check if this code is needed. It creates an error in phpstan, since it's not used
    // private int $timeout;

    private SpanConverter $spanConverter;

    /**
     * Exporter constructor.
     */
    public function __construct(
        ConfigOpts $config = null
    ) {
        if (null === $config) {
            $config = new ConfigOpts();
        }
        $this->setClient($config->getHttpClient());
        $this->setRequestFactory($config->getHttpRequestFactory());
        $this->setStreamFactory($config->getHttpStreamFactory());
        $this->setEndpointUrl($this->validateEndpoint($config->getEndpoint()));
        // @todo: Please, check if this code is needed. It creates an error in phpstan, since it's not used
        // $this->certificateFile = getenv('OTEL_EXPORTER_OTLP_CERTIFICATE') ?: 'none';
        $this->headers = $config->getHeaders();
        $this->compression = $config->getCompression();
        $this->protocol = $config->getProtocol();
        // @todo: Please, check if this code is needed. It creates an error in phpstan, since it's not used
        // $this->timeout =(int) getenv('OTEL_EXPORTER_OTLP_TIMEOUT') ?: 10;

        $this->setSpanConverter(new SpanConverter());

        if ($this->protocol !== 'http/protobuf') {
            throw new InvalidArgumentException('Invalid OTLP Protocol Specified');
        }
    }

    protected function serializeTrace(iterable $spans): string
    {
        $bytes = (new ExportTraceServiceRequest([
            'resource_spans' => [$this->spanConverter->as_otlp_resource_span($spans)],
        ]))->serializeToString();

        // TODO: Add Tests
        return $this->shouldCompress()
            ? gzencode($bytes)
            : $bytes;
    }

    protected function marshallRequest(iterable $spans): RequestInterface
    {
        $request =  $this->createRequest(self::REQUEST_METHOD)
            ->withBody(
                $this->createStream(
                    $this->serializeTrace($spans)
                )
            )
            ->withHeader(self::HEADER_CONTENT_TYPE, self::VALUE_CONTENT_TYPE);

        foreach ($this->headers as $header => $value) {
            $request = $request->withHeader($header, $value);
        }

        if ($this->shouldCompress()) {
            return $request->withHeader(self::HEADER_CONTENT_ENCODING, self::VALUE_CONTENT_ENCODING);
        }

        return $request;
    }

    /**
     * validateEndpoint does two functions, firstly checks that the endpoint is valid
     *  secondly it appends https:// and /v1/traces should they have been omitted
     */
    private function validateEndpoint(string $endpoint): string
    {
        $dsn = DsnParser::parseUrl($endpoint);

        if ($dsn->getScheme() === null) {
            $dsn = $dsn->withScheme('https');
        } elseif (!($dsn->getScheme() === 'https' || $dsn->getScheme() === 'http')) {
            throw new InvalidArgumentException('Expected scheme of http or https, given: ' . $dsn->getScheme());
        }

        if ($dsn->getPath() === null) {
            return (string) $dsn->withPath('/v1/traces');
        }

        return (string) $dsn;
    }

    /** @inheritDoc */
    public static function fromConnectionString(string $endpointUrl, string $name = null, $args = null)
    {
        return new Exporter(
            (new ConfigOpts())->withEndpoint($endpointUrl),
        );
    }

    public function setSpanConverter(SpanConverter $spanConverter): void
    {
        $this->spanConverter = $spanConverter;
    }

    private function shouldCompress(): bool
    {
        return $this->compression === 'gzip' && function_exists('gzencode');
    }
}
