<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Metrics\StalenessHandler;

use OpenTelemetry\SDK\Metrics\StalenessHandler\ImmediateStalenessHandlerFactory;
use PHPUnit\Framework\TestCase;
use stdClass;
use WeakReference;

#[\PHPUnit\Framework\Attributes\CoversClass(\OpenTelemetry\SDK\Metrics\StalenessHandler\ImmediateStalenessHandler::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\OpenTelemetry\SDK\Metrics\StalenessHandler\ImmediateStalenessHandlerFactory::class)]
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

    public function test_releases_callbacks_on_persistent_acquire(): void
    {
        $handler = (new ImmediateStalenessHandlerFactory())->create();

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
