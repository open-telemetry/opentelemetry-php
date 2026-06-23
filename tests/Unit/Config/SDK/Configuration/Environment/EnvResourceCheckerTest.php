<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Config\SDK\Configuration\Environment;

use OpenTelemetry\Config\SDK\Configuration\Environment\EnvReader;
use OpenTelemetry\Config\SDK\Configuration\Environment\EnvResource;
use OpenTelemetry\Config\SDK\Configuration\Environment\EnvResourceChecker;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Resource\FileResource;

#[CoversClass(EnvResourceChecker::class)]
final class EnvResourceCheckerTest extends TestCase
{
    public function test_supports_returns_true_for_env_resource(): void
    {
        $envReader = $this->createMock(EnvReader::class);
        $checker = new EnvResourceChecker($envReader);

        $resource = new EnvResource('FOO', 'bar');

        $this->assertTrue($checker->supports($resource));
    }

    public function test_supports_returns_false_for_non_env_resource(): void
    {
        $envReader = $this->createMock(EnvReader::class);
        $checker = new EnvResourceChecker($envReader);

        $resource = new FileResource(__FILE__);

        $this->assertFalse($checker->supports($resource));
    }

    public function test_is_fresh_returns_true_when_value_matches(): void
    {
        $envReader = $this->createMock(EnvReader::class);
        $envReader->method('read')->with('MY_VAR')->willReturn('my_value');

        $checker = new EnvResourceChecker($envReader);
        $resource = new EnvResource('MY_VAR', 'my_value');

        $this->assertTrue($checker->isFresh($resource, time()));
    }

    public function test_is_fresh_returns_false_when_value_changed(): void
    {
        $envReader = $this->createMock(EnvReader::class);
        $envReader->method('read')->with('MY_VAR')->willReturn('new_value');

        $checker = new EnvResourceChecker($envReader);
        $resource = new EnvResource('MY_VAR', 'old_value');

        $this->assertFalse($checker->isFresh($resource, time()));
    }

    public function test_is_fresh_returns_true_when_both_null(): void
    {
        $envReader = $this->createMock(EnvReader::class);
        $envReader->method('read')->with('UNSET_VAR')->willReturn(null);

        $checker = new EnvResourceChecker($envReader);
        $resource = new EnvResource('UNSET_VAR', null);

        $this->assertTrue($checker->isFresh($resource, time()));
    }

    public function test_is_fresh_returns_false_when_env_becomes_unset(): void
    {
        $envReader = $this->createMock(EnvReader::class);
        $envReader->method('read')->with('MY_VAR')->willReturn(null);

        $checker = new EnvResourceChecker($envReader);
        $resource = new EnvResource('MY_VAR', 'was_set');

        $this->assertFalse($checker->isFresh($resource, time()));
    }
}
