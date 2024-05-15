<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Common\Util;

use OpenTelemetry\SDK\Common\Util\ShutdownHandler;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use WeakReference;

#[CoversClass(ShutdownHandler::class)]
final class ShutdownHandlerTest extends TestCase
{
    public function test_shutdown_handler_does_not_keep_reference_to_shutdown_function_this(): void
    {
        $object = new class() {
            public function foo(): void
            {
            }
        };

        ShutdownHandler::register($object->foo(...));

        $reference = WeakReference::create($object);
        $object = null;

        $this->assertNull($reference->get());
    }
}
