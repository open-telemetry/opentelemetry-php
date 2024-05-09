<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Common\Util;

use LogicException;
use OpenTelemetry\SDK\Common\Util\ClassConstantAccessor;
use PHPUnit\Framework\TestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\OpenTelemetry\SDK\Common\Util\ClassConstantAccessor::class)]
class ClassConstantAccessorTest extends TestCase
{
    public function test_get_value_return_correct_value(): void
    {
        $this->assertSame(
            ClassConstantAccessorTestClass::FOO,
            ClassConstantAccessor::getValue(ClassConstantAccessorTestClass::class, 'FOO')
        );
    }

    public function test_get_value_returns_null_on_non_existing_constant(): void
    {
        $this->assertNull(
            ClassConstantAccessor::getValue(ClassConstantAccessorTestClass::class, 'BAR')
        );
    }

    public function test_require_value_return_correct_value(): void
    {
        $this->assertSame(
            ClassConstantAccessorTestClass::FOO,
            ClassConstantAccessor::requireValue(ClassConstantAccessorTestClass::class, 'FOO')
        );
    }

    public function test_require_value_throws_exception_on_non_existing_constant(): void
    {
        $this->expectException(LogicException::class);

        ClassConstantAccessor::requireValue(ClassConstantAccessorTestClass::class, 'BAR');
    }
}

class ClassConstantAccessorTestClass
{
    public const FOO = 'bar';
}
