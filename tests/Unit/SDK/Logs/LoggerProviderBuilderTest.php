<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Logs;

use OpenTelemetry\SDK\Logs\LoggerProvider;
use OpenTelemetry\SDK\Logs\LoggerProviderInterface;
use OpenTelemetry\SDK\Logs\LogRecordProcessorInterface;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\SDK\Logs\LoggerProviderBuilder
 */
class LoggerProviderBuilderTest extends TestCase
{
    public function test_builder(): void
    {
        $processor = $this->createMock(LogRecordProcessorInterface::class);
        $resource = $this->createMock(ResourceInfo::class);
        $provider = LoggerProvider::builder()
            ->addLogRecordProcessor($processor)
            ->addResource($resource)
            ->build();
        $this->assertInstanceOf(LoggerProviderInterface::class, $provider);
    }
}
