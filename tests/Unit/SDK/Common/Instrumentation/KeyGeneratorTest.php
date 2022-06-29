<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Common\Instrumentation;

use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScope;
use OpenTelemetry\SDK\Common\Instrumentation\KeyGenerator;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\SDK\Common\Instrumentation\KeyGenerator
 */
class KeyGeneratorTest extends TestCase
{
    public function test_equal_instrumentation_scope_return_same_key(): void
    {
        $this->assertSame(
            KeyGenerator::generateInstanceKey(new InstrumentationScope('foo', '0.0.1', null, Attributes::create([]))),
            KeyGenerator::generateInstanceKey(new InstrumentationScope('foo', '0.0.1', null, Attributes::create([]))),
        );
    }

    public function test_non_equal_instrumentation_scope_return_different_keys_name(): void
    {
        $this->assertNotSame(
            KeyGenerator::generateInstanceKey(new InstrumentationScope('foo', '0.0.1', null, Attributes::create([]))),
            KeyGenerator::generateInstanceKey(new InstrumentationScope('bar', '0.0.1', null, Attributes::create([]))),
        );
    }

    public function test_non_equal_instrumentation_scope_return_different_keys_version(): void
    {
        $this->assertNotSame(
            KeyGenerator::generateInstanceKey(new InstrumentationScope('foo', '0.0.1', null, Attributes::create([]))),
            KeyGenerator::generateInstanceKey(new InstrumentationScope('foo', '0.0.2', null, Attributes::create([]))),
        );
    }

    public function test_non_equal_instrumentation_scope_return_different_keys_schemaurl(): void
    {
        $this->assertNotSame(
            KeyGenerator::generateInstanceKey(new InstrumentationScope('foo', '0.0.1', 'https://bar', Attributes::create([]))),
            KeyGenerator::generateInstanceKey(new InstrumentationScope('foo', '0.0.1', 'https://baz', Attributes::create([]))),
        );
    }

    public function test_non_equal_instrumentation_scope_return_different_keys_attributes(): void
    {
        $this->assertNotSame(
            KeyGenerator::generateInstanceKey(new InstrumentationScope('foo', '0.0.1', null, Attributes::create(['foo' => 'bar']))),
            KeyGenerator::generateInstanceKey(new InstrumentationScope('foo', '0.0.1', null, Attributes::create(['foo' => 'baz']))),
        );
    }
}
