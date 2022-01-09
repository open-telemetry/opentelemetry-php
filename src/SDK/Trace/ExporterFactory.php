<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace;

use InvalidArgumentException;
use Nyholm\Dsn\Configuration\Dsn;
use Nyholm\Dsn\Configuration\Url;
use Nyholm\Dsn\DsnParser;
use OpenTelemetry\SDK\EnvironmentVariablesTrait;

class ExporterFactory
{
    use EnvironmentVariablesTrait;

    private const KNOWN_EXPORTERS = [
        'console' => '\OpenTelemetry\SDK\Trace\SpanExporter\ConsoleSpanExporter',
        'logger+file' => '\OpenTelemetry\SDK\Trace\SpanExporter\LoggerExporter',
        'jaeger+http' => '\OpenTelemetry\Contrib\Jaeger\Exporter',
        'zipkin+http' => '\OpenTelemetry\Contrib\Zipkin\Exporter',
        'otlp+grpc' => '\OpenTelemetry\Contrib\OtlpGrpc\Exporter',
        'otlp+http' => '\OpenTelemetry\Contrib\OtlpHttp\Exporter',
        'newrelic+http' => '\OpenTelemetry\Contrib\Newrelic\Exporter',
        'zipkintonewrelic+http' => '\OpenTelemetry\Contrib\ZipkinToNewrelic\Exporter',
        // this entry exists only for testing purposes
        'test+http' => '\OpenTelemetry\Contrib\Test\Exporter',
    ];

    private const DEFAULT_SERVICE_NAME = 'unknown_service';

    private string $serviceName;

    public function __construct(string $serviceName = self::DEFAULT_SERVICE_NAME)
    {
        $this->serviceName = $serviceName;
    }

    /**
      * Returns the corresponding Exporter via the configuration string
      *
      * @param string $connectionString String containing information for Exporter creation
      * Should follow the format: type+baseUri?option1=a
      * Query string is optional and based on the Exporter
      */
    public function fromConnectionString(string $connectionString): SpanExporterInterface
    {
        if (in_array($connectionString, ['console', 'otlp+http'])) {
            return self::buildExporter($connectionString);
        }

        $dsn = DsnParser::parseUrl($connectionString);

        self::validateScheme((string) $dsn->getScheme());

        $endpoint = self::getEndpointFromDsn($dsn);
        $serviceName = $this->resolveServiceName($dsn);

        if (in_array(self::normalizeScheme((string) $dsn->getScheme()), ['newrelic+http', 'zipkintonewrelic+http'])) {
            return self::buildExporter(
                (string) $dsn->getScheme(),
                $endpoint,
                $serviceName,
                self::getParameterFromDsn($dsn, 'licenseKey')
            );
        }

        return self::buildExporter(
            (string) $dsn->getScheme(),
            $endpoint,
            $serviceName
        );
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
                        return self::buildExporter('otlp+grpc');
                    case 'http/protobuf':
                        return self::buildExporter('otlp+http');
                    case 'http/json':
                        throw new InvalidArgumentException('otlp+http/json not implemented');
                    default:
                        throw new InvalidArgumentException('Unknown protocol: ' . $protocol);
                }
                // no break
            case 'console':
                return self::buildExporter('console');
            default:
                throw new InvalidArgumentException('Invalid exporter name');
        }
    }

    private function resolveServiceName(Dsn $dsn): string
    {
        return self::getParameterFromDsn($dsn, 'serviceName') ?? $this->serviceName;
    }

    private static function getParameterFromDsn(Dsn $dsn, string $parameter): ?string
    {
        $parameters = $dsn->getParameters();

        foreach ([$parameter, strtolower($parameter)] as $name) {
            if (array_key_exists($name, $parameters)) {
                return $parameters[$name];
            }
        }

        return null;
    }

    private static function getEndpointFromDsn(Url $dsn): string
    {
        return (string) new Url(
            self::getProtocolFromScheme((string) $dsn->getScheme()),
            $dsn->getHost(),
            $dsn->getPort(),
            $dsn->getPath(),
            [],
            $dsn->getAuthentication()
        );
    }

    private static function buildExporter(string $scheme, string $endpoint = null, string $name = null, $args = null): SpanExporterInterface
    {
        $exporterClass = self::KNOWN_EXPORTERS[self::normalizeScheme($scheme)];
        self::validateExporterClass($exporterClass);

        return call_user_func([$exporterClass, 'fromConnectionString'], $endpoint, $name, $args);
    }

    private static function validateScheme(string $scheme)
    {
        if (!array_key_exists(self::normalizeScheme($scheme), self::KNOWN_EXPORTERS)) {
            throw new InvalidArgumentException('Invalid exporter scheme: ' . $scheme);
        }
    }

    private static function validateExporterClass(string $class)
    {
        if (!class_exists($class)) {
            throw new InvalidArgumentException('Could not find exporter class: ' . $class);
        }
    }

    private static function getProtocolFromScheme(string $scheme): string
    {
        $components = explode('+', $scheme);

        return count($components) === 1 ? $components[0] : $components[1];
    }

    private static function normalizeScheme(string $scheme): string
    {
        return str_replace('https', 'http', $scheme);
    }
}
