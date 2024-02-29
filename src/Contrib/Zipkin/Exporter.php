<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\Zipkin;

use JsonException;
use OpenTelemetry\API\Behavior\LogsMessagesTrait;
use OpenTelemetry\SDK\Common\Export\TransportInterface;
use OpenTelemetry\SDK\Common\Future\CancellationInterface;
use OpenTelemetry\SDK\Common\Future\FutureInterface;
use OpenTelemetry\SDK\Trace\Behavior\UsesSpanConverterTrait;
use OpenTelemetry\SDK\Trace\SpanConverterInterface;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;
use Throwable;

/**
 * Class ZipkinExporter - implements the export interface for data transfer via Zipkin protocol
 * @package OpenTelemetry\Exporter
 */
class Exporter implements SpanExporterInterface
{
    use LogsMessagesTrait;
    use UsesSpanConverterTrait;

    public function __construct(
        private TransportInterface $transport,
        SpanConverterInterface $spanConverter = null
    ) {
        $this->setSpanConverter($spanConverter ?? new SpanConverter());
    }

    /**
     * @throws JsonException
     */
    protected function serializeTrace(iterable $spans): string
    {
        return json_encode(
            $this->getSpanConverter()->convert($spans),
            JSON_THROW_ON_ERROR
        );
    }

    public function export(iterable $batch, ?CancellationInterface $cancellation = null): FutureInterface
    {
        return $this->transport
            ->send($this->serializeTrace($batch), $cancellation)
            ->map(static fn (): bool => true)
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
