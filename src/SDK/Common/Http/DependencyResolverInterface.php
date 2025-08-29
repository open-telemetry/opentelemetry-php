<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Http;

use OpenTelemetry\SDK\Common\Http\Psr\Message\FactoryResolverInterface;

interface DependencyResolverInterface extends FactoryResolverInterface, PsrClientResolverInterface, HttpPlugClientResolverInterface
{
}
