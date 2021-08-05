<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Trace;

use Exception;
use Nyholm\Dsn\DsnParser;
use OpenTelemetry\Contrib\Jaeger\Exporter as JaegerExporter;
use OpenTelemetry\Contrib\Newrelic\Exporter as NewrelicExporter;
use OpenTelemetry\Contrib\Otlp\Exporter as OtlpExporter;
use OpenTelemetry\Contrib\OtlpGrpc\Exporter as OtlpGrpcExporter;
use OpenTelemetry\Contrib\OtlpHttp\Exporter as OtlpHttpExporter;
use OpenTelemetry\Contrib\Zipkin\Exporter as ZipkinExporter;
use OpenTelemetry\Contrib\ZipkinToNewrelic\Exporter as ZipkinToNewrelicExporter;

class ExporterFactory
{
    private $name;
    private $allowedExporters = ['jaeger' => true, 'zipkin' => true, 'newrelic' => true, 'otlp' => true, 'otlpgrpc' => true, 'otlphttp' => true ,'zipkintonewrelic' => true];

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
    public function fromConnectionString(string $configurationString)
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
                return OtlpExporter::fromConnectionString('', $this->name, $scheme);
            case 'otlpgrpc':
                return OtlpGrpcExporter::fromConnectionString();
            case 'otlphttp':
                return OtlpHttpExporter::fromConnectionString();
            case 'zipkintonewrelic':
                return ZipkinToNewrelicExporter::fromConnectionString($endpointUrl, $this->name, $args['licenseKey'] ?? null);
            default:
                throw new Exception('Invalid contrib name.');
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
