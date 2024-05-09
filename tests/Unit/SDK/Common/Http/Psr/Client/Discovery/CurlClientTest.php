<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Common\Http\Psr\Client\Discovery;

use OpenTelemetry\SDK\Common\Http\Psr\Client\Discovery\CurlClient;
use OpenTelemetry\SDK\Common\Http\Psr\Client\Discovery\DiscoveryInterface;

#[\PHPUnit\Framework\Attributes\CoversClass(\OpenTelemetry\SDK\Common\Http\Psr\Client\Discovery\CurlClient::class)]
class CurlClientTest extends AbstractDiscoveryTestCase
{
    public function getInstance(): DiscoveryInterface
    {
        return new CurlClient();
    }
}
