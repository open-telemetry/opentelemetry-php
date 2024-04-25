<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Common\Http\Psr\Client\Discovery;

use OpenTelemetry\SDK\Common\Http\Psr\Client\Discovery\DiscoveryInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;

abstract class AbstractDiscoveryTestCase extends TestCase
{
    abstract public function getInstance(): DiscoveryInterface;

    public function test_available(): void
    {
        $this->expectNotToPerformAssertions();
        $this->getInstance()->available();
    }

    public function test_create(): void
    {
        try {
            $client = $this->getInstance()->create(['timeout' => 1234]);
            $this->assertInstanceOf(ClientInterface::class, $client);
        } catch (\Error) {
            //dependencies may not be installed in test environment
            $this->expectNotToPerformAssertions();
        }
    }
}
