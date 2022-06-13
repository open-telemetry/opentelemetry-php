<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Common\Event\Handler;

use OpenTelemetry\SDK\Common\Log\LoggerHolder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

abstract class LogBasedHandlerTest extends TestCase
{
    // @var MockObject&LoggerInterface $logger
    protected LoggerInterface $logger;

    public function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        LoggerHolder::set($this->logger);
    }

    public function tearDown(): void
    {
        LoggerHolder::unset();
    }
}
