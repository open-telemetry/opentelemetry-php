<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Common\Http\Psr\Client\Discovery;

use OpenTelemetry\SDK\Common\Http\Psr\Client\Discovery\DiscoveryInterface;
use OpenTelemetry\SDK\Common\Http\Psr\Client\Discovery\Symfony;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpClient\Psr18Client;

#[CoversClass(Symfony::class)]
class SymfonyTest extends AbstractDiscoveryTestCase
{
    #[\Override]
    public function getInstance(): DiscoveryInterface
    {
        return new Symfony();
    }

    /**
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.62.0/specification/configuration/common.md#timeout
     */
    public function test_zero_timeout_means_no_idle_timeout(): void
    {
        if (!$this->getInstance()->available()) {
            $this->markTestSkipped('symfony/http-client is not installed');
        }

        $client = (new \ReflectionProperty(Psr18Client::class, 'client'))->getValue($this->getInstance()->create(['timeout' => 0.]));
        $options = (new \ReflectionProperty($client, 'defaultOptions'))->getValue($client);

        $this->assertGreaterThan(0, $options['timeout']);
        $this->assertSame(0., $options['max_duration']);
    }

    public function test_uses_tls_options(): void
    {
        if (!$this->getInstance()->available()) {
            $this->markTestSkipped('symfony/http-client is not installed');
        }

        $client = (new \ReflectionProperty(Psr18Client::class, 'client'))->getValue($this->getInstance()->create([
            'timeout' => 1.,
            'verify' => '/ca.pem',
            'cafile' => '/ca.pem',
            'cert' => '/cert.pem',
            'local_cert' => '/cert.pem',
            'ssl_key' => '/key.pem',
            'local_pk' => '/key.pem',
        ]));
        $options = (new \ReflectionProperty($client, 'defaultOptions'))->getValue($client);

        $this->assertSame('/ca.pem', $options['cafile']);
        $this->assertSame('/cert.pem', $options['local_cert']);
        $this->assertSame('/key.pem', $options['local_pk']);
    }
}
