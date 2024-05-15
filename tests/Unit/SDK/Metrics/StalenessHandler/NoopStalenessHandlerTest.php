<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Metrics\StalenessHandler;

use OpenTelemetry\SDK\Metrics\StalenessHandler\NoopStalenessHandler;
use OpenTelemetry\SDK\Metrics\StalenessHandler\NoopStalenessHandlerFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(NoopStalenessHandler::class)]
#[CoversClass(NoopStalenessHandlerFactory::class)]
final class NoopStalenessHandlerTest extends TestCase
{
    public function test_on_stale(): void
    {
        $called = false;
        $handler = (new NoopStalenessHandlerFactory())->create();
        $handler->onStale(static function () use (&$called): void {
            $called = true;
        });

        $handler->acquire();
        $handler->acquire();
        $this->assertFalse($called);

        $handler->release();
        $handler->release();
        $this->assertFalse($called);
    }
}
