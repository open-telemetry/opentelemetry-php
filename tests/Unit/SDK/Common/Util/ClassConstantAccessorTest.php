<?php

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
    public function testRequireValueWithExistingConstant(): void
    {
        $value = ClassConstantAccessor::requireValue(self::class, 'TEST_CONSTANT');
        
        $this->assertEquals('test_value', $value);
    }

    /**
     * @covers \OpenTelemetry\SDK\Common\Util\ClassConstantAccessor::requireValue
     */
    public function testRequireValueWithNonExistingConstantThrowsException(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('The class "OpenTelemetry\Tests\Unit\SDK\Common\Util\ClassConstantAccessorTest" does not have a constant "NON_EXISTING_CONSTANT"');
        
        ClassConstantAccessor::requireValue(self::class, 'NON_EXISTING_CONSTANT');
    }

    /**
     * @covers \OpenTelemetry\SDK\Common\Util\ClassConstantAccessor::getValue
     */
    public function testGetValueWithExistingConstant(): void
    {
        $value = ClassConstantAccessor::getValue(self::class, 'TEST_CONSTANT');
        
        $this->assertEquals('test_value', $value);
    }

    /**
     * @covers \OpenTelemetry\SDK\Common\Util\ClassConstantAccessor::getValue
     */
    public function testGetValueWithNonExistingConstantReturnsNull(): void
    {
        $value = ClassConstantAccessor::getValue(self::class, 'NON_EXISTENT_CONSTANT');
        
        $this->assertNull($value);
    }

    /**
     * @covers \OpenTelemetry\SDK\Common\Util\ClassConstantAccessor::getValue
     */
    public function testGetValueWithEmptyClassName(): void
    {
        $value = ClassConstantAccessor::getValue('', 'TEST_CONSTANT');
        
        $this->assertNull($value);
    }

    /**
     * @covers \OpenTelemetry\SDK\Common\Util\ClassConstantAccessor::getValue
     */
    public function testGetValueWithEmptyConstantName(): void
    {
        $value = ClassConstantAccessor::getValue(self::class, '');
        
        $this->assertNull($value);
    }

    /**
     * @covers \OpenTelemetry\SDK\Common\Util\ClassConstantAccessor::requireValue
     */
    public function testRequireValueWithEmptyClassNameThrowsException(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('The class "" does not have a constant "TEST_CONSTANT"');
        
        ClassConstantAccessor::requireValue('', 'TEST_CONSTANT');
    }

    /**
     * @covers \OpenTelemetry\SDK\Common\Util\ClassConstantAccessor::requireValue
     */
    public function testRequireValueWithEmptyConstantNameThrowsException(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('The class "OpenTelemetry\Tests\Unit\SDK\Common\Util\ClassConstantAccessorTest" does not have a constant ""');
        
        ClassConstantAccessor::requireValue(self::class, '');
    }

    /**
     * @covers \OpenTelemetry\SDK\Common\Util\ClassConstantAccessor::getValue
     */
    public function testGetValueWithNumericConstant(): void
    {
        $value = ClassConstantAccessor::getValue(self::class, 'NUMERIC_CONSTANT');
        
        $this->assertEquals(42, $value);
    }

    /**
     * @covers \OpenTelemetry\SDK\Common\Util\ClassConstantAccessor::getValue
     */
    public function testGetValueWithBooleanConstant(): void
    {
        $value = ClassConstantAccessor::getValue(self::class, 'BOOLEAN_CONSTANT');
        
        $this->assertTrue($value);
    }

    /**
     * @covers \OpenTelemetry\SDK\Common\Util\ClassConstantAccessor::getValue
     */
    public function testGetValueWithArrayConstant(): void
    {
        $value = ClassConstantAccessor::getValue(self::class, 'ARRAY_CONSTANT');
        
        $this->assertEquals(['key' => 'value'], $value);
    }

    /**
     * @covers \OpenTelemetry\SDK\Common\Util\ClassConstantAccessor::getValue
     */
    public function testGetValueWithNullConstant(): void
    {
        $value = ClassConstantAccessor::getValue(self::class, 'NULL_CONSTANT');
        
        $this->assertNull($value);
    }

    /**
     * @covers \OpenTelemetry\SDK\Common\Util\ClassConstantAccessor::requireValue
     */
    public function testRequireValueWithNumericConstant(): void
    {
        $value = ClassConstantAccessor::requireValue(self::class, 'NUMERIC_CONSTANT');
        
        $this->assertEquals(42, $value);
    }

    /**
     * @covers \OpenTelemetry\SDK\Common\Util\ClassConstantAccessor::requireValue
     */
    public function testRequireValueWithBooleanConstant(): void
    {
        $value = ClassConstantAccessor::requireValue(self::class, 'BOOLEAN_CONSTANT');
        
        $this->assertTrue($value);
    }

    /**
     * @covers \OpenTelemetry\SDK\Common\Util\ClassConstantAccessor::requireValue
     */
    public function testRequireValueWithArrayConstant(): void
    {
        $value = ClassConstantAccessor::requireValue(self::class, 'ARRAY_CONSTANT');
        
        $this->assertEquals(['key' => 'value'], $value);
    }

    /**
     * @covers \OpenTelemetry\SDK\Common\Util\ClassConstantAccessor::requireValue
     */
    public function testRequireValueWithNullConstant(): void
    {
        $value = ClassConstantAccessor::requireValue(self::class, 'NULL_CONSTANT');
        
        $this->assertNull($value);
    }

    /**
     * @covers \OpenTelemetry\SDK\Common\Util\ClassConstantAccessor::getValue
     */
    public function testGetValueWithExternalClass(): void
    {
        $value = ClassConstantAccessor::getValue(self::class, 'TEST_CONSTANT');
        
        $this->assertEquals('test_value', $value);
    }

    /**
     * @covers \OpenTelemetry\SDK\Common\Util\ClassConstantAccessor::requireValue
     */
    public function testRequireValueWithExternalClass(): void
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
