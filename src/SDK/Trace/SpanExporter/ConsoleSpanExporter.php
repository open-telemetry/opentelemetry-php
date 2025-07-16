<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\SpanExporter;

use JsonException;
use OpenTelemetry\API\Behavior\LogsMessagesTrait;
use OpenTelemetry\SDK\Common\Export\TransportInterface;
use OpenTelemetry\SDK\Common\Future\CancellationInterface;
use OpenTelemetry\SDK\Common\Future\FutureInterface;
use OpenTelemetry\SDK\Trace\Behavior\UsesSpanConverterTrait;
use OpenTelemetry\SDK\Trace\SpanConverterInterface;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;

class ConsoleSpanExporter implements SpanExporterInterface
{
    use UsesSpanConverterTrait;
    use LogsMessagesTrait;

    public function __construct(
        private readonly TransportInterface $transport,
        ?SpanConverterInterface $converter = null,
    ) {
        $this->setSpanConverter($converter ?? new FriendlySpanConverter());
    }

    #[\Override]
    public function export(iterable $batch, ?CancellationInterface $cancellation = null): FutureInterface
    {
        $payload = '';
        foreach ($batch as $span) {
            try {
                $payload .= json_encode(
                    $this->getSpanConverter()->convert([$span]),
                    JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT
                ) . PHP_EOL;
            } catch (JsonException $e) {
                self::logWarning('Error converting span: ' . $e->getMessage());
            }
        }

        return $this->transport->send($payload)
            ->map(fn () => true)
            ->catch(fn () => false);
    }

    #[\Override]
    public function shutdown(?CancellationInterface $cancellation = null): bool
    {
        return $this->transport->shutdown();
    }

    #[\Override]
    public function forceFlush(?CancellationInterface $cancellation = null): bool
    {
        return $this->transport->forceFlush();
    }
}
