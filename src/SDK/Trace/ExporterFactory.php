<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace;

use InvalidArgumentException;
use OpenTelemetry\SDK\Common\Dsn\DsnInterface;
use OpenTelemetry\SDK\Common\Dsn\Parser;
use OpenTelemetry\SDK\Common\Dsn\ParserInterface;
use OpenTelemetry\SDK\Common\Environment\EnvironmentVariablesTrait;
use OpenTelemetry\SDK\Common\Environment\KnownValues as Values;
use OpenTelemetry\SDK\Common\Environment\Variables as Env;
use OpenTelemetry\SDK\Common\Export\TransportFactoryInterface;
use OpenTelemetry\SDK\Common\Export\TransportInterface;

class ExporterFactory
{
    use EnvironmentVariablesTrait;

    private const KNOWN_EXPORTERS = [
        'console' => '\OpenTelemetry\SDK\Trace\SpanExporter\ConsoleSpanExporter',
        'memory' => '\OpenTelemetry\SDK\Trace\SpanExporter\InMemoryExporter',
        'logger+file' => '\OpenTelemetry\SDK\Trace\SpanExporter\LoggerExporter',
        'jaeger+http' => '\OpenTelemetry\Contrib\Jaeger\Exporter',
        'zipkin+http' => '\OpenTelemetry\Contrib\Zipkin\Exporter',
        'otlp+grpc' => '\OpenTelemetry\Contrib\Otlp\Exporter',
        'otlp+http' => '\OpenTelemetry\Contrib\Otlp\Exporter',
        'newrelic+http' => '\OpenTelemetry\Contrib\Newrelic\Exporter',
        'zipkintonewrelic+http' => '\OpenTelemetry\Contrib\ZipkinToNewrelic\Exporter',
        // this entry exists only for testing purposes
        'test+http' => '\OpenTelemetry\Contrib\Test\Exporter',
    ];
    private const KNOWN_TRANSPORT_FACTORIES = [
        'otlp+grpc' => '\OpenTelemetry\Contrib\Grpc\GrpcTransportFactory',
        'otlp+http' => '\OpenTelemetry\Contrib\OtlpHttp\OtlpHttpTransportFactory',
    ];

    private const DEFAULT_SERVICE_NAME = 'unknown_service';

    private string $serviceName;
    private ParserInterface $parser;

    public function __construct(string $serviceName = self::DEFAULT_SERVICE_NAME, ParserInterface $parser = null)
    {
        $this->serviceName = $serviceName;
        $this->parser = $parser ?? Parser::create();
    }

    /**
      * Returns the corresponding Exporter via the configuration string
      *
      * @param string $exporterDsn String containing information for Exporter creation
      * Should follow the format: type+baseUri?option1=a
      * Query string is optional and based on the Exporter
      */
    public function fromConnectionString(string $exporterDsn): SpanExporterInterface
    {
        if (in_array($exporterDsn, ['console', 'memory'])) {
            return self::buildExporter($exporterDsn);
        }

        $dsn = $this->parser->parse($exporterDsn);

        self::validateProtocol($dsn->getProtocol());

        $endpoint = $dsn->getEndpoint();
        $serviceName = $this->resolveServiceName($dsn);

        if (in_array(self::normalizeProtocol($dsn->getProtocol()), ['newrelic+http', 'zipkintonewrelic+http'])) {
            return self::buildExporter(
                $dsn->getProtocol(),
                $endpoint,
                $serviceName,
                self::getOptionFromDsn($dsn, 'licenseKey')
            );
        }

        return self::buildExporter(
            $dsn->getProtocol(),
            $endpoint,
            $serviceName
        );
    }

    public function fromEnvironment(): ?SpanExporterInterface
    {
        $envValue = $this->getStringFromEnvironment(Env::OTEL_TRACES_EXPORTER, '');
        $exporters = explode(',', $envValue);
        //TODO "The SDK MAY accept a comma-separated list to enable setting multiple exporters"
        if (1 !== count($exporters)) {
            throw new InvalidArgumentException(sprintf('Env Var %s requires exactly 1 exporter', Env::OTEL_TRACES_EXPORTER));
        }
        $exporter = $exporters[0];
        switch ($exporter) {
            case Values::VALUE_NONE:
                return null;
            case Values::VALUE_JAEGER:
            case Values::VALUE_ZIPKIN:
            case Values::VALUE_NEWRELIC:
            case 'zipkintonewrelic':
                throw new InvalidArgumentException(sprintf('Exporter %s cannot be created from environment', $exporter));
            case Values::VALUE_OTLP:
                $protocol = $this->getEnumFromEnvironment(
                    Env::OTEL_EXPORTER_OTLP_PROTOCOL,
                    $this->getEnumFromEnvironment(Env::OTEL_EXPORTER_OTLP_TRACES_PROTOCOL, '')
                );
                switch ($protocol) {
                    case Values::VALUE_GRPC:
                        return self::buildExporterWithTransport('otlp+grpc');
                    case Values::VALUE_HTTP_PROTOBUF:
                        return self::buildExporterWithTransport('otlp+http');
                    case Values::VALUE_HTTP_JSON:
                        throw new InvalidArgumentException('otlp+http/json not implemented');
                    default:
                        throw new InvalidArgumentException('Unknown protocol: ' . $protocol);
                }
                // no break
            case 'console':
                return self::buildExporter('console');
            default:
                throw new InvalidArgumentException(sprintf('Invalid exporter name "%s"', $exporter));
        }
    }

    private function resolveServiceName(DsnInterface $dsn): string
    {
        return self::getOptionFromDsn($dsn, 'serviceName') ?? $this->serviceName;
    }

    private static function getOptionFromDsn(DsnInterface $dsn, string $parameter): ?string
    {
        foreach ([$parameter, strtolower($parameter)] as $name) {
            if (($option = $dsn->getOption($name)) !== null) {
                return $option;
            }
        }

        return null;
    }

    private static function buildExporter($protocol, string $endpoint = null, string $name = null, $args = null): SpanExporterInterface
    {
        $exporterClass = self::KNOWN_EXPORTERS[self::normalizeProtocol($protocol)];
        self::validateClass($exporterClass);

        return call_user_func([$exporterClass, 'fromConnectionString'], $endpoint, $name, $args, $protocol);
    }

    /**
     * @phan-suppress PhanTypeMismatchArgument
     * @phan-suppress PhanUndeclaredClass
     */
    private static function buildExporterWithTransport(string $protocol): SpanExporterInterface
    {
        $exporterClass = self::KNOWN_EXPORTERS[self::normalizeProtocol($protocol)];
        self::validateClass($exporterClass);

        $exporter = new $exporterClass(self::buildTransport($protocol)); // @phpstan-ignore-line
        assert($exporter instanceof SpanExporterInterface);

        return $exporter;
    }

    private static function buildTransport(string $protocol): TransportInterface
    {
        $factoryClass = self::KNOWN_TRANSPORT_FACTORIES[self::normalizeProtocol($protocol)];
        self::validateClass($factoryClass);
        $factory = new $factoryClass();
        assert($factory instanceof TransportFactoryInterface);

        return $factory->create();
    }

    private static function validateProtocol(string $protocol): void
    {
        if (!array_key_exists(self::normalizeProtocol($protocol), self::KNOWN_EXPORTERS)) {
            throw new InvalidArgumentException('Invalid exporter protocol: ' . $protocol);
        }
    }

    private static function validateClass(string $class): void
    {
        if (!class_exists($class)) {
            throw new InvalidArgumentException('Could not find class: ' . $class);
        }
    }

    private static function normalizeProtocol(string $scheme): string
    {
        return str_replace('https', 'http', $scheme);
    }
}
