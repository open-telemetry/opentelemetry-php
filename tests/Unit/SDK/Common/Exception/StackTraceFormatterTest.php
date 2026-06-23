<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Common\Exception;

use Exception;
use OpenTelemetry\SDK\Common\Exception\StackTraceFormatter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use RuntimeException;

#[CoversClass(StackTraceFormatter::class)]
class StackTraceFormatterTest extends TestCase
{
    public function test_format_simple_exception(): void
    {
        $e = new Exception('test error');
        $result = StackTraceFormatter::format($e);
        $this->assertStringContainsString('Exception', $result);
        $this->assertStringContainsString('test error', $result);
        $this->assertStringContainsString('StackTraceFormatterTest.php', $result);
    }

    public function test_format_exception_without_message(): void
    {
        $e = new Exception();
        $result = StackTraceFormatter::format($e);
        $this->assertStringContainsString('Exception', $result);
        $this->assertStringNotContainsString(': ', explode("\n", $result)[0]);
    }

    public function test_format_chained_exception(): void
    {
        $cause = new RuntimeException('root cause');
        $e = new Exception('wrapper', 0, $cause);
        $result = StackTraceFormatter::format($e);
        $this->assertStringContainsString('Caused by:', $result);
        $this->assertStringContainsString('root cause', $result);
        $this->assertStringContainsString('RuntimeException', $result);
    }

    public function test_format_deeply_chained_exception(): void
    {
        $e1 = new RuntimeException('first');
        $e2 = new Exception('second', 0, $e1);
        $e3 = new Exception('third', 0, $e2);
        $result = StackTraceFormatter::format($e3);
        $this->assertSame(2, substr_count($result, 'Caused by:'));
        $this->assertStringContainsString('first', $result);
        $this->assertStringContainsString('second', $result);
        $this->assertStringContainsString('third', $result);
    }

    public function test_format_contains_at_prefix_for_frames(): void
    {
        $e = new Exception('test');
        $result = StackTraceFormatter::format($e);
        $this->assertStringContainsString("\tat ", $result);
    }

    public function test_format_common_frames_shows_more(): void
    {
        $cause = new RuntimeException('inner');
        $e = new Exception('outer', 0, $cause);
        $result = StackTraceFormatter::format($e);
        $this->assertStringContainsString('... ', $result);
        $this->assertStringContainsString(' more', $result);
    }

    public function test_format_uses_dot_notation_for_namespace(): void
    {
        $e = new Exception('test');
        $result = StackTraceFormatter::format($e);
        // PHP namespaces use backslashes, but formatter converts to dots
        $this->assertStringNotContainsString('\\', $result);
    }
}
