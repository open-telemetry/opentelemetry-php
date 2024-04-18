<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Common\Http\Psr\Client\Discovery;

use OpenTelemetry\SDK\Common\Http\Psr\Client\Discovery\Buzz;
use OpenTelemetry\SDK\Common\Http\Psr\Client\Discovery\DiscoveryInterface;

/**
 * @covers \OpenTelemetry\SDK\Common\Http\Psr\Client\Discovery\Buzz
 */
class BuzzTest extends AbstractDiscoveryTestCase
{
    public function getInstance(): DiscoveryInterface
    {
        return new Buzz();
    }
}
