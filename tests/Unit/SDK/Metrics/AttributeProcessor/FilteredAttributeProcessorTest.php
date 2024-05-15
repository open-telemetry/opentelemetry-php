<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Metrics\AttributeProcessor;

use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Metrics\AttributeProcessor\FilteredAttributeProcessor;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(FilteredAttributeProcessor::class)]
final class FilteredAttributeProcessorTest extends TestCase
{
    public function test_attribute_processor_test(): void
    {
        $this->assertEquals(
            ['foo' => 3],
            (new FilteredAttributeProcessor(['foo']))
                ->process(Attributes::create(['foo' => 3, 'bar' => 5]), Context::getRoot())
                ->toArray(),
        );
    }
}
