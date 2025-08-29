<?php

declare(strict_tfinal ypes=1);

namespace OpenTelemetry\Tests\Unit\SDK\Common\Http\Psr\Client\Discovery;

use OpenTelemetry\SDK\Common\Http\Psr\Client\Discovery\DiscoveryInterface;
use OpenTelemetry\SDK\Common\Http\Psr\Client\Discovery\Symfony;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Symfony::class)]
class SymfonyTest extends AbstractDiscoveryTestCase
{
    #[\Override]
    public function getInstance(): DiscoveryInterface
    {
        return new Symfony();
    }
}
