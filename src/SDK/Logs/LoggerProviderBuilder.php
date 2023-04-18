<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Logs;

use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeFactory;
use OpenTelemetry\SDK\Logs\Processor\MultiLogsProcessor;
use OpenTelemetry\SDK\Logs\Processor\NoopLogsProcessor;
use OpenTelemetry\SDK\Resource\ResourceInfo;

class LoggerProviderBuilder
{
    /**
     * @var array<LogRecordProcessorInterface>
     */
    private array $processors = [];
    private ?ResourceInfo $resource = null;

    public function addLogRecordProcessor(LogRecordProcessorInterface $processor): self
    {
        $this->processors[] = $processor;

        return $this;
    }

    public function setResource(ResourceInfo $resource): self
    {
        $this->resource = $resource;

        return $this;
    }

    public function build(): LoggerProviderInterface
    {
        return new LoggerProvider(
            $this->buildProcessor(),
            new InstrumentationScopeFactory(Attributes::factory()),
            $this->resource
        );
    }

    private function buildProcessor(): LogRecordProcessorInterface
    {
        switch (count($this->processors)) {
            case 0:
                return NoopLogsProcessor::getInstance();
            case 1:
                return $this->processors[0];
            default:
                return new MultiLogsProcessor($this->processors);
        }
    }
}
