<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\API\Behavior\Internal\LogWriter;

use OpenTelemetry\API\Behavior\Internal\LogWriter\ErrorLogWriter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ErrorLogWriter::class)]
final class ErrorLogWriterTest extends TestCase
{
    public function test_write_calls_error_log(): void
    {
        $writer = new ErrorLogWriter();

        // Capture error_log output by redirecting to a temp file
        $file = tempnam(sys_get_temp_dir(), 'otel_errorlog_');
        $this->assertNotFalse($file);

        try {
            ini_set('error_log', $file);
            $writer->write('warning', 'test error log message', []);

            $contents = file_get_contents($file);
            $this->assertStringContainsString('test error log message', $contents);
        } finally {
            ini_restore('error_log');
            unlink($file);
        }
    }

    public function test_write_with_exception_context(): void
    {
        $writer = new ErrorLogWriter();

        $file = tempnam(sys_get_temp_dir(), 'otel_errorlog_');
        $this->assertNotFalse($file);

        try {
            ini_set('error_log', $file);
            $exception = new \RuntimeException('boom');
            $writer->write('error', 'failure occurred', ['exception' => $exception]);

            $contents = file_get_contents($file);
            $this->assertStringContainsString('failure occurred', $contents);
            $this->assertStringContainsString('boom', $contents);
        } finally {
            ini_restore('error_log');
            unlink($file);
        }
    }
}
