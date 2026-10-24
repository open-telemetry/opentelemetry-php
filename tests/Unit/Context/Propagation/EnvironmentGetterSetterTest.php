<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Context\Propagation;

use OpenTelemetry\Context\Propagation\EnvironmentGetterSetter;
use OpenTelemetry\Tests\TestState;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(EnvironmentGetterSetter::class)]
class EnvironmentGetterSetterTest extends TestCase
{
    use TestState;

    #[\Override]
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
        $this->assertContains('A', $keys);
        $this->assertContains('B', $keys);
    }

    /**
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.61.0/specification/context/env-carriers.md#key-name-normalization
     */
    public function test_keys_returns_only_normalized_names(): void
    {
        $this->setEnvironmentVariable('TRACEPARENT', 'value');
        $this->setEnvironmentVariable('lowercase', 'value');
        $this->setEnvironmentVariable('WITH-DASH', 'value');
        $this->setEnvironmentVariable('1LEADING_DIGIT', 'value');

        $keys = (new EnvironmentGetterSetter())->keys([]);

        $this->assertContains('TRACEPARENT', $keys);
        $this->assertNotContains('lowercase', $keys);
        $this->assertNotContains('LOWERCASE', $keys);
        $this->assertNotContains('WITH-DASH', $keys);
        $this->assertNotContains('1LEADING_DIGIT', $keys);
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

    /**
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.61.0/specification/context/env-carriers.md#key-name-normalization
     */
    public function test_get_empty_key_reads_underscore(): void
    {
        $this->setEnvironmentVariable('_', 'underscore');
        $map = new EnvironmentGetterSetter();

        $this->assertSame('underscore', $map->get([], ''));
    }

    /**
     * @param non-empty-string $key
     *
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.61.0/specification/context/env-carriers.md#key-name-normalization
     */
    #[DataProvider('normalizationProvider')]
    public function test_get_normalizes_key(string $key, string $normalized): void
    {
        $this->setEnvironmentVariable($normalized, 'value');

        $this->assertSame('value', (new EnvironmentGetterSetter())->get([], $key));
        $this->assertSame(['value'], (new EnvironmentGetterSetter())->getAll([], $key));
    }

    /**
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.61.0/specification/context/env-carriers.md#key-name-normalization
     */
    public function test_get_does_not_read_non_normalized_name(): void
    {
        $this->setEnvironmentVariable('x-b3-traceid', 'value');

        $this->assertNull((new EnvironmentGetterSetter())->get([], 'x-b3-traceid'));
    }

    /**
     * @param non-empty-string $key
     *
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.61.0/specification/context/env-carriers.md#key-name-normalization
     */
    #[DataProvider('normalizationProvider')]
    public function test_set_normalizes_key(string $key, string $normalized): void
    {
        $this->setEnvironmentVariable($normalized, null);
        $carrier = [];

        (new EnvironmentGetterSetter())->set($carrier, $key, 'value');

        $this->assertSame('value', getenv($normalized));
    }

    public static function normalizationProvider(): array
    {
        return [
            'lowercase' => ['traceparent', 'TRACEPARENT'],
            'dash' => ['x-b3-traceid', 'X_B3_TRACEID'],
            'dot and space' => ['a.b c', 'A_B_C'],
            'non-ascii' => ["caf\u{e9}", 'CAF_'],
            'invalid utf-8' => ["caf\xe9", 'CAF_'],
            'leading digit' => ['1abc', '_1ABC'],
            'already normalized' => ['_FOO_1', '_FOO_1'],
        ];
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

    /**
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.61.0/specification/context/env-carriers.md#key-name-normalization
     */
    public function test_set_empty_key_writes_underscore(): void
    {
        $this->setEnvironmentVariable('_', null);
        $carrier = [];

        (new EnvironmentGetterSetter())->set($carrier, '', 'alpha');

        $this->assertSame('alpha', getenv('_'));
    }
}
