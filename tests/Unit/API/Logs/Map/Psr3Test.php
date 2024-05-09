<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\API\Logs\Map;

use OpenTelemetry\API\Logs\Map\Psr3;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;

#[\PHPUnit\Framework\Attributes\CoversClass(\OpenTelemetry\API\Logs\Map\Psr3::class)]
class Psr3Test extends TestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('levelProvider')]
    public function test_severity_number(string $level): void
    {
        $this->assertNotNull(Psr3::severityNumber($level));
    }

    public static function levelProvider(): array
    {
        return [
            [LogLevel::EMERGENCY],
            [LogLevel::ALERT],
            [LogLevel::CRITICAL],
            [LogLevel::ERROR],
            [LogLevel::WARNING],
            [LogLevel::NOTICE],
            [LogLevel::INFO],
            [LogLevel::DEBUG],
        ];
    }
}
