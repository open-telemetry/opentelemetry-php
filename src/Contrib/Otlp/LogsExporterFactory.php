<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\Otlp;

use OpenTelemetry\API\Signals;
use OpenTelemetry\SDK\Common\Configuration\Configuration;
use OpenTelemetry\SDK\Common\Configuration\Variables;
use OpenTelemetry\SDK\Common\Export\TransportFactoryInterface;
use OpenTelemetry\SDK\Common\Export\TransportInterface;
use OpenTelemetry\SDK\Logs\LogRecordExporterFactoryInterface;
use OpenTelemetry\SDK\Logs\LogRecordExporterInterface;
use OpenTelemetry\SDK\Registry;
use function parse_url;
use const PHP_URL_SCHEME;

class LogsExporterFactory implements LogRecordExporterFactoryInterface
{
    private const DEFAULT_COMPRESSION = 'none';

    public function __construct(private readonly ?TransportFactoryInterface $transportFactory = null)
    {
    }

    /**
     * @psalm-suppress ArgumentTypeCoercion
     */
    #[\Override]
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
        $timeout = $this->getTimeout();

        $cacert = OtlpUtil::getStringVar(Variables::OTEL_EXPORTER_OTLP_LOGS_CERTIFICATE, Variables::OTEL_EXPORTER_OTLP_CERTIFICATE);
        $cert = OtlpUtil::getStringVar('OTEL_EXPORTER_OTLP_LOGS_CERTIFICATE', 'OTEL_EXPORTER_OTLP_CERTIFICATE');
        $key = OtlpUtil::getStringVar('OTEL_EXPORTER_OTLP_LOGS_CLIENT_KEY', 'OTEL_EXPORTER_OTLP_CLIENT_KEY');

        $factoryClass = Registry::transportFactory($protocol);
        $factory = $this->transportFactory ?: new $factoryClass();

        return $factory->create(
            $endpoint,
            Protocols::contentType($protocol),
            $headers,
            $compression,
            $timeout,
            cacert: $cacert,
            cert: $cert,
            key: $key,
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
        if ($protocol === Protocols::GRPC) {
            $endpoint = OtlpUtil::getStringVar(
                Variables::OTEL_EXPORTER_OTLP_LOGS_ENDPOINT,
                Variables::OTEL_EXPORTER_OTLP_ENDPOINT,
            ) ?? 'http://localhost:4317';

            if (parse_url($endpoint, PHP_URL_SCHEME) === null) {
                $insecure = OtlpUtil::getBoolVar(
                    Variables::OTEL_EXPORTER_OTLP_LOGS_INSECURE,
                    Variables::OTEL_EXPORTER_OTLP_INSECURE,
                ) ?? false;
                $endpoint = $insecure
                    ? 'http://' . $endpoint
                    : 'https://' . $endpoint;
            }

            return $endpoint . OtlpUtil::method(Signals::LOGS);
        }

        if (Configuration::has(Variables::OTEL_EXPORTER_OTLP_LOGS_ENDPOINT)) {
            return Configuration::getString(Variables::OTEL_EXPORTER_OTLP_LOGS_ENDPOINT);
        }

        $endpoint = Configuration::getString(Variables::OTEL_EXPORTER_OTLP_ENDPOINT);

        return HttpEndpointResolver::create()->resolveToString($endpoint, Signals::LOGS);
    }

    private function getTimeout(): float
    {
        $value = Configuration::has(Variables::OTEL_EXPORTER_OTLP_LOGS_TIMEOUT) ?
            Configuration::getInt(Variables::OTEL_EXPORTER_OTLP_LOGS_TIMEOUT) :
            Configuration::getInt(Variables::OTEL_EXPORTER_OTLP_TIMEOUT);

        return $value/1000;
    }
}
