<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Http\Psr\Message;

use Psr\Http\Message\ServerRequestFactoryInterface;

interface ServerRequestFactoryDecoratorInterface extends ServerRequestFactoryInterface, FactoryDecoratorInterface
{
}
