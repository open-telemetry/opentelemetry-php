<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\SpanExporter;

use OpenTelemetry\SDK\Logs\SimplePsrFileLogger;
use OpenTelemetry\SDK\Trace\Behavior\LoggerAwareTrait;
use OpenTelemetry\SDK\Trace\Behavior\SpanExporterTrait;
use OpenTelemetry\SDK\Trace\Behavior\UsesSpanConverterTrait;
use OpenTelemetry\SDK\Trace\SpanConverterInterface;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Psr\Log\NullLogger;
use Throwable;

class LoggerExporter implements SpanExporterInterface, LoggerAwareInterface
{
    use SpanExporterTrait;
    use UsesSpanConverterTrait;
    use LoggerAwareTrait;

    public const GRANULARITY_AGGREGATE = 1;
    public const GRANULARITY_SPAN = 2;
    public const DEFAULT_LOG_LEVEL = LogLevel::DEBUG;

    private string $serviceName;
    private int $granularity = self::GRANULARITY_AGGREGATE;

    /**
     * @param string $serviceName
     * @param LoggerInterface|null $logger
     * @param string|null $defaultLogLevel
     * @param SpanConverterInterface|null $converter
     * @param int $granularity
     */
    public function __construct(
        string $serviceName,
        ?LoggerInterface $logger = null,
        ?string $defaultLogLevel = LogLevel::DEBUG,
        ?SpanConverterInterface $converter = null,
        int $granularity = 1
    ) {
        $this->setServiceName($serviceName);
        $this->setLogger($logger ?? new NullLogger());
        $this->setDefaultLogLevel($defaultLogLevel ?? LogLevel::DEBUG);
        $this->setSpanConverter($converter ?? new FriendlySpanConverter());
        $this->setGranularity($granularity);
    }

    /** @inheritDoc */
    public function doExport(iterable $spans): int
    {
        try {
            $this->doLog($spans);
        } catch (Throwable $t) {
            return SpanExporterInterface::STATUS_FAILED_NOT_RETRYABLE;
        }

        return SpanExporterInterface::STATUS_SUCCESS;
    }

    /**
     * @param string $endpointUrl
     * @param string $name
     * @param string $args
     * @return LoggerExporter
     */
    public static function fromConnectionString(string $endpointUrl, string $name, string $args): self
    {
        return new self(
            $name,
            new SimplePsrFileLogger($endpointUrl),
            $args
        );
    }

    /**
     * @param string $serviceName
     */
    private function setServiceName(string $serviceName): void
    {
        $this->serviceName = $serviceName;
    }

    /**
     * @param int $granularity
     */
    public function setGranularity(int $granularity): void
    {
        $this->granularity = $granularity === self::GRANULARITY_SPAN
            ? self::GRANULARITY_SPAN
            : self::GRANULARITY_AGGREGATE;
    }

    /**
     * @param iterable $spans
     */
    private function doLog(iterable $spans): void
    {
        if ($this->granularity === self::GRANULARITY_AGGREGATE) {
            $this->log($this->serviceName, $this->getSpanConverter()->convert($spans));

            return;
        }

        foreach ($spans as $span) {
            $this->log($this->serviceName, $this->getSpanConverter()->convert([$span]));
        }
    }
}
