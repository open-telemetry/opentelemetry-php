<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Common\Http\Psr\Client\Discovery;

use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use OpenTelemetry\SDK\Common\Http\Psr\Client\Discovery\DiscoveryInterface;
use OpenTelemetry\SDK\Common\Http\Psr\Client\Discovery\Guzzle;
use PHPUnit\Framework\Attributes\CoversClass;
use Psr\Http\Message\RequestInterface;

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
        $this->assertFalse($this->requestOptions(['timeout' => 1])['decode_content'] ?? null);
    }

    public function test_decode_content_can_be_overridden(): void
    {
        $this->assertTrue($this->requestOptions(['decode_content' => true])['decode_content'] ?? null);
    }

    /**
     * @param array<string, mixed> $clientOptions
     * @return array<string, mixed> the request options the client hands to its handler
     */
    private function requestOptions(array $clientOptions): array
    {
        if (!$this->getInstance()->available()) {
            $this->markTestSkipped('Guzzle is not installed');
        }

        $seen = [];
        $client = $this->getInstance()->create($clientOptions + [
            'handler' => static function (RequestInterface $request, array $options) use (&$seen) {
                $seen = $options;

                return new FulfilledPromise(new Response());
            },
        ]);
        $client->sendRequest(new Request('POST', 'http://localhost'));

        return $seen;
    }
}
