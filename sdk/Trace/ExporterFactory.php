<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Trace;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\HttpFactory;
use Nyholm\Dsn\DsnParser;
use OpenTelemetry\Contrib\Jaeger\Exporter as JaegerExporter;
use OpenTelemetry\Contrib\Newrelic\Exporter as NewrelicExporter;
use OpenTelemetry\Contrib\Otlp\Exporter as OtlpExporter;
use OpenTelemetry\Contrib\OtlpGrpc\Exporter as OtlpGrpcExporter;
use OpenTelemetry\Contrib\Zipkin\Exporter as ZipkinExporter;
use OpenTelemetry\Contrib\ZipkinToNewrelic\Exporter as ZipkinToNewrelicExporter;

class ExporterFactory
{
    private $name;
    private $allowedExporters = ['jaeger' => true, 'zipkin' => true, 'newrelic' => true, 'otlp' => true, 'otlpgrpc' => true, 'zipkintonewrelic' => true];

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
        if (sizeof($strArr) != 2) {
            return null;
        }

        $contribName = strtolower($strArr[0]);
        $endpointUrl = $strArr[1];

        if (!$this->isAllowed($contribName)) {
            return null;
        }

        // endpoint is only parsed if it is provided
        $dsn = empty($endpointUrl) ? '' : DsnParser::parse($endpointUrl);
        $endpointUrl = $this->parseBaseUrl($dsn);
        // parameters are only retrieved if there was an endpoint given
        $args = empty($dsn) ? [] : $dsn->getParameters();
        
        switch ($contribName) {
            case 'jaeger':
                return $exporter = $this->generateJaeger($endpointUrl);
            case 'zipkin':
                return $exporter = $this->generateZipkin($endpointUrl);
            case 'newrelic':
                return $exporter = $this->generateNewrelic($endpointUrl, $args['licenseKey'] ?? null);
            case 'otlp':
                return $exporter = $this->generateOtlp();
            case 'otlpgrpc':
                return $exporter = $this->generateOtlpGrpc();
            case 'zipkintonewrelic':
                return $exporter = $this->generateZipkinToNewrelic($endpointUrl, $args['licenseKey'] ?? null);
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
            return null;
        }
        $parsedUrl = '';
        $parsedUrl .= empty($dsn->getScheme()) ? '' : $dsn->getScheme() . '://';
        $parsedUrl .= empty($dsn->getHost()) ? '' : $dsn->getHost();
        $parsedUrl .= empty($dsn->getPort()) ? '' : ':' . $dsn->getPort();
        $parsedUrl .= empty($dsn->getPath()) ? '' : $dsn->getPath();

        return $parsedUrl;
    }

    private function generateJaeger(string $endpointUrl)
    {
        $exporter = new JaegerExporter(
            $this->name,
            $endpointUrl,
            new Client(),
            new HttpFactory(),
            new HttpFactory()
        );

        return $exporter;
    }
    private function generateZipkin(string $endpointUrl)
    {
        $exporter = new ZipkinExporter(
            $this->name,
            $endpointUrl,
            new Client(),
            new HttpFactory(),
            new HttpFactory()
        );

        return $exporter;
    }
    private function generateNewrelic(string $endpointUrl, $licenseKey)
    {
        if ($licenseKey == false) {
            return null;
        }
        $exporter = new NewrelicExporter(
            $this->name,
            $endpointUrl,
            $licenseKey,
            new Client(),
            new HttpFactory(),
            new HttpFactory()
        );

        return $exporter;
    }

    private function generateOtlp()
    {
        $exporter = new OtlpExporter(
            $this->name,
            new Client(),
            new HttpFactory(),
            new HttpFactory()
        );

        return $exporter;
    }

    private function generateOtlpGrpc()
    {
        return new OtlpGrpcExporter();
    }
   
    private function generateZipkinToNewrelic(string $endpointUrl, $licenseKey)
    {
        if ($licenseKey == false) {
            return null;
        }
        $exporter = new ZipkinToNewrelicExporter(
            $this->name,
            $endpointUrl,
            $licenseKey,
            new Client(),
            new HttpFactory(),
            new HttpFactory()
        );

        return $exporter;
    }
}
