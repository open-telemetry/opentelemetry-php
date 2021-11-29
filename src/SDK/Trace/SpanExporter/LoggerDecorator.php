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
use RuntimeException;

class LoggerDecorator implements SpanExporterInterface, LoggerAwareInterface
{
    use SpanExporterDecoratorTrait;
    use UsesSpanConverterTrait;
    use LoggerAwareTrait;

    private const RESPONSE_MAPPING = [
        SpanExporterInterface::STATUS_SUCCESS =>  LogLevel::INFO,
        SpanExporterInterface::STATUS_FAILED_RETRYABLE =>  LogLevel::ERROR,
        SpanExporterInterface::STATUS_FAILED_NOT_RETRYABLE =>  LogLevel::ALERT,
    ];

    private const MESSAGE_MAPPING = [
        SpanExporterInterface::STATUS_SUCCESS =>  'Status Success',
        SpanExporterInterface::STATUS_FAILED_RETRYABLE =>  'Status Failed Retryable',
        SpanExporterInterface::STATUS_FAILED_NOT_RETRYABLE =>  'Status Failed Not Retryable',
    ];

    public function __construct(
        SpanExporterInterface $decorated,
        ?LoggerInterface $logger = null,
        ?SpanConverterInterface $converter = null
    ) {
        $this->setDecorated($decorated);
        $this->setLogger($logger ?? new NullLogger());
        $this->setSpanConverter($converter ?? new FriendlySpanConverter());
    }

    public static function fromConnectionString(string $endpointUrl, string $name, string $args): void
    {
        throw new RuntimeException(
            sprintf('%s cannot be instantiated via %s', __CLASS__, __METHOD__)
        );
    }

    protected function beforeExport(iterable $spans): iterable
    {
        return $spans;
    }

    /**
     * @param iterable $spans
     * @param int $exporterResponse
     */
    protected function afterExport(iterable $spans, int $exporterResponse): void
    {
        $this->log(
            self::MESSAGE_MAPPING[$exporterResponse],
            $this->getSpanConverter()->convert($spans),
            self::RESPONSE_MAPPING[$exporterResponse]
        );
    }
}
