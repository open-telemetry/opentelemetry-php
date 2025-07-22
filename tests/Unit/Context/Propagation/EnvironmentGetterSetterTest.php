<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Context\Propagation;

use InvalidArgumentException;
use OpenTelemetry\Context\Propagation\EnvironmentGetterSetter;
use OpenTelemetry\Tests\TestState;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(EnvironmentGetterSetter::class)]
class EnvironmentGetterSetterTest extends TestCase
{
    use TestState;

    protected function tearDown(): void
    {
        $this->restoreEnvironmentVariables();
    }

    public function test_get_instance(): void
    {
        $instance = EnvironmentGetterSetter::getInstance();

        $this->assertInstanceOf(EnvironmentGetterSetter::class, $instance);
    }

    public function test_keys_from_environment(): void
    {
        $carrier = [];
        $this->setEnvironmentVariable('A', 'alpha');
        $this->setEnvironmentVariable('B', 'beta=bravo');

        $map = new EnvironmentGetterSetter();
        $keys = $map->keys($carrier);
        $this->assertContains('a', $keys);
        $this->assertContains('b', $keys);
    }

    public function test_keys_with_empty_environment(): void
    {
        foreach (array_keys(getenv()) as $key) {
            $this->setEnvironmentVariable($key, null);
        }
        $map = new EnvironmentGetterSetter();

        $this->assertSame([], $map->keys([]));
    }

    public function test_get_values_from_environment(): void
    {
        $this->setEnvironmentVariable('A', 'alpha');
        $this->setEnvironmentVariable('B', 'beta');
        $map = new EnvironmentGetterSetter();

        $this->assertSame('alpha', $map->get([], 'a'));
        $this->assertSame('beta', $map->get([], 'b'));
    }

    public function test_for_empty_key(): void
    {
        $map = new EnvironmentGetterSetter();

        $this->assertNull($map->get([], ''));
    }

    public function test_for_get_with_nonexistent_key(): void
    {
        $map = new EnvironmentGetterSetter();

        $this->assertNull($map->get([], 'not_exist'));
    }

    public function test_can_get_integer_value(): void
    {
        $this->setEnvironmentVariable('A', '1');
        $map = new EnvironmentGetterSetter();

        $this->assertSame('1', $map->get([], 'a'));
    }

    public function test_can_get_all_values_from_environment(): void
    {
        $this->setEnvironmentVariable('A', 'alpha');
        $this->setEnvironmentVariable('B', 'beta');
        $map = new EnvironmentGetterSetter();

        $this->assertSame(['alpha'], $map->getAll([], 'a'));
        $this->assertSame(['beta'], $map->getAll([], 'b'));
    }

    public function test_for_get_all_with_nonexistent_key(): void
    {
        $map = new EnvironmentGetterSetter();

        $this->assertSame([], $map->getAll([], 'not_exist'));
    }

    public function test_set_environment(): void
    {
        $carrier = [];
        $map = new EnvironmentGetterSetter();

        $map->set($carrier, 'b', 'beta');
        $this->assertSame('beta', $map->get($carrier, 'b'));
        $this->assertSame('beta', getenv('B'));
    }

    public function test_set_empty_key(): void
    {
        $carrier = [];
        $map = new EnvironmentGetterSetter();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unable to set value with an empty key');
        $map->set($carrier, '', 'alpha');
    }
}
