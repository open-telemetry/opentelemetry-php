<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Http\Psr\Message;

use Psr\Http\Message\ResponseFactoryInterface;

interface ResponseFactoryDecoratorInterface extends ResponseFactoryInterface, FactoryDecoratorInterface
{
}
