<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\SDK\Unit\Trace;

use AssertWell\PHPUnitGlobalState\EnvironmentVariables;
use Exception;
use OpenTelemetry\SDK\Trace\SpanLimitsBuilder;
use PHPUnit\Framework\TestCase;

class SpanLimitsBuilderTest extends TestCase
{
    use EnvironmentVariables;

    /**
     * @test
     */
    public function spanLimitsBuilder_usesDefaultValues()
    {
        $builder = new SpanLimitsBuilder();
        $spanLimits = $builder->build();
        $this->assertEquals(128, $spanLimits->getAttributeLimits()->getAttributeCountLimit());
    }

    /**
     * @test
     */
    public function spanLimitsBuilder_usesEnvironmentVariable()
    {
        $this->setEnvironmentVariable('OTEL_SPAN_ATTRIBUTE_COUNT_LIMIT', 111);
        $builder = new SpanLimitsBuilder();
        $spanLimits = $builder->build();
        $this->assertEquals(111, $spanLimits->getAttributeLimits()->getAttributeCountLimit());
    }

    /**
     * @test
     */
    public function spanLimitsBuilder_usesConfiguredValue()
    {
        $this->setEnvironmentVariable('OTEL_SPAN_ATTRIBUTE_COUNT_LIMIT', 111);
        $builder = new SpanLimitsBuilder();
        $builder->setAttributeCountLimit(222);
        $spanLimits = $builder->build();
        $this->assertEquals(222, $spanLimits->getAttributeLimits()->getAttributeCountLimit());
    }

    /**
     * @test
     */
    public function spanLimitsBuilder_throwsExceptionOnInvalidValueFromEnvironment()
    {
        $this->setEnvironmentVariable('OTEL_SPAN_ATTRIBUTE_COUNT_LIMIT', 'fruit');
        $builder = new SpanLimitsBuilder();
        $this->expectException(Exception::class);
        $builder->build();
    }
}
