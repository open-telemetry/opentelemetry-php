<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\API\Logs;

use OpenTelemetry\API\Behavior\Internal\Logging;
use OpenTelemetry\API\Logs\Severity;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use function sprintf;
use ValueError;

#[CoversClass(Severity::class)]
class SeverityTest extends TestCase
{
    public function test_value_error(): void
    {
        $this->expectException(ValueError::class);
        Severity::fromPsr3('unknown');
    }

    #[DataProvider('levelProvider')]
    public function test_severity_number(string $level): void
    {
        $this->assertNotNull(Severity::fromPsr3($level));
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

    #[DataProvider('levelProvider')]
    public function test_to_psr3_round_trips_psr3_levels(string $level): void
    {
        $this->assertSame($level, Severity::fromPsr3($level)->toPsr3());
    }

    #[DataProvider('numberedSeverityProvider')]
    public function test_to_psr3_collapses_numbered_severities(Severity $severity, string $expected): void
    {
        $this->assertSame($expected, $severity->toPsr3());
    }

    public static function numberedSeverityProvider(): iterable
    {
        yield 'trace' => [Severity::TRACE, LogLevel::DEBUG];
        yield 'trace4' => [Severity::TRACE4, LogLevel::DEBUG];
        yield 'debug4' => [Severity::DEBUG4, LogLevel::DEBUG];
        yield 'info2, pinned to notice by the spec' => [Severity::INFO2, LogLevel::NOTICE];
        yield 'info4' => [Severity::INFO4, LogLevel::NOTICE];
        yield 'warn' => [Severity::WARN, LogLevel::WARNING];
        yield 'warn4' => [Severity::WARN4, LogLevel::WARNING];
        yield 'error2, pinned to critical by the spec' => [Severity::ERROR2, LogLevel::CRITICAL];
        yield 'error4' => [Severity::ERROR4, LogLevel::ALERT];
        yield 'fatal4' => [Severity::FATAL4, LogLevel::EMERGENCY];
    }

    /**
     * The mapped level is used as a threshold, so a higher severity must never map to a more
     * verbose PSR-3 level than a lower one.
     */
    public function test_to_psr3_is_monotonic(): void
    {
        $previous = -1;
        foreach (Severity::cases() as $case) {
            $priority = Logging::level($case->toPsr3());
            $this->assertGreaterThanOrEqual(
                $previous,
                $priority,
                sprintf('%s maps to %s, which is less severe than the preceding case', $case->name, $case->toPsr3()),
            );
            $previous = $priority;
        }
    }

    /**
     * The `match` is exhaustive, so every case maps without an UnhandledMatchError, and onto a
     * level that round-trips back through {@see Severity::fromPsr3()}.
     */
    public function test_to_psr3_covers_every_case(): void
    {
        foreach (Severity::cases() as $case) {
            $this->assertSame($case->toPsr3(), Severity::fromPsr3($case->toPsr3())->toPsr3());
        }
    }

    #[DataProvider('nameProvider')]
    public function test_from_name(string $name, Severity $expected): void
    {
        $this->assertSame($expected, Severity::fromName($name));
    }

    public static function nameProvider(): iterable
    {
        yield 'lowercase, as the config enum emits' => ['warn', Severity::WARN];
        yield 'numbered' => ['fatal4', Severity::FATAL4];
        yield 'uppercase' => ['DEBUG', Severity::DEBUG];
        yield 'mixed case' => ['Info2', Severity::INFO2];
    }

    public function test_from_name_rejects_unknown(): void
    {
        $this->expectException(ValueError::class);
        Severity::fromName('warning');
    }
}
