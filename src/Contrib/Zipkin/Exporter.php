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
 * @deprecated The Zipkin exporter is deprecated. Use OTLP instead.
 * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.61.0/specification/trace/sdk_exporters/zipkin.md
 */
class Exporter implements SpanExporterInterface
{
    use LogsMessagesTrait;
    use UsesSpanConverterTrait;

    /**
     * @phan-suppress PhanDeprecatedClass
     */
    public function __construct(
        private readonly TransportInterface $transport,
        ?SpanConverterInterface $spanConverter = null,
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

    #[\Override]
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

    #[\Override]
    public function shutdown(?CancellationInterface $cancellation = null): bool
    {
        return $this->transport->shutdown($cancellation);
    }

    #[\Override]
    public function forceFlush(?CancellationInterface $cancellation = null): bool
    {
        return $this->transport->forceFlush($cancellation);
    }
}
