<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\OtlpHttp;

use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;
use InvalidArgumentException;
use OpenTelemetry\API\Common\Signal\Signals;
use OpenTelemetry\Contrib\Otlp\SpanConverter;
use Opentelemetry\Proto\Collector\Trace\V1\ExportTraceServiceResponse;
use OpenTelemetry\SDK\Behavior\LogsMessagesTrait;
use OpenTelemetry\SDK\Common\Environment\EnvironmentVariablesTrait;
use OpenTelemetry\SDK\Common\Environment\Variables as Env;
use OpenTelemetry\SDK\Common\Export\Http\PsrTransportFactory;
use OpenTelemetry\SDK\Common\Export\TransportFactoryInterface;
use OpenTelemetry\SDK\Common\Export\TransportInterface;
use OpenTelemetry\SDK\Common\Future\CancellationInterface;
use OpenTelemetry\SDK\Common\Future\FutureInterface;
use OpenTelemetry\SDK\Common\Otlp\HttpEndpointResolver;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;
use function sprintf;
use Throwable;

class Exporter implements SpanExporterInterface
{
    use LogsMessagesTrait;

    private const DEFAULT_ENDPOINT = 'https://localhost:4318';
    private const DEFAULT_COMPRESSION = 'none';
    private const OTLP_PROTOCOL = 'http/protobuf';

    private TransportInterface $transport;

    /**
     * Exporter constructor.
     */
    public function __construct(TransportInterface $transport)
    {
        $this->transport = $transport;
    }

    /** @internal */
    public static function createTransport(
        TransportFactoryInterface $transportFactory
    ): TransportInterface {
        $env = new class() {
            use EnvironmentVariablesTrait;
        };

        $endpoint = $env->hasEnvironmentVariable(Env::OTEL_EXPORTER_OTLP_TRACES_ENDPOINT)
            ? $env->getStringFromEnvironment(Env::OTEL_EXPORTER_OTLP_TRACES_ENDPOINT)
            : HttpEndpointResolver::create()->resolveToString(
                $env->getStringFromEnvironment(Env::OTEL_EXPORTER_OTLP_ENDPOINT, self::DEFAULT_ENDPOINT),
                Signals::TRACE,
            );

        $headers = $env->hasEnvironmentVariable(Env::OTEL_EXPORTER_OTLP_TRACES_HEADERS) ?
            $env->getMapFromEnvironment(Env::OTEL_EXPORTER_OTLP_TRACES_HEADERS) :
            $env->getMapFromEnvironment(Env::OTEL_EXPORTER_OTLP_HEADERS);

        $compression = $env->hasEnvironmentVariable(Env::OTEL_EXPORTER_OTLP_TRACES_COMPRESSION) ?
            $env->getEnumFromEnvironment(Env::OTEL_EXPORTER_OTLP_TRACES_COMPRESSION, self::DEFAULT_COMPRESSION) :
            $env->getEnumFromEnvironment(Env::OTEL_EXPORTER_OTLP_COMPRESSION, self::DEFAULT_COMPRESSION);

        $protocol = $env->hasEnvironmentVariable(Env::OTEL_EXPORTER_OTLP_TRACES_PROTOCOL) ?
            $env->getEnumFromEnvironment(Env::OTEL_EXPORTER_OTLP_TRACES_PROTOCOL, self::OTLP_PROTOCOL) :
            $env->getEnumFromEnvironment(Env::OTEL_EXPORTER_OTLP_PROTOCOL, self::OTLP_PROTOCOL);

        if ($protocol !== self::OTLP_PROTOCOL) {
            throw new InvalidArgumentException(sprintf('Invalid OTLP Protocol "%s" specified', $protocol));
        }
        if ($compression === self::DEFAULT_COMPRESSION) {
            $compression = null;
        }

        return $transportFactory->create(
            $endpoint,
            $headers,
            $compression,
            10,
            100,
            1,
        );
    }

    public static function fromConnectionString(string $endpointUrl = null, string $name = null, $args = null): Exporter
    {
        return new Exporter(self::createTransport(new PsrTransportFactory(
            Psr18ClientDiscovery::find(),
            Psr17FactoryDiscovery::findRequestFactory(),
            Psr17FactoryDiscovery::findStreamFactory(),
        )));
    }

    public function export(iterable $spans, ?CancellationInterface $cancellation = null): FutureInterface
    {
        return $this->transport
            ->send((new SpanConverter())->convert($spans)->serializeToString(), 'application/x-protobuf', $cancellation)
            ->map(static function (string $payload): bool {
                $serviceResponse = new ExportTraceServiceResponse();
                $serviceResponse->mergeFromString($payload);

                $partialSuccess = $serviceResponse->getPartialSuccess();
                if ($partialSuccess !== null && $partialSuccess->getRejectedSpans()) {
                    self::logError('Export partial success', [
                        'rejected_spans' => $partialSuccess->getRejectedSpans(),
                        'error_message' => $partialSuccess->getErrorMessage(),
                    ]);

                    return false;
                }
                if ($partialSuccess !== null && $partialSuccess->getErrorMessage()) {
                    self::logWarning('Export success with warnings/suggestions', ['error_message' => $partialSuccess->getErrorMessage()]);
                }

                return true;
            })
            ->catch(static function (Throwable $throwable): bool {
                self::logError('Export failure', ['exception' => $throwable]);

                return false;
            });
    }

    public function shutdown(?CancellationInterface $cancellation = null): bool
    {
        return $this->transport->shutdown($cancellation);
    }

    public function forceFlush(?CancellationInterface $cancellation = null): bool
    {
        return $this->transport->forceFlush($cancellation);
    }
}
