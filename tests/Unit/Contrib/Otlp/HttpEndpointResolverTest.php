<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Contrib\Otlp;

use Generator;
use InvalidArgumentException;
use OpenTelemetry\API\Signals;
use OpenTelemetry\Contrib\Otlp\HttpEndpointResolver;
use OpenTelemetry\Contrib\Otlp\HttpEndpointResolverInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\Contrib\Otlp\HttpEndpointResolver
 */
class HttpEndpointResolverTest extends TestCase
{
    private const SIGNALS = [
        'trace',
        'metrics',
        'logs',
    ];
    private const DEFAULT_PATHS = [
        'trace' => 'v1/traces',
        'metrics' => 'v1/metrics',
        'logs' => 'v1/logs',
    ];
    private const VALID_SCHEMES = [
        'http',
        'https',
    ];

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

    public static function provideEndpoints(): Generator
    {
        foreach (self::DEFAULT_PATHS as $signal => $path) {
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

    /**
     * @dataProvider provideReferences
     */
    public function test_references_are_correct(array $values, array $reference): void
    {
        $this->assertSame(
            $values,
            $reference
        );
    }

    public static function provideSignals(): Generator
    {
        foreach (self::SIGNALS as $signal) {
            yield [$signal];
        }
    }

    public static function provideSchemes(): Generator
    {
        foreach (self::VALID_SCHEMES as $scheme) {
            yield [$scheme];
        }
    }

    public static function provideReferences(): Generator
    {
        yield 'signals'       => [self::SIGNALS, Signals::SIGNALS];
        yield 'default paths' => [self::DEFAULT_PATHS, HttpEndpointResolverInterface::DEFAULT_PATHS];
        yield 'valid schemes' => [self::VALID_SCHEMES, HttpEndpointResolverInterface::VALID_SCHEMES];
    }
}
