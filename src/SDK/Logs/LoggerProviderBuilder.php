<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Logs;

use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeFactory;
use OpenTelemetry\SDK\Logs\Processor\NoopLogsProcessor;
use OpenTelemetry\SDK\Resource\ResourceInfo;

class LoggerProviderBuilder
{
    private ?LogRecordProcessorInterface $processor = null;
    private ?ResourceInfo $resource = null;

    public function addLogRecordProcessor(LogRecordProcessorInterface $processor): self
    {
        $this->processor = $processor;

        return $this;
    }
    public function addResource(ResourceInfo $resource): self
    {
        $this->resource = $resource;

        return $this;
    }

    public function build(): LoggerProviderInterface
    {
        return new LoggerProvider(
            $this->processor ?? NoopLogsProcessor::getInstance(),
            new InstrumentationScopeFactory(Attributes::factory()),
            $this->resource
        );
    }
}
