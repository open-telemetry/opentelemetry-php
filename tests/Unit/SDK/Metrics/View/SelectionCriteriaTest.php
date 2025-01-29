<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Metrics\View;

use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScope;
use OpenTelemetry\SDK\Metrics\Instrument;
use OpenTelemetry\SDK\Metrics\InstrumentType;
use OpenTelemetry\SDK\Metrics\View\SelectionCriteria\AllCriteria;
use OpenTelemetry\SDK\Metrics\View\SelectionCriteria\InstrumentationScopeNameCriteria;
use OpenTelemetry\SDK\Metrics\View\SelectionCriteria\InstrumentationScopeSchemaUrlCriteria;
use OpenTelemetry\SDK\Metrics\View\SelectionCriteria\InstrumentationScopeVersionCriteria;
use OpenTelemetry\SDK\Metrics\View\SelectionCriteria\InstrumentNameCriteria;
use OpenTelemetry\SDK\Metrics\View\SelectionCriteria\InstrumentTypeCriteria;
use OpenTelemetry\SDK\Metrics\View\SelectionCriteriaInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

#[CoversClass(InstrumentationScopeNameCriteria::class)]
#[CoversClass(InstrumentationScopeVersionCriteria::class)]
#[CoversClass(InstrumentationScopeSchemaUrlCriteria::class)]
#[CoversClass(InstrumentNameCriteria::class)]
#[CoversClass(InstrumentTypeCriteria::class)]
#[CoversClass(AllCriteria::class)]
final class SelectionCriteriaTest extends TestCase
{
    use ProphecyTrait;

    public function test_instrument_scope_name_criteria(): void
    {
        $this->assertTrue((new InstrumentationScopeNameCriteria('scopeName'))->accepts(
            new Instrument(InstrumentType::COUNTER, 'name', null, null),
            new InstrumentationScope('scopeName', null, null, Attributes::create([])),
        ));
        $this->assertFalse((new InstrumentationScopeNameCriteria('scopeName'))->accepts(
            new Instrument(InstrumentType::COUNTER, 'name', null, null),
            new InstrumentationScope('scope-name', null, null, Attributes::create([])),
        ));
    }

    public function test_instrument_scope_version_criteria(): void
    {
        $this->assertTrue((new InstrumentationScopeVersionCriteria('1.0.0'))->accepts(
            new Instrument(InstrumentType::COUNTER, 'name', null, null),
            new InstrumentationScope('scopeName', '1.0.0', null, Attributes::create([])),
        ));
        $this->assertFalse((new InstrumentationScopeVersionCriteria('1.0.0'))->accepts(
            new Instrument(InstrumentType::COUNTER, 'name', null, null),
            new InstrumentationScope('scopeName', '2.0.0', null, Attributes::create([])),
        ));
    }

    public function test_instrument_scope_schema_url_criteria(): void
    {
        $this->assertTrue((new InstrumentationScopeSchemaUrlCriteria('https://schema-url.test/1.0'))->accepts(
            new Instrument(InstrumentType::COUNTER, 'name', null, null),
            new InstrumentationScope('scopeName', null, 'https://schema-url.test/1.0', Attributes::create([])),
        ));
        $this->assertFalse((new InstrumentationScopeSchemaUrlCriteria('https://schema-url.test/1.0'))->accepts(
            new Instrument(InstrumentType::COUNTER, 'name', null, null),
            new InstrumentationScope('scopeName', null, 'https://schema-url.test/2.0', Attributes::create([])),
        ));
    }

    /**
     * @param non-empty-string $pattern
     */
    #[DataProvider('instrumentNameProvider')]
    public function test_instrument_name_criteria(string $pattern, string $name, bool $expected): void
    {
        $this->assertSame($expected, (new InstrumentNameCriteria($pattern))->accepts(
            new Instrument(InstrumentType::COUNTER, $name, null, null),
            new InstrumentationScope('scopeName', null, null, Attributes::create([])),
        ));
    }

    public static function instrumentNameProvider(): iterable
    {
        yield 'exact - matching' => ['foobar', 'foobar', true];
        yield 'exact - not matching' => ['foobar', 'foobaz', false];
        yield 'wildcard ? - matching' => ['foo?ar', 'foobar', true];
        yield 'wildcard ? - not matching' => ['foo?ar', 'foobaz', false];
        yield 'wildcard ? - not matching (too many)' => ['foo?ar', 'foobaar', false];
        yield 'wildcard * - matching' => ['foo*ar', 'foobar', true];
        yield 'wildcard * - matching (multiple character)' => ['foo*ar', 'foobaar', true];
        yield 'wildcard * - matching (no character)' => ['foo*ar', 'fooar', true];
        yield 'wildcard * - not matching' => ['foo*ar', 'foobaz', false];
        yield 'match all - matching' => ['*', 'foobar', true];
    }

    public function test_instrument_type_criteria_wildcard(): void
    {
        $this->assertTrue((new InstrumentTypeCriteria([InstrumentType::COUNTER, InstrumentType::HISTOGRAM]))->accepts(
            new Instrument(InstrumentType::COUNTER, 'name', null, null),
            new InstrumentationScope('scopeName', null, null, Attributes::create([])),
        ));
        $this->assertTrue((new InstrumentTypeCriteria(InstrumentType::COUNTER))->accepts(
            new Instrument(InstrumentType::COUNTER, 'name', null, null),
            new InstrumentationScope('scopeName', null, null, Attributes::create([])),
        ));
        $this->assertFalse((new InstrumentTypeCriteria(InstrumentType::COUNTER))->accepts(
            new Instrument(InstrumentType::HISTOGRAM, 'name', null, null),
            new InstrumentationScope('scopeName', null, null, Attributes::create([])),
        ));
    }

    public function test_all_criteria_accepts_if_all_criteria_accept(): void
    {
        $instrument = new Instrument(InstrumentType::COUNTER, 'name', null, null);
        $instrumentScope = new InstrumentationScope('scopeName', null, null, Attributes::create([]));

        $criterias = [];
        for ($i = 0; $i < 3; $i++) {
            $criteria = $this->prophesize(SelectionCriteriaInterface::class);
            /** @noinspection PhpUndefinedMethodInspection */
            /** @phpstan-ignore-next-line */
            $criteria
                ->accepts()
                ->shouldBeCalledOnce()
                ->withArguments([$instrument, $instrumentScope])
                ->willReturn(true);
            $criterias[] = $criteria->reveal();
        }

        $this->assertTrue((new AllCriteria($criterias))->accepts($instrument, $instrumentScope));
    }

    public function test_all_criteria_rejects_if_any_criteria_rejects(): void
    {
        $instrument = new Instrument(InstrumentType::COUNTER, 'name', null, null);
        $instrumentScope = new InstrumentationScope('scopeName', null, null, Attributes::create([]));

        $criterias = [];
        for ($i = 0; $i < 3; $i++) {
            $criteria = $this->prophesize(SelectionCriteriaInterface::class);
            /** @noinspection PhpUndefinedMethodInspection */
            /** @phpstan-ignore-next-line */
            $criteria
                ->accepts()
                ->withArguments([$instrument, $instrumentScope])
                ->willReturn(($i & 1) !== 0);
            $criterias[] = $criteria->reveal();
        }

        $this->assertFalse((new AllCriteria($criterias))->accepts($instrument, $instrumentScope));
    }
}
