<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Logs;

use OpenTelemetry\API\Logs\LoggerProviderInterface;
use OpenTelemetry\API\Logs\NoopEventLoggerProvider;
use OpenTelemetry\SDK\Common\Configuration\Variables;
use OpenTelemetry\SDK\Logs\EventLoggerProvider;
use OpenTelemetry\SDK\Logs\EventLoggerProviderFactory;
use OpenTelemetry\Tests\TestState;
use PHPUnit\Framework\TestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\OpenTelemetry\SDK\Logs\EventLoggerProviderFactory::class)]
class EventLoggerProviderFactoryTest extends TestCase
{
    use TestState;

    /**
     * @psalm-suppress ArgumentTypeCoercion
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('createProvider')]
    public function test_create(string $disabled, string $expected): void
    {
        $this->setEnvironmentVariable(Variables::OTEL_SDK_DISABLED, $disabled);
        $loggerProvider = $this->createMock(LoggerProviderInterface::class);
        $factory = new EventLoggerProviderFactory();
        $eventLoggerProvider = $factory->create($loggerProvider);
        $this->assertInstanceOf($expected, $eventLoggerProvider);
    }

    public static function createProvider(): array
    {
        return [
            'sdk disabled' => [
                'true',
                NoopEventLoggerProvider::class,
            ],
            'sdk enabled' => [
                'false',
                EventLoggerProvider::class,
            ],
        ];
    }
}
