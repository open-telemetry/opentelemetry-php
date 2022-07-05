<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Http;

use OpenTelemetry\SDK\Common\Http\HttpPlug\Client\ResolverInterface as HttpPlugClientResolverInterface;
use OpenTelemetry\SDK\Common\Http\Psr\Client\ResolverInterface as PsrClientResolverInterface;
use OpenTelemetry\SDK\Common\Http\Psr\Message\FactoryResolverInterface;

interface DependencyResolverInterface extends FactoryResolverInterface, PsrClientResolverInterface, HttpPlugClientResolverInterface
{
}
