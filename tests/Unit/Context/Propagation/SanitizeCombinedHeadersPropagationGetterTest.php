<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Context\Propagation;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use OpenTelemetry\Context\Propagation\ExtendedPropagationGetterInterface;
use OpenTelemetry\Context\Propagation\PropagationGetterInterface;
use OpenTelemetry\Context\Propagation\SanitizeCombinedHeadersPropagationGetter;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(SanitizeCombinedHeadersPropagationGetter::class)]
class SanitizeCombinedHeadersPropagationGetterTest extends MockeryTestCase
{
    /** @var Mockery\MockInterface&PropagationGetterInterface */
    private $propagationGetter;

    /** @var Mockery\MockInterface&ExtendedPropagationGetterInterface */
    private $extendedPropagationGetter;

    #[\Override]
    protected function setUp(): void
    {
        $this->propagationGetter = Mockery::mock(PropagationGetterInterface::class);
        $this->extendedPropagationGetter = Mockery::mock(ExtendedPropagationGetterInterface::class);
    }

    public function test_get_all_from_carrier_with_semicolons(): void
    {
        $carrier = ['a' => ['key1=value1;key2=value2', 'key3=value3']];

        $this->extendedPropagationGetter->shouldReceive('getAll')->with($carrier, 'a')->andReturn(['key1=value1;key2=value2', 'key3=value3']);
        $getter = new SanitizeCombinedHeadersPropagationGetter($this->extendedPropagationGetter);

        $this->assertSame(['key1=value1,key2=value2', 'key3=value3'], $getter->getAll($carrier, 'a'));
    }

    public function test_get_all_from_carrier_with_leading_commas(): void
    {
        $carrier = ['a' => [',,alpha,beta']];

        $this->extendedPropagationGetter->shouldReceive('getAll')->with($carrier, 'a')->andReturn([',,alpha,beta']);
        $getter = new SanitizeCombinedHeadersPropagationGetter($this->extendedPropagationGetter);

        $this->assertSame(['alpha,beta'], $getter->getAll($carrier, 'a'));
    }

    public function test_get_all_from_not_existing_key(): void
    {
        $carrier = ['a' => 'alpha'];

        $this->extendedPropagationGetter->shouldReceive('getAll')->with($carrier, 'b')->andReturn([]);
        $getter = new SanitizeCombinedHeadersPropagationGetter($this->extendedPropagationGetter);

        $this->assertSame([], $getter->getAll($carrier, 'b'));
    }

    public function test_get_all_from_carrier_without_implement_extended_getter(): void
    {
        $carrier = ['a' => 'alpha'];

        $this->propagationGetter->shouldReceive('get')->with($carrier, 'a')->andReturn('alpha');
        $getter = new SanitizeCombinedHeadersPropagationGetter($this->propagationGetter);

        $this->assertSame(['alpha'], $getter->getAll($carrier, 'a'));
    }
}
