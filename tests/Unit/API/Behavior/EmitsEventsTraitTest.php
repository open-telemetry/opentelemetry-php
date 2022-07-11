<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\API\Behavior;

use OpenTelemetry\API\Behavior\EmitsEventsTrait;
use OpenTelemetry\API\Common\Event\Dispatcher;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @covers \OpenTelemetry\API\Behavior\EmitsEventsTrait
 */
class EmitsEventsTraitTest extends TestCase
{
    public function tearDown(): void
    {
        Dispatcher::unset();
    }

    public function test_does_stuff(): void
    {
        $event = new stdClass();
        $class = $this->createInstance();
        Dispatcher::getInstance()->listen(get_class($event), function () {
            $this->assertTrue(true, 'listener was called');
        });
        $class->run('emit', $event);
    }

    private function createInstance(): object
    {
        return new class() {
            use EmitsEventsTrait;
            //accessor for protected trait methods
            public function run(string $method, $param): void
            {
                $this->{$method}($param);
            }
        };
    }
}
