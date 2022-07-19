<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Integration\API\Common;

use OpenTelemetry\API\Common\Event\Dispatcher;
use OpenTelemetry\Context\Context;
use PHPUnit\Framework\TestCase;

/**
 * @coversNothing
 */
class EventContextIntegrationTest extends TestCase
{
    public function test_event_dispatcher_does_not_leak_scope(): void
    {
        $key = Context::createKey('-');
        $scope = Context::getCurrent()->with($key, 1)->activate();
        Dispatcher::getRoot();
        $result = $scope->detach();

        $this->assertSame(0, $result);
        $this->assertNull(Context::getCurrent()->get($key));
    }
}
