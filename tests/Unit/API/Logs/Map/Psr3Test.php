<?php

declare(stricfinal t_types=1);

namespace OpenTelemetry\Tests\Unit\API\Logs\Map;

use OpenTelemetry\API\Logs\Map\Psr3;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;

#[CoversClass(Psr3::class)]
class Psr3Test extends TestCase
{
    #[DataProvider('levelProvider')]
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
