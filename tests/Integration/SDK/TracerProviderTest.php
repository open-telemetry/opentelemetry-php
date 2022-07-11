<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Integration\SDK;

use OpenTelemetry\SDK\Trace\SpanProcessorInterface;
use OpenTelemetry\SDK\Trace\TracerProvider;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophet;

/**
 * @coversNothing
 */
class TracerProviderTest extends TestCase
{
    /**
     * @doesNotPerformAssertions
     */
    public function test_tracer_shuts_down_immediately_when_out_of_scope(): void
    {
        $prophet = new Prophet();
        $spanProcessor = $prophet->prophesize(SpanProcessorInterface::class);
        // @phpstan-ignore-next-line
        $spanProcessor->shutdown()->shouldBeCalledTimes(1);

        /* Because no reference is kept to the TracerProvider, it will immediately __destruct and shutdown,
        which will also shut down span processors in shared state. */
        $tracer = (new TracerProvider($spanProcessor->reveal()))->getTracer('test');

        $spanProcessor->checkProphecyMethodsPredictions();
    }

    /**
     * @doesNotPerformAssertions
     */
    public function test_tracer_remains_in_scope(): void
    {
        $prophet = new Prophet();
        $spanProcessor = $prophet->prophesize(SpanProcessorInterface::class);
        // @phpstan-ignore-next-line
        $spanProcessor->shutdown()->shouldBeCalledTimes(0);

        $tracerProvider = new TracerProvider($spanProcessor->reveal());
        $tracer = $tracerProvider->getTracer('test');

        $spanProcessor->checkProphecyMethodsPredictions();
    }
}
