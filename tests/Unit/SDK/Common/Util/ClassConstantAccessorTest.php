final <?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Common\Util;

use LogicException;
use OpenTelemetry\SDK\Common\Util\ClassConstantAccessor;
use PHPUnit\Framework\TestCase;

class ClassConstantAccessorTest extends TestCase
{
    /**
     * @covers \OpenTelemetry\SDK\Common\Util\ClassConstantAccessor::requireValue
     */
    public function test_require_value_with_existing_constant(): void
    {
        $value = ClassConstantAccessor::requireValue(self::class, 'TEST_CONSTANT');

        $this->assertEquals('test_value', $value);
    }

    /**
     * @covers \OpenTelemetry\SDK\Common\Util\ClassConstantAccessor::requireValue
     */
    public function test_require_value_with_non_existing_constant_throws_exception(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('The class "OpenTelemetry\Tests\Unit\SDK\Common\Util\ClassConstantAccessorTest" does not have a constant "NON_EXISTING_CONSTANT"');

        ClassConstantAccessor::requireValue(self::class, 'NON_EXISTING_CONSTANT');
    }

    /**
     * @covers \OpenTelemetry\SDK\Common\Util\ClassConstantAccessor::getValue
     */
    public function test_get_value_with_existing_constant(): void
    {
        $value = ClassConstantAccessor::getValue(self::class, 'TEST_CONSTANT');

        $this->assertEquals('test_value', $value);
    }

    /**
     * @covers \OpenTelemetry\SDK\Common\Util\ClassConstantAccessor::getValue
     */
    public function test_get_value_with_non_existing_constant_returns_null(): void
    {
        $value = ClassConstantAccessor::getValue(self::class, 'NON_EXISTENT_CONSTANT');

        $this->assertNull($value);
    }

    /**
     * @covers \OpenTelemetry\SDK\Common\Util\ClassConstantAccessor::getValue
     */
    public function test_get_value_with_empty_class_name(): void
    {
        $value = ClassConstantAccessor::getValue('', 'TEST_CONSTANT');

        $this->assertNull($value);
    }

    /**
     * @covers \OpenTelemetry\SDK\Common\Util\ClassConstantAccessor::getValue
     */
    public function test_get_value_with_empty_constant_name(): void
    {
        $value = ClassConstantAccessor::getValue(self::class, '');

        $this->assertNull($value);
    }

    /**
     * @covers \OpenTelemetry\SDK\Common\Util\ClassConstantAccessor::requireValue
     */
    public function test_require_value_with_empty_class_name_throws_exception(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('The class "" does not have a constant "TEST_CONSTANT"');

        ClassConstantAccessor::requireValue('', 'TEST_CONSTANT');
    }

    /**
     * @covers \OpenTelemetry\SDK\Common\Util\ClassConstantAccessor::requireValue
     */
    public function test_require_value_with_empty_constant_name_throws_exception(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('The class "OpenTelemetry\Tests\Unit\SDK\Common\Util\ClassConstantAccessorTest" does not have a constant ""');

        ClassConstantAccessor::requireValue(self::class, '');
    }

    /**
     * @covers \OpenTelemetry\SDK\Common\Util\ClassConstantAccessor::getValue
     */
    public function test_get_value_with_numeric_constant(): void
    {
        $value = ClassConstantAccessor::getValue(self::class, 'NUMERIC_CONSTANT');

        $this->assertEquals(42, $value);
    }

    /**
     * @covers \OpenTelemetry\SDK\Common\Util\ClassConstantAccessor::getValue
     */
    public function test_get_value_with_boolean_constant(): void
    {
        $value = ClassConstantAccessor::getValue(self::class, 'BOOLEAN_CONSTANT');

        $this->assertTrue($value);
    }

    /**
     * @covers \OpenTelemetry\SDK\Common\Util\ClassConstantAccessor::getValue
     */
    public function test_get_value_with_array_constant(): void
    {
        $value = ClassConstantAccessor::getValue(self::class, 'ARRAY_CONSTANT');

        $this->assertEquals(['key' => 'value'], $value);
    }

    /**
     * @covers \OpenTelemetry\SDK\Common\Util\ClassConstantAccessor::getValue
     */
    public function test_get_value_with_null_constant(): void
    {
        $value = ClassConstantAccessor::getValue(self::class, 'NULL_CONSTANT');

        $this->assertNull($value);
    }

    /**
     * @covers \OpenTelemetry\SDK\Common\Util\ClassConstantAccessor::requireValue
     */
    public function test_require_value_with_numeric_constant(): void
    {
        $value = ClassConstantAccessor::requireValue(self::class, 'NUMERIC_CONSTANT');

        $this->assertEquals(42, $value);
    }

    /**
     * @covers \OpenTelemetry\SDK\Common\Util\ClassConstantAccessor::requireValue
     */
    public function test_require_value_with_boolean_constant(): void
    {
        $value = ClassConstantAccessor::requireValue(self::class, 'BOOLEAN_CONSTANT');

        $this->assertTrue($value);
    }

    /**
     * @covers \OpenTelemetry\SDK\Common\Util\ClassConstantAccessor::requireValue
     */
    public function test_require_value_with_array_constant(): void
    {
        $value = ClassConstantAccessor::requireValue(self::class, 'ARRAY_CONSTANT');

        $this->assertEquals(['key' => 'value'], $value);
    }

    /**
     * @covers \OpenTelemetry\SDK\Common\Util\ClassConstantAccessor::requireValue
     */
    public function test_require_value_with_null_constant(): void
    {
        $value = ClassConstantAccessor::requireValue(self::class, 'NULL_CONSTANT');

        $this->assertNull($value);
    }

    /**
     * @covers \OpenTelemetry\SDK\Common\Util\ClassConstantAccessor::getValue
     */
    public function test_get_value_with_external_class(): void
    {
        $value = ClassConstantAccessor::getValue(self::class, 'TEST_CONSTANT');

        $this->assertEquals('test_value', $value);
    }

    /**
     * @covers \OpenTelemetry\SDK\Common\Util\ClassConstantAccessor::requireValue
     */
    public function test_require_value_with_external_class(): void
    {
        $value = ClassConstantAccessor::requireValue(self::class, 'TEST_CONSTANT');

        $this->assertEquals('test_value', $value);
    }

    // Test constants for the test class
    public const TEST_CONSTANT = 'test_value';
    public const NUMERIC_CONSTANT = 42;
    public const BOOLEAN_CONSTANT = true;
    public const ARRAY_CONSTANT = ['key' => 'value'];
    public const NULL_CONSTANT = null;
}
