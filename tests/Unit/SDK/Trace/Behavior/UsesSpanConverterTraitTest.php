<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Trace\Behavior;

use OpenTelemetry\SDK\Trace\Behavior\UsesSpanConverterTrait;
use OpenTelemetry\SDK\Trace\SpanConverterInterface;
use OpenTelemetry\SDK\Trace\SpanExporter\NullSpanConverter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(UsesSpanConverterTrait::class)]
class UsesSpanConverterTraitTest extends TestCase
{
    public function test_accessors(): void
    {
        $instance = $this->createInstance();
        $converter = $this->createSpanConverterInterfaceMock();

        $instance->doSetSpanConverter($converter);

        $this->assertSame(
            $converter,
            $instance->getSpanConverter()
        );
    }

    public function test_fallback_converter(): void
    {
        $this->assertInstanceOf(
            NullSpanConverter::class,
            $this->createInstance()->getSpanConverter()
        );
    }

    private function createInstance(): object
    {
        return new class() {
            use UsesSpanConverterTrait;
            public function doSetSpanConverter(SpanConverterInterface $converter): void
            {
                $this->setSpanConverter($converter);
            }
        };
    }

    private function createSpanConverterInterfaceMock(): SpanConverterInterface
    {
        return $this->createMock(SpanConverterInterface::class);
    }
}
