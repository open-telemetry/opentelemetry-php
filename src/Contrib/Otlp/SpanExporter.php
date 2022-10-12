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
 * @psalm-import-type SUPPORTED_CONTENT_TYPES from ProtobufSerializer
 */
final class SpanExporter implements SpanExporterInterface
{
    use LogsMessagesTrait;

    private TransportInterface $transport;
    private ProtobufSerializer $serializer;

    /**
     * @psalm-param TransportInterface<SUPPORTED_CONTENT_TYPES> $transport
     */
    public function __construct(TransportInterface $transport)
    {
        $this->transport = $transport;
        $this->serializer = ProtobufSerializer::forTransport($transport);
    }

    public function export(iterable $batch, ?CancellationInterface $cancellation = null): FutureInterface
    {
        return $this->transport
            ->send($this->serializer->serialize((new SpanConverter())->convert($batch)), $cancellation)
            ->map(function (?string $payload): bool {
                if ($payload === null) {
                    return true;
                }

                $serviceResponse = new ExportTraceServiceResponse();
                $this->serializer->hydrate($serviceResponse, $payload);

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
