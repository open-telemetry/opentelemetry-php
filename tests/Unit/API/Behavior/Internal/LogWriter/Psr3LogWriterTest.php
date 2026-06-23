<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\API\Behavior\Internal\LogWriter;

use OpenTelemetry\API\Behavior\Internal\LogWriter\Psr3LogWriter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

#[CoversClass(Psr3LogWriter::class)]
final class Psr3LogWriterTest extends TestCase
{
    public function test_write_delegates_to_psr3_logger(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('log')
            ->with('warning', 'test message', ['key' => 'value']);

        $writer = new Psr3LogWriter($logger);
        $writer->write('warning', 'test message', ['key' => 'value']);
    }

    public function test_write_passes_level_through(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('log')
            ->with('error', 'error message', []);

        $writer = new Psr3LogWriter($logger);
        $writer->write('error', 'error message', []);
    }

    public function test_write_passes_context_with_exception(): void
    {
        $exception = new \RuntimeException('test exception');
        $context = ['exception' => $exception, 'extra' => 'data'];

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('log')
            ->with('critical', 'something broke', $context);

        $writer = new Psr3LogWriter($logger);
        $writer->write('critical', 'something broke', $context);
    }
}
