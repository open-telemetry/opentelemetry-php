<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Metrics\StalenessHandler;

use OpenTelemetry\SDK\Metrics\ReferenceCounterInterface;
use OpenTelemetry\SDK\Metrics\StalenessHandler\NoopStalenessHandler;
use OpenTelemetry\SDK\Metrics\StalenessHandler\NoopStalenessHandlerFactory;
use OpenTelemetry\SDK\Metrics\StalenessHandlerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(NoopStalenessHandlerFactory::class)]
#[CoversClass(NoopStalenessHandler::class)]
final class NoopStalenessHandlerFactoryTest extends TestCase
{
    public function test_create_returns_noop_staleness_handler(): void
    {
        $factory = new NoopStalenessHandlerFactory();
        $handler = $factory->create();

        $this->assertInstanceOf(NoopStalenessHandler::class, $handler);
        $this->assertInstanceOf(ReferenceCounterInterface::class, $handler);
        $this->assertInstanceOf(StalenessHandlerInterface::class, $handler);
    }

    public function test_create_returns_same_instance(): void
    {
        $factory = new NoopStalenessHandlerFactory();

        $this->assertSame($factory->create(), $factory->create());
    }

    public function test_noop_handler_acquire_and_release_do_not_throw(): void
    {
        $factory = new NoopStalenessHandlerFactory();
        $handler = $factory->create();

        $handler->acquire();
        $handler->acquire(true);
        $handler->release();

        $this->addToAssertionCount(1);
    }

    public function test_noop_handler_on_stale_does_not_invoke_callback(): void
    {
        $factory = new NoopStalenessHandlerFactory();
        $handler = $factory->create();

        $called = false;
        $handler->onStale(static function () use (&$called): void {
            $called = true;
        });

        $handler->acquire();
        $handler->release();

        /** @phpstan-ignore-next-line */
        $this->assertFalse($called);
    }
}
