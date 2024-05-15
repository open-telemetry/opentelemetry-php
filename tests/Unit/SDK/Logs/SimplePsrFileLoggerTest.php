<?php

declare(strict_types=1);

namespace Logs;

use OpenTelemetry\SDK\Logs\SimplePsrFileLogger;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\InvalidArgumentException;
use Psr\Log\LogLevel;

#[CoversClass(SimplePsrFileLogger::class)]
class SimplePsrFileLoggerTest extends TestCase
{
    private const ROOT_DIR = 'var';
    private const LOG_FILE = 'test.log';
    private const LOG_PATH = self::ROOT_DIR . '/' . self::LOG_FILE;
    private const LOG_LEVELS = [
        LogLevel::EMERGENCY,
        LogLevel::ALERT,
        LogLevel::CRITICAL,
        LogLevel::ERROR,
        LogLevel::WARNING,
        LogLevel::NOTICE,
        LogLevel::INFO,
        LogLevel::DEBUG,
    ];

    private SimplePsrFileLogger $logger;
    private vfsStreamDirectory $root;

    public function setUp(): void
    {
        $this->root = vfsStream::setup(self::ROOT_DIR);
        $this->logger = new SimplePsrFileLogger(
            vfsStream::url(self::LOG_PATH)
        );
    }

    #[DataProvider('logLevelProvider')]
    public function test_log(string $logLevel): void
    {
        $this->assertFalse($this->root->hasChild(self::LOG_FILE));

        $this->logger->log($logLevel, 'foo', ['bar']);
        $this->logger->{$logLevel}('foz', ['baz']);

        $this->assertTrue($this->root->hasChild(self::LOG_FILE));
    }

    public static function logLevelProvider(): array
    {
        $result = [];

        foreach (self::LOG_LEVELS as $level) {
            $result[] = [$level];
        }

        return $result;
    }

    public function test_log_invalid_log_level(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->logger->log('foo', 'foo', ['bar']);
    }

    public function test_log_invalid_json(): void
    {
        $this->assertFalse($this->root->hasChild(self::LOG_FILE));

        $resource = fopen('php://stdin', 'rb');

        $this->logger->log('info', 'foo', [1 => $resource]);

        $this->assertTrue($this->root->hasChild(self::LOG_FILE));

        fclose($resource);
    }
}
