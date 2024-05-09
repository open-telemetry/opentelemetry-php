<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Metrics\StalenessHandler;

use OpenTelemetry\API\Common\Time\TestClock;
use OpenTelemetry\SDK\Metrics\StalenessHandler\DelayedStalenessHandlerFactory;
use PHPUnit\Framework\TestCase;
use stdClass;
use WeakReference;

#[\PHPUnit\Framework\Attributes\CoversClass(\OpenTelemetry\SDK\Metrics\StalenessHandler\DelayedStalenessHandler::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\OpenTelemetry\SDK\Metrics\StalenessHandler\DelayedStalenessHandlerFactory::class)]
final class DelayedStalenessHandlerTest extends TestCase
{
    public function test_on_stale(): void
    {
        $called = false;
        $clock = new TestClock();
        $factory = new DelayedStalenessHandlerFactory($clock, .15);
        $handler = $factory->create();
        $handler->onStale(static function () use (&$called): void {
            $called = true;
        });

        $handler->acquire();
        $handler->acquire();
        $handler->release();
        $handler->release();

        $clock->advance(100_000_000);
        $factory->create();
        $this->assertFalse($called);

        $clock->advance(100_000_000);
        $factory->create();
        /** @phpstan-ignore-next-line */
        $this->assertTrue($called);
    }

    public function test_on_stale_acquire_does_not_trigger_callbacks(): void
    {
        $called = false;
        $clock = new TestClock();
        $factory = new DelayedStalenessHandlerFactory($clock, .15);
        $handler = $factory->create();
        $handler->onStale(static function () use (&$called): void {
            $called = true;
        });

        $handler->acquire();
        $handler->release();

        $clock->advance(100_000_000);
        $factory->create();
        $this->assertFalse($called);

        $clock->advance(100_000_000);
        $handler->acquire();
        $factory->create();
        $this->assertFalse($called);
    }

    public function test_releases_callbacks_on_persistent_acquire(): void
    {
        $handler = (new DelayedStalenessHandlerFactory(new TestClock(), 0))->create();

        $object = new stdClass();
        $reference = WeakReference::create($object);
        /** @phpstan-ignore-next-line */
        $handler->onStale(static function () use ($object): void {
        });
        $handler->acquire(true);
        $object = null;
        $this->assertNull($reference->get());

        $object = new stdClass();
        $reference = WeakReference::create($object);
        /** @phpstan-ignore-next-line */
        $handler->onStale(static function () use ($object): void {
        });
        $object = null;
        $this->assertNull($reference->get());
    }
}
