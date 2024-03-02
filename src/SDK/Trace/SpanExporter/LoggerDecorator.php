<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\SpanExporter;

use OpenTelemetry\SDK\Trace\Behavior\LoggerAwareTrait;
use OpenTelemetry\SDK\Trace\Behavior\SpanExporterDecoratorTrait;
use OpenTelemetry\SDK\Trace\Behavior\UsesSpanConverterTrait;
use OpenTelemetry\SDK\Trace\SpanConverterInterface;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Psr\Log\NullLogger;

class LoggerDecorator implements SpanExporterInterface, LoggerAwareInterface
{
    use SpanExporterDecoratorTrait;
    use UsesSpanConverterTrait;
    use LoggerAwareTrait;

    public function __construct(
        SpanExporterInterface $decorated,
        ?LoggerInterface $logger = null,
        ?SpanConverterInterface $converter = null,
    ) {
        $this->setDecorated($decorated);
        $this->setLogger($logger ?? new NullLogger());
        $this->setSpanConverter($converter ?? new FriendlySpanConverter());
    }

    protected function beforeExport(iterable $spans): iterable
    {
        return $spans;
    }

    protected function afterExport(iterable $spans, bool $exportSuccess): void
    {
        if ($exportSuccess) {
            $this->log(
                'Status Success',
                $this->getSpanConverter()->convert($spans),
                LogLevel::INFO,
            );
        } else {
            $this->log(
                'Status Failed Retryable',
                $this->getSpanConverter()->convert($spans),
                LogLevel::ERROR,
            );
        }
    }
}
