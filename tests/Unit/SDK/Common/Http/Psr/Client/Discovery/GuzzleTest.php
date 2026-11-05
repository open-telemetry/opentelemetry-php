<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Common\Http\Psr\Client\Discovery;

use OpenTelemetry\SDK\Common\Http\Psr\Client\Discovery\DiscoveryInterface;
use OpenTelemetry\SDK\Common\Http\Psr\Client\Discovery\Guzzle;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Guzzle::class)]
class GuzzleTest extends AbstractDiscoveryTestCase
{
    #[\Override]
    public function getInstance(): DiscoveryInterface
    {
        return new Guzzle();
    }

    public function test_response_is_not_decoded_by_the_client(): void
    {
        $client = $this->getInstance()->create(['timeout' => 1]);
        if (!method_exists($client, 'getConfig')) {
            $this->markTestSkipped('Guzzle client config is not accessible');
        }

        $this->assertFalse($client->getConfig('decode_content'));
    }

    public function test_decode_content_can_be_overridden(): void
    {
        $client = $this->getInstance()->create(['decode_content' => true]);
        if (!method_exists($client, 'getConfig')) {
            $this->markTestSkipped('Guzzle client config is not accessible');
        }

        $this->assertTrue($client->getConfig('decode_content'));
    }
}
