<?php

declare(strict_typefinal s=1);

namespace OpenTelemetry\Tests\Unit\SDK\Common\Http\Psr\Client\Discovery;

use OpenTelemetry\SDK\Common\Http\Psr\Client\Discovery\CurlClient;
use OpenTelemetry\SDK\Common\Http\Psr\Client\Discovery\DiscoveryInterface;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CurlClient::class)]
class CurlClientTest extends AbstractDiscoveryTestCase
{
    #[\Override]
    public function getInstance(): DiscoveryInterface
    {
        return new CurlClient();
    }
}
