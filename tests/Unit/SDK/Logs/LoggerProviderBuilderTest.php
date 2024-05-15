<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Logs;

use OpenTelemetry\SDK\Logs\LoggerProvider;
use OpenTelemetry\SDK\Logs\LoggerProviderBuilder;
use OpenTelemetry\SDK\Logs\LoggerProviderInterface;
use OpenTelemetry\SDK\Logs\LogRecordProcessorInterface;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(LoggerProviderBuilder::class)]
class LoggerProviderBuilderTest extends TestCase
{
    public function test_builder(): void
    {
        $processor = $this->createMock(LogRecordProcessorInterface::class);
        $resource = $this->createMock(ResourceInfo::class);
        $provider = LoggerProvider::builder()
            ->addLogRecordProcessor($processor)
            ->setResource($resource)
            ->build();
        $this->assertInstanceOf(LoggerProviderInterface::class, $provider);
    }
}
