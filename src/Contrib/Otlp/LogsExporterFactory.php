<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\Otlp;

use OpenTelemetry\API\Signals;
use OpenTelemetry\SDK\Common\Configuration\Configuration;
use OpenTelemetry\SDK\Common\Configuration\Defaults;
use OpenTelemetry\SDK\Common\Configuration\Variables;
use OpenTelemetry\SDK\Common\Export\TransportFactoryInterface;
use OpenTelemetry\SDK\Common\Export\TransportInterface;
use OpenTelemetry\SDK\Logs\LogRecordExporterFactoryInterface;
use OpenTelemetry\SDK\Logs\LogRecordExporterInterface;
use OpenTelemetry\SDK\Registry;

class LogsExporterFactory implements LogRecordExporterFactoryInterface
{
    private const DEFAULT_COMPRESSION = 'none';

    private ?TransportFactoryInterface $transportFactory;

    public function __construct(?TransportFactoryInterface $transportFactory = null)
    {
        $this->transportFactory = $transportFactory;
    }

    /**
     * @psalm-suppress ArgumentTypeCoercion
     */
    public function create(): LogRecordExporterInterface
    {
        $protocol = Configuration::has(Variables::OTEL_EXPORTER_OTLP_LOGS_PROTOCOL)
            ? Configuration::getEnum(Variables::OTEL_EXPORTER_OTLP_LOGS_PROTOCOL)
            : Configuration::getEnum(Variables::OTEL_EXPORTER_OTLP_PROTOCOL);

        return new LogsExporter($this->buildTransport($protocol));
    }

    /**
     * @psalm-suppress UndefinedClass
     */
    private function buildTransport(string $protocol): TransportInterface
    {
        $endpoint = $this->getEndpoint($protocol);

        $headers = OtlpUtil::getHeaders(Signals::LOGS);
        $compression = $this->getCompression();

        $factoryClass = Registry::transportFactory($protocol);
        $factory = $this->transportFactory ?: new $factoryClass();

        return $factory->create(
            $endpoint,
            Protocols::contentType($protocol),
            $headers,
            $compression,
        );
    }

    private function getCompression(): string
    {
        return Configuration::has(Variables::OTEL_EXPORTER_OTLP_LOGS_COMPRESSION) ?
            Configuration::getEnum(Variables::OTEL_EXPORTER_OTLP_LOGS_COMPRESSION) :
            Configuration::getEnum(Variables::OTEL_EXPORTER_OTLP_COMPRESSION, self::DEFAULT_COMPRESSION);
    }

    private function getEndpoint(string $protocol): string
    {
        if (Configuration::has(Variables::OTEL_EXPORTER_OTLP_LOGS_ENDPOINT)) {
            return Configuration::getString(Variables::OTEL_EXPORTER_OTLP_LOGS_ENDPOINT);
        }
        $endpoint = Configuration::has(Variables::OTEL_EXPORTER_OTLP_ENDPOINT)
            ? Configuration::getString(Variables::OTEL_EXPORTER_OTLP_ENDPOINT)
            : Defaults::OTEL_EXPORTER_OTLP_ENDPOINT;
        if ($protocol === Protocols::GRPC) {
            return $endpoint . OtlpUtil::method(Signals::LOGS);
        }

        return HttpEndpointResolver::create()->resolveToString($endpoint, Signals::LOGS);
    }
}
