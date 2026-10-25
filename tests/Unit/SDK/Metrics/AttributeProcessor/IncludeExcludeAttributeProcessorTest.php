<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Metrics\AttributeProcessor;

use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Metrics\AttributeProcessor\IncludeExcludeAttributeProcessor;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(IncludeExcludeAttributeProcessor::class)]
final class IncludeExcludeAttributeProcessorTest extends TestCase
{
    /**
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.62.0/specification/metrics/sdk.md#stream-configuration
     *
     * @param list<string>|null $included
     * @param list<string>|null $excluded
     */
    #[DataProvider('filterProvider')]
    public function test_filters_attribute_keys(?array $included, ?array $excluded, array $expected): void
    {
        $this->assertSame(
            $expected,
            (new IncludeExcludeAttributeProcessor($included, $excluded))
                ->process(Attributes::create(['foo' => 1, 'foobar' => 2, 'fob' => 3, 'bar' => 4, 'a.b' => 5, 'axb' => 6]), Context::getRoot())
                ->toArray(),
        );
    }

    public static function filterProvider(): iterable
    {
        yield 'exact included' => [['foo', 'bar'], null, ['foo' => 1, 'bar' => 4]];
        yield 'star included' => [['foo*'], null, ['foo' => 1, 'foobar' => 2]];
        yield 'question mark included' => [['fo?'], null, ['foo' => 1, 'fob' => 3]];
        yield 'regex characters are literal' => [['a.b'], null, ['a.b' => 5]];
        yield 'excluded only' => [null, ['foo*', 'a?b'], ['fob' => 3, 'bar' => 4]];
        yield 'excluded wins' => [['foo*', 'bar'], ['foobar', 'bar'], ['foo' => 1]];
        yield 'empty included keeps none' => [[], null, []];
        yield 'neither keeps all' => [null, null, ['foo' => 1, 'foobar' => 2, 'fob' => 3, 'bar' => 4, 'a.b' => 5, 'axb' => 6]];
    }
}
