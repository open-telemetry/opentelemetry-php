<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Common\InstrumentationScope;

use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeInterface;
use OpenTelemetry\SDK\Common\InstrumentationScope\Condition;
use OpenTelemetry\SDK\Common\InstrumentationScope\Predicate;
use OpenTelemetry\SDK\Common\InstrumentationScope\State;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Condition::class)]
class ConditionTest extends TestCase
{
    public function test_predicate_match(): void
    {
        $predicate = $this->createMock(Predicate::class);
        $predicate->expects($this->once())->method('match')->willReturn(true);
        $condition = new Condition($predicate, State::DISABLED);
        $this->assertTrue($condition->match($this->createMock(InstrumentationScopeInterface::class)));
    }
}
