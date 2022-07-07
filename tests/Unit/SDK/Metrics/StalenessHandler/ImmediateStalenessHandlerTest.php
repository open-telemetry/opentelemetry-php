<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Metrics\StalenessHandler;

use OpenTelemetry\SDK\Metrics\StalenessHandler\ImmediateStalenessHandlerFactory;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\SDK\Metrics\StalenessHandler\ImmediateStalenessHandler
 * @covers \OpenTelemetry\SDK\Metrics\StalenessHandler\ImmediateStalenessHandlerFactory
 */
final class ImmediateStalenessHandlerTest extends TestCase
{
    public function test_on_stale(): void
    {
        $called = false;
        $handler = (new ImmediateStalenessHandlerFactory())->create();
        $handler->onStale(static function () use (&$called): void {
            $called = true;
        });

        $handler->acquire();
        $handler->acquire();
        $this->assertFalse($called);

        $handler->release();
        $this->assertFalse($called);

        $handler->release();
        /** @phpstan-ignore-next-line */
        $this->assertTrue($called);
    }
}
