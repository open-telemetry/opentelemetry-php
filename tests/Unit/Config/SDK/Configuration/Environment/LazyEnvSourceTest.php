<?php

declare(strict_types=1);

namespace OpenTelemetry\Test\Unit\Config\SDK\Configuration\Environment;

use OpenTelemetry\Config\SDK\Configuration\Environment\EnvSource;
use OpenTelemetry\Config\SDK\Configuration\Environment\LazyEnvSource;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(LazyEnvSource::class)]
final class LazyEnvSourceTest extends TestCase
{
    public function test_read_raw_resolves_closure_and_delegates(): void
    {
        $innerSource = $this->createMock(EnvSource::class);
        $innerSource->method('readRaw')->with('MY_VAR')->willReturn('value');

        $lazy = new LazyEnvSource(fn () => $innerSource);
        $this->assertSame('value', $lazy->readRaw('MY_VAR'));
    }

    public function test_read_raw_calls_closure_only_once(): void
    {
        $callCount = 0;
        $innerSource = $this->createMock(EnvSource::class);
        $innerSource->method('readRaw')->willReturn('value');

        $lazy = new LazyEnvSource(function () use ($innerSource, &$callCount) {
            $callCount++;

            return $innerSource;
        });

        $lazy->readRaw('VAR1');
        $lazy->readRaw('VAR2');

        $this->assertSame(1, $callCount);
    }

    public function test_read_raw_works_with_env_source_directly(): void
    {
        $innerSource = $this->createMock(EnvSource::class);
        $innerSource->method('readRaw')->with('MY_VAR')->willReturn('direct');

        $lazy = new LazyEnvSource($innerSource);
        $this->assertSame('direct', $lazy->readRaw('MY_VAR'));
    }

    public function test_read_raw_returns_null_when_inner_source_returns_null(): void
    {
        $innerSource = $this->createMock(EnvSource::class);
        $innerSource->method('readRaw')->willReturn(null);

        $lazy = new LazyEnvSource(fn () => $innerSource);
        $this->assertNull($lazy->readRaw('MISSING'));
    }
}
