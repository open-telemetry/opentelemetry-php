<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace;

use InvalidArgumentException;
use Nyholm\Dsn\DsnParser;
use OpenTelemetry\Contrib\Jaeger\Exporter as JaegerExporter;
use OpenTelemetry\Contrib\Newrelic\Exporter as NewrelicExporter;
use OpenTelemetry\Contrib\OtlpGrpc\Exporter as OtlpGrpcExporter;
use OpenTelemetry\Contrib\OtlpHttp\Exporter as OtlpHttpExporter;
use OpenTelemetry\Contrib\Zipkin\Exporter as ZipkinExporter;
use OpenTelemetry\Contrib\ZipkinToNewrelic\Exporter as ZipkinToNewrelicExporter;
use OpenTelemetry\SDK\EnvironmentVariablesTrait;
use OpenTelemetry\SDK\Trace\Behavior\LoggerAwareTrait;
use OpenTelemetry\SDK\Trace\SpanExporter\ConsoleSpanExporter;

class ExporterFactory
{
    use EnvironmentVariablesTrait;
    use LoggerAwareTrait;

    private string $name;
    private array $allowedExporters = ['jaeger' => true, 'zipkin' => true, 'newrelic' => true, 'otlp' => true, 'otlpgrpc' => true, 'otlphttp' => true ,'zipkintonewrelic' => true, 'console' => true];

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
      * Returns the corresponding Exporter via the configuration string
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
            throw new InvalidArgumentException('Invalid format.');
        }

        $contribName = strtolower($strArr[0]);
        $endpointUrl = $strArr[1];

        if (!$this->isAllowed($contribName)) {
            throw new InvalidArgumentException('Invalid contrib name.');
        }

        // @phan-suppress-next-line PhanUndeclaredClassMethod
        $dsn = empty($endpointUrl) ? '' : DsnParser::parse($endpointUrl);
        $endpointUrl = $this->parseBaseUrl($dsn);
        // parameters are only retrieved if there was an endpoint given
        $args = empty($dsn) ? [] : $dsn->getParameters();
        $scheme = empty($dsn) ? '' : $dsn->getScheme();
        $exporter = null;

        switch ($contribName) {
            case 'jaeger':
                return $this->injectLogger(JaegerExporter::fromConnectionString($endpointUrl, $this->name));
            case 'zipkin':
                return $this->injectLogger(ZipkinExporter::fromConnectionString($endpointUrl, $this->name));
            case 'newrelic':
                return $this->injectLogger(NewrelicExporter::fromConnectionString($endpointUrl, $this->name, $args['licenseKey'] ?? null));
            case 'otlp':
                switch ($scheme) {
                case 'grpc':
                    return $this->injectLogger(OtlpGrpcExporter::fromConnectionString($endpointUrl));
                case 'http':
                    return $this->injectLogger(OtlpHttpExporter::fromConnectionString($endpointUrl));
                default:
                    throw new InvalidArgumentException('Invalid otlp scheme');
                }
                // no break
            case 'zipkintonewrelic':
                return $this->injectLogger(ZipkinToNewrelicExporter::fromConnectionString($endpointUrl, $this->name, $args['licenseKey'] ?? null));
            case 'console':
                return $this->injectLogger(ConsoleSpanExporter::fromConnectionString($endpointUrl));
            default:
                throw new InvalidArgumentException('Invalid contrib name.');
        }
    }

    public function fromEnvironment(): ?SpanExporterInterface
    {
        $envValue = $this->getStringFromEnvironment('OTEL_TRACES_EXPORTER', '');
        if (!$envValue) {
            throw new InvalidArgumentException('OTEL_TRACES_EXPORTER not set');
        }
        $exporters = explode(',', $envValue);
        //TODO "The SDK MAY accept a comma-separated list to enable setting multiple exporters"
        if (1 !== count($exporters)) {
            throw new InvalidArgumentException('OTEL_TRACES_EXPORTER requires exactly 1 exporter');
        }
        $exporter = $exporters[0];
        switch ($exporter) {
            case 'none':
                return null;
            case 'jaeger':
            case 'zipkin':
            case 'newrelic':
            case 'zipkintonewrelic':
                throw new InvalidArgumentException(sprintf('Exporter %s cannot be created from environment', $exporter));
            case 'otlp':
                $protocol = getenv('OTEL_EXPORTER_OTLP_TRACES_PROTOCOL') ?: getenv('OTEL_EXPORTER_OTLP_PROTOCOL');
                if (!$protocol) {
                    throw new InvalidArgumentException('OTEL_EXPORTER_OTLP_TRACES_PROTOCOL or OTEL_EXPORTER_OTLP_PROTOCOL required');
                }
                switch ($protocol) {
                    case 'grpc':
                        return (new OtlpGrpcExporter())->withLogger($this->getLogger());
                    case 'http/protobuf':
                        return OtlpHttpExporter::create()->withLogger($this->getLogger());
                    case 'http/json':
                        throw new InvalidArgumentException('otlp+http/json not implemented');
                    default:
                        throw new InvalidArgumentException('Unknown protocol: ' . $protocol);
                }
                // no break
            case 'console':
                return (new ConsoleSpanExporter())->withLogger($this->getLogger());
            default:
                throw new InvalidArgumentException('Invalid exporter name');
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
            throw new InvalidArgumentException('Invalid endpoint');
        }
        $parsedUrl = '';
        $parsedUrl .= empty($dsn->getScheme()) ? '' : $dsn->getScheme() . '://';
        $parsedUrl .= empty($dsn->getHost()) ? '' : $dsn->getHost();
        $parsedUrl .= empty($dsn->getPort()) ? '' : ':' . $dsn->getPort();
        $parsedUrl .= empty($dsn->getPath()) ? '' : $dsn->getPath();

        return $parsedUrl;
    }
}
