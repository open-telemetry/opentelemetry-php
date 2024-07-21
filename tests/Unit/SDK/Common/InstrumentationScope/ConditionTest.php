<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Common\InstrumentationScope;

use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScope;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeInterface;
use OpenTelemetry\SDK\Common\InstrumentationScope\Condition;
use OpenTelemetry\SDK\Common\InstrumentationScope\Predicate;
use OpenTelemetry\SDK\Common\InstrumentationScope\State;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Condition::class)]
#[CoversClass(Predicate::class)]
class ConditionTest extends TestCase
{
    public function test_predicate_match(): void
    {
        $predicate = $this->createMock(Predicate::class);
        $predicate->expects($this->once())->method('match')->willReturn(true);
        $condition = new Condition($predicate, State::DISABLED);
        $this->assertTrue($condition->match($this->createMock(InstrumentationScopeInterface::class)));
    }

    #[DataProvider('conditionsProvider')]
    public function test_conditions(Predicate $predicate, bool $match): void
    {
        $condition = new Condition($predicate, State::ENABLED);
        $scope = new InstrumentationScope('two', null, null, Attributes::create(['foo' => 'bar']));
        $this->assertSame($match, $condition->match($scope));
    }

    public static function conditionsProvider(): array
    {
        return [
            'match name' => [new Predicate\Name('~two~'), true],
            'no match name' => [new Predicate\Name('~one~'), false],
            'attribute exists' => [new Predicate\AttributeExists('foo'), true],
            'attribute does not exist' => [new Predicate\Attribute('bar', 'anything'), false],
            'attributes matches' => [new Predicate\Attribute('foo', 'bar'), true],
            'attribute does not match' => [new Predicate\Attribute('foo', 'no-match'), false],
        ];
    }
}
