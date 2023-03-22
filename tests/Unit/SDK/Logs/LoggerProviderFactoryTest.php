<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Logs;

use OpenTelemetry\API\Logs\LoggerProviderInterface;
use OpenTelemetry\SDK\Logs\LoggerProviderFactory;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\SDK\Logs\LoggerProviderFactory
 */
class LoggerProviderFactoryTest extends TestCase
{
    public function test_create(): void
    {
        $factory = new LoggerProviderFactory();
        $this->assertInstanceOf(LoggerProviderInterface::class, $factory->create());
    }
}
