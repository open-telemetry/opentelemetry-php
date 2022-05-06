<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Integration;

use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\ContextStorage;
use OpenTelemetry\SDK\Trace\TracerProvider;
use PHPUnit\Framework\TestCase;

/**
 * This class tests using multiple ContextStorage to generate multiple active traces at the same time.
 */
class ConcurrentTraceTest extends TestCase
{
    public function test_stuff(): void
    {
        $tracerProvider =  new TracerProvider();
        $tracer = $tracerProvider->getTracer();
        $default = ContextStorage::default();
        $custom = ContextStorage::create('custom'); //@todo this should use the preferred storage type (fiber or non)
        //file_put_contents('php://stdout', '^^ context for custom storage'.PHP_EOL);
        //$context = (new Context())->setStorage($custom);
        $context = $custom->current(); //??
        $root = $tracer->spanBuilder('root')
            //->setParent($context)
            ->setStorage($custom)
            ->startSpan();
        $ctx = $root->activate();
        $foo = $custom->current();
        //$this->assertEquals($ctx, $foo);

        $child = $tracer->spanBuilder('child')
            ->setStorage($custom)
            ->startSpan();
        $child->end();
        $root->end();

        //@todo confirm that child is a child of root
        //@todo further, create a span in the default storage, ensure they don't interfere with each other
    }
}
