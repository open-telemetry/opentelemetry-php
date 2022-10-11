<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\Otlp;

use Opentelemetry\Proto\Collector\Trace\V1\ExportTraceServiceResponse;
use OpenTelemetry\SDK\Behavior\LogsMessagesTrait;
use OpenTelemetry\SDK\Common\Export\TransportInterface;
use OpenTelemetry\SDK\Common\Future\CancellationInterface;
use OpenTelemetry\SDK\Common\Future\FutureInterface;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;
use Throwable;

/**
 * @todo rename to TraceExporter ?
 */
class Exporter implements SpanExporterInterface
{
    use LogsMessagesTrait;

    private TransportInterface $transport;
    private string $protocol; //@see Protocols::*

    public function __construct(TransportInterface $transport, string $protocol)
    {
        $this->transport = $transport;
        $this->protocol = $protocol;
    }

    public function export(iterable $spans, ?CancellationInterface $cancellation = null): FutureInterface
    {
        $protocol = $this->protocol;
        $payload = Converter::encode((new SpanConverter())->convert($spans), $protocol);

        return $this->transport
            ->send($payload, Converter::contentType($this->protocol), $cancellation)
            ->map(static function (string $payload) use ($protocol): bool {
                $serviceResponse = new ExportTraceServiceResponse();
                Converter::decode($serviceResponse, $payload, $protocol);

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

    public static function fromConnectionString(string $endpointUrl, string $name, string $args)
    {
        trigger_error('Not implemented');
    }
}
