<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Common\Http\Psr\Message;

use OpenTelemetry\Context\Propagation\TextMapPropagatorInterface;

trait UsesTextMapPropagatorTrait
{
    use CreatesMockTrait;

    private function createPropagatorMock(): TextMapPropagatorInterface
    {
        $propagator = $this->createMock(TextMapPropagatorInterface::class);
        $propagator->method('inject')
            ->willReturnCallback(static function (array &$carrier) {
                $carrier['foo'] = 'bar';
            });

        return $propagator;
    }
}
