<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\API\Behavior\Internal\LogWriter;

use OpenTelemetry\API\Behavior\Internal\LogWriter\NoopLogWriter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(NoopLogWriter::class)]
final class NoopLogWriterTest extends TestCase
{
    public function test_write_does_nothing(): void
    {
        $writer = new NoopLogWriter();

        // Should not throw or produce any side effects
        $writer->write('info', 'this message is discarded', []);
        $writer->write('error', 'this too', ['exception' => new \RuntimeException('ignored')]);

        $this->assertTrue(true);
    }
}
