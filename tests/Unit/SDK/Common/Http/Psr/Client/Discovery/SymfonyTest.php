<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Common\Http\Psr\Client\Discovery;

use OpenTelemetry\SDK\Common\Http\Psr\Client\Discovery\DiscoveryInterface;
use OpenTelemetry\SDK\Common\Http\Psr\Client\Discovery\Symfony;

#[\PHPUnit\Framework\Attributes\CoversClass(\OpenTelemetry\SDK\Common\Http\Psr\Client\Discovery\Symfony::class)]
class SymfonyTest extends AbstractDiscoveryTestCase
{
    public function getInstance(): DiscoveryInterface
    {
        return new Symfony();
    }
}
