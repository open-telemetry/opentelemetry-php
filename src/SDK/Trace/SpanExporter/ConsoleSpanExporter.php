<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\SpanExporter;

use OpenTelemetry\SDK\Trace\Behavior\SpanExporterTrait;
use OpenTelemetry\SDK\Trace\Behavior\UsesSpanConverterTrait;
use OpenTelemetry\SDK\Trace\SpanConverterInterface;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;
use OpenTelemetry\SDK\Trace\Behavior\LoggerAwareTrait;
use Psr\Log\LogLevel;
use Throwable;

class ConsoleSpanExporter implements SpanExporterInterface
{
    use LoggerAwareTrait;
    use SpanExporterTrait;
    use UsesSpanConverterTrait;

    public function __construct(?SpanConverterInterface $converter = null)
    {
        $this->setSpanConverter($converter ?? new FriendlySpanConverter());
    }

    /** @inheritDoc */
    public function doExport(iterable $spans): int
    {
        try {
            foreach ($spans as $span) {
                print(json_encode(
                    $this->getSpanConverter()->convert([$span]),
                    JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT
                ) . PHP_EOL
                );
            }
        } catch (Throwable $t) {
            $this->log('Error exporting span', ['error' => $t->getMessage()], LogLevel::ERROR);
            return SpanExporterInterface::STATUS_FAILED_NOT_RETRYABLE;
        }

        return SpanExporterInterface::STATUS_SUCCESS;
    }

    public static function fromConnectionString(string $endpointUrl = null, string $name = null, $args = null)
    {
        return new self();
    }
}
