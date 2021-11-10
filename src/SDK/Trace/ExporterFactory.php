<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\HttpFactory;
use Nyholm\Dsn\DsnParser;
use OpenTelemetry\Contrib\Jaeger\Exporter as JaegerExporter;
use OpenTelemetry\Contrib\Newrelic\Exporter as NewrelicExporter;
use OpenTelemetry\Contrib\OtlpGrpc\Exporter as OtlpGrpcExporter;
use OpenTelemetry\Contrib\OtlpHttp\Exporter as OtlpHttpExporter;
use OpenTelemetry\Contrib\Zipkin\Exporter as ZipkinExporter;
use OpenTelemetry\Contrib\ZipkinToNewrelic\Exporter as ZipkinToNewrelicExporter;
use OpenTelemetry\SDK\Trace\SpanExporter\ConsoleSpanExporter;

class ExporterFactory
{
    private $name;
    private $allowedExporters = ['jaeger' => true, 'zipkin' => true, 'newrelic' => true, 'otlp' => true, 'otlpgrpc' => true, 'otlphttp' => true ,'zipkintonewrelic' => true, 'console' => true];

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
      * Returns the coresponding Exporter via the configuration string
      *
      * @param string $configurationString String containing unextracted information for Exporter creation
      * Should follow the format: contribType+baseUrl?option1=a
      * Query string is optional and based on the Exporter
      */
    public function fromConnectionString(string $configurationString): SpanExporterInterface
    {
        $strArr = explode('+', $configurationString);
        // checks if input is given with the format type+baseUrl
        if (sizeof($strArr) !== 2) {
            throw new Exception('Invalid format.');
        }

        $contribName = strtolower($strArr[0]);
        $endpointUrl = $strArr[1];

        if (!$this->isAllowed($contribName)) {
            throw new Exception('Invalid contrib name.');
        }

        // @phan-suppress-next-line PhanUndeclaredClassMethod
        $dsn = empty($endpointUrl) ? '' : DsnParser::parse($endpointUrl);
        $endpointUrl = $this->parseBaseUrl($dsn);
        // parameters are only retrieved if there was an endpoint given
        $args = empty($dsn) ? [] : $dsn->getParameters();
        $scheme = empty($dsn) ? '' : $dsn->getScheme();

        switch ($contribName) {
            case 'jaeger':
                return JaegerExporter::fromConnectionString($endpointUrl, $this->name);
            case 'zipkin':
                return ZipkinExporter::fromConnectionString($endpointUrl, $this->name);
            case 'newrelic':
                return NewrelicExporter::fromConnectionString($endpointUrl, $this->name, $args['licenseKey'] ?? null);
            case 'otlp':
                switch ($scheme) {
                case 'grpc':
                    return OtlpGrpcExporter::fromConnectionString($endpointUrl);
                case 'http':
                    return OtlpHttpExporter::fromConnectionString($endpointUrl);
                default:
                    throw new Exception('Invalid otlp scheme');
                }
                // no break
            case 'zipkintonewrelic':
                return ZipkinToNewrelicExporter::fromConnectionString($endpointUrl, $this->name, $args['licenseKey'] ?? null);
            case 'console':
                return ConsoleSpanExporter::fromConnectionString($endpointUrl);
            default:
                throw new Exception('Invalid contrib name.');
        }
    }

    public function fromEnvironment(): ?SpanExporterInterface
    {
        $envValue = getenv('OTEL_TRACES_EXPORTER');
        if (!$envValue) {
            throw new Exception('OTEL_TRACES_EXPORTER not set');
        }
        $exporters = explode(',', $envValue);
        //TODO "The SDK MAY accept a comma-separated list to enable setting multiple exporters"
        if (1 !== count($exporters)) {
            throw new Exception('OTEL_TRACES_EXPORTER requires exactly 1 exporter');
        }
        $exporter = $exporters[0];
        switch ($exporter) {
            case 'none':
                return null;
            case 'jaeger':
            case 'zipkin':
            case 'newrelic':
            case 'zipkintonewrelic':
                throw new Exception(sprintf('Exporter %s cannot be created from environment', $exporter));
            case 'otlp':
                $protocol = getenv('OTEL_EXPORTER_OTLP_TRACES_PROTOCOL') ?: getenv('OTEL_EXPORTER_OTLP_PROTOCOL');
                if (!$protocol) {
                    throw new Exception('OTEL_EXPORTER_OTLP_TRACES_PROTOCOL or OTEL_EXPORTER_OTLP_PROTOCOL required');
                }
                switch ($protocol) {
                    case 'grpc':
                        return new OtlpGrpcExporter();
                    case 'http/protobuf':
                        $factory = new HttpFactory();

                        return new OtlpHttpExporter(new Client(), $factory, $factory); //TODO requires discovery
                    case 'http/json':
                        throw new Exception('otlp+http/json not implemented');
                    default:
                        throw new Exception('Unknown protocol: ' . $protocol);
                }
                // no break
            case 'console':
                return new ConsoleSpanExporter();
            default:
                throw new Exception('Invalid exporter name');
        }
    }

    private function isAllowed(string $exporter)
    {
        return array_key_exists($exporter, $this->allowedExporters) && $this->allowedExporters[$exporter];
    }

    // constructs the baseUrl with the arguments retrieved from the raw baseUrl
    private function parseBaseUrl($dsn)
    {
        if ($dsn == false) {
            throw new Exception('Invalid endpoint');
        }
        $parsedUrl = '';
        $parsedUrl .= empty($dsn->getScheme()) ? '' : $dsn->getScheme() . '://';
        $parsedUrl .= empty($dsn->getHost()) ? '' : $dsn->getHost();
        $parsedUrl .= empty($dsn->getPort()) ? '' : ':' . $dsn->getPort();
        $parsedUrl .= empty($dsn->getPath()) ? '' : $dsn->getPath();

        return $parsedUrl;
    }
}
