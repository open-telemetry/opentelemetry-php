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

class Exporter implements SpanExporterInterface
{
    use LogsMessagesTrait;

    private TransportInterface $transport;

    /**
     * Exporter constructor.
     */
    public function __construct(TransportInterface $transport)
    {
        $this->transport = $transport;
    }

    /**
     * @todo inefficient string conversion to/from ExportTraceService{Request,Response}?
     */
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
