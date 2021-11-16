<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\SpanExporter;

use OpenTelemetry\SDK\Trace\Behavior\SpanExporterTrait;
use OpenTelemetry\SDK\Trace\Behavior\UsesSpanConverterTrait;
use OpenTelemetry\SDK\Trace\SpanConverterInterface;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;
use Throwable;

class ConsoleSpanExporter implements SpanExporterInterface
{
    use SpanExporterTrait;
    use UsesSpanConverterTrait;

    public function __construct(?SpanConverterInterface $converter = null)
    {
        $this->setSpanConverter($converter ?? new FriendlySpanConverter());
    }

    /** @inheritDoc */
    public function export(iterable $spans): int
    {
        if (!$this->running) {
            return SpanExporterInterface::STATUS_FAILED_NOT_RETRYABLE;
        }

        try {
            foreach ($spans as $span) {
                print(json_encode(
                    $this->getSpanConverter()->convert($span),
                    JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT
                ) . PHP_EOL
                );
            }
        } catch (Throwable $t) {
            return SpanExporterInterface::STATUS_FAILED_NOT_RETRYABLE;
        }

        return SpanExporterInterface::STATUS_SUCCESS;
    }

    public static function fromConnectionString(string $endpointUrl = null, string $name = null, $args = null)
    {
        return new self();
    }
}
