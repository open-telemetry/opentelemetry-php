<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Common\Otlp;

use Generator;
use InvalidArgumentException;
use OpenTelemetry\API\Common\Signal\Signals;
use OpenTelemetry\SDK\Common\Otlp\HttpEndpointResolver;
use OpenTelemetry\SDK\Common\Otlp\HttpEndpointResolverInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\SDK\Common\Otlp\HttpEndpointResolver
 */
class HttpEndpointResolverTest extends TestCase
{
    /**
     * @dataProvider provideEndpoints
     */
    public function test_normalize(string $baseEndpoint, string $signal, string $expectedEndpoint): void
    {
        $this->assertSame(
            $expectedEndpoint,
            HttpEndpointResolver::create()
                ->resolveToString($baseEndpoint, $signal)
        );
    }

    /**
     * @dataProvider provideSignals
     */
    public function test_normalize_throws_exception_on_invalid_scheme(string $signal): void
    {
        $this->expectException(InvalidArgumentException::class);

        HttpEndpointResolver::create()
            ->resolve('foo://collector', $signal);
    }

    /**
     * @dataProvider provideSchemes
     */
    public function test_normalize_throws_exception_on_invalid_signal(string $scheme): void
    {
        $this->expectException(InvalidArgumentException::class);

        HttpEndpointResolver::create()
            ->resolve($scheme . '://collector', 'foo');
    }

    /**
     * @dataProvider provideSignals
     */
    public function test_normalize_throws_exception_on_invalid_url(string $signal): void
    {
        $this->expectException(InvalidArgumentException::class);

        HttpEndpointResolver::create()
            ->resolve('/\/', $signal);
    }

    public function provideEndpoints(): Generator
    {
        foreach (HttpEndpointResolverInterface::DEFAULT_PATHS as $signal => $path) {
            $baseEndpoint = 'http://collector';
            yield [$baseEndpoint, $signal, sprintf('%s/%s', $baseEndpoint, $path)];

            $baseEndpoint = 'http://root@collector';
            yield [$baseEndpoint, $signal, sprintf('%s/%s', $baseEndpoint, $path)];

            $baseEndpoint = 'http://root:secret@collector';
            yield [$baseEndpoint, $signal, sprintf('%s/%s', $baseEndpoint, $path)];

            $baseEndpoint = 'http://collector:4318';
            yield [$baseEndpoint, $signal, sprintf('%s/%s', $baseEndpoint, $path)];

            $baseEndpoint = 'http://collector:4318/';
            yield [$baseEndpoint, $signal, $baseEndpoint . $path];

            $baseEndpoint = 'http://collector:4318/custom/path';
            yield [$baseEndpoint, $signal, sprintf('%s/%s', $baseEndpoint, $path)];

            $baseEndpoint = 'http://collector:4318/custom/path/';
            yield [$baseEndpoint, $signal, $baseEndpoint . $path];
        }
    }

    public function provideSignals(): Generator
    {
        foreach (Signals::SIGNALS as $signal) {
            yield [$signal];
        }
    }

    public function provideSchemes(): Generator
    {
        foreach (HttpEndpointResolverInterface::VALID_SCHEMES as $scheme) {
            yield [$scheme];
        }
    }
}
