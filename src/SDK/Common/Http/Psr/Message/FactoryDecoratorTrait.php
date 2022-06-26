<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Http\Psr\Message;

use OpenTelemetry\Context\Propagation\TextMapPropagatorInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;

trait FactoryDecoratorTrait
{
    private TextMapPropagatorInterface $propagator;

    /**
     * @var RequestFactoryInterface|ServerRequestFactoryInterface|ResponseFactoryInterface
     */
    private $decorated;

    public function getPropagator(): TextMapPropagatorInterface
    {
        return $this->propagator;
    }
}
