<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Context\Propagation;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use OpenTelemetry\Context\Propagation\PropagationGetterInterface;
use OpenTelemetry\Context\Propagation\SanitizeCombinedHeadersPropagationGetter;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(SanitizeCombinedHeadersPropagationGetter::class)]
class SanitizeCombinedHeadersPropagationGetterTest extends MockeryTestCase
{
    /** @var Mockery\MockInterface&PropagationGetterInterface */
    private $propagationGetter;

    protected function setUp(): void
    {
        $this->propagationGetter = Mockery::mock(PropagationGetterInterface::class);
    }

    public function test_get_all_from_carrier_with_semicolons(): void
    {
        $carrier = ['a' => ['key1=value1;key2=value2', 'key3=value3']];

        $this->propagationGetter->shouldReceive('getAll')->with($carrier, 'a')->andReturn(['key1=value1;key2=value2', 'key3=value3']);
        $getter = new SanitizeCombinedHeadersPropagationGetter($this->propagationGetter);

        $this->assertSame(['key1=value1,key2=value2', 'key3=value3'], $getter->getAll($carrier, 'a'));
    }

    public function test_get_all_from_carrier_with_leading_commas(): void
    {
        $carrier = ['a' => [',,alpha,beta']];

        $this->propagationGetter->shouldReceive('getAll')->with($carrier, 'a')->andReturn([',,alpha,beta']);
        $getter = new SanitizeCombinedHeadersPropagationGetter($this->propagationGetter);

        $this->assertSame(['alpha,beta'], $getter->getAll($carrier, 'a'));
    }

    public function test_get_all_from_not_existing_key(): void
    {
        $carrier = ['a' => 'alpha'];

        $this->propagationGetter->shouldReceive('getAll')->with($carrier, 'b')->andReturn([]);
        $getter = new SanitizeCombinedHeadersPropagationGetter($this->propagationGetter);

        $this->assertSame([], $getter->getAll($carrier, 'b'));
    }
}
