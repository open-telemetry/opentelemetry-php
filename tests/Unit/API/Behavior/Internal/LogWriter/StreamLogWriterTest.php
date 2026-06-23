<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\API\Behavior\Internal\LogWriter;

use OpenTelemetry\API\Behavior\Internal\LogWriter\StreamLogWriter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(StreamLogWriter::class)]
final class StreamLogWriterTest extends TestCase
{
    public function test_write_appends_to_stream(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'otel_test_');
        $this->assertNotFalse($file);

        try {
            $writer = new StreamLogWriter($file);
            $writer->write('warning', 'test message', []);

            $contents = file_get_contents($file);
            $this->assertStringContainsString('test message', $contents);
        } finally {
            unlink($file);
        }
    }

    public function test_constructor_throws_for_invalid_destination(): void
    {
        $this->expectException(\RuntimeException::class);
        new StreamLogWriter('/nonexistent/path/that/should/not/exist/file.log');
    }

    public function test_write_with_exception_context(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'otel_test_');
        $this->assertNotFalse($file);

        try {
            $writer = new StreamLogWriter($file);
            $exception = new \RuntimeException('test exception');
            $writer->write('error', 'something failed', ['exception' => $exception]);

            $contents = file_get_contents($file);
            $this->assertStringContainsString('something failed', $contents);
            $this->assertStringContainsString('test exception', $contents);
        } finally {
            unlink($file);
        }
    }
}
