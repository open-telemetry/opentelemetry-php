<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Config\SDK\Configuration\Internal;

use OpenTelemetry\Config\SDK\Configuration\Environment\EnvReader;
use OpenTelemetry\Config\SDK\Configuration\Internal\EnvSubstitutionNormalization;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\BooleanNodeDefinition;
use Symfony\Component\Config\Definition\Builder\FloatNodeDefinition;
use Symfony\Component\Config\Definition\Builder\IntegerNodeDefinition;
use Symfony\Component\Config\Definition\Builder\ScalarNodeDefinition;
use Symfony\Component\Config\Definition\Builder\VariableNodeDefinition;

class EnvSubstitutionNormalizationTest extends TestCase
{
    private $mockEnvReader;
    private $normalization;

    protected function setUp(): void
    {
        $this->mockEnvReader = $this->createMock(EnvReader::class);
        $this->normalization = new EnvSubstitutionNormalization($this->mockEnvReader);
    }

    /**
     * @covers \OpenTelemetry\Config\SDK\Configuration\Internal\EnvSubstitutionNormalization::replaceEnvVariables
     */
    public function testReplaceEnvVariablesWithNoSubstitution(): void
    {
        $value = 'no_env_vars_here';
        
        $result = $this->callReplaceEnvVariables($value);
        
        $this->assertEquals($value, $result);
    }

    /**
     * @covers \OpenTelemetry\Config\SDK\Configuration\Internal\EnvSubstitutionNormalization::replaceEnvVariables
     */
    public function testReplaceEnvVariablesWithSimpleEnvVar(): void
    {
        $value = '${env:TEST_VAR}';
        $envValue = 'test_value';
        
        $this->mockEnvReader->expects($this->once())
            ->method('read')
            ->with('TEST_VAR')
            ->willReturn($envValue);
            
        $result = $this->callReplaceEnvVariables($value);
        
        $this->assertEquals($envValue, $result);
    }

    /**
     * @covers \OpenTelemetry\Config\SDK\Configuration\Internal\EnvSubstitutionNormalization::replaceEnvVariables
     */
    public function testReplaceEnvVariablesWithDefaultValue(): void
    {
        $value = '${env:TEST_VAR:-default_value}';
        
        $this->mockEnvReader->expects($this->once())
            ->method('read')
            ->with('TEST_VAR')
            ->willReturn(null);
            
        $result = $this->callReplaceEnvVariables($value);
        
        $this->assertEquals('default_value', $result);
    }

    /**
     * @covers \OpenTelemetry\Config\SDK\Configuration\Internal\EnvSubstitutionNormalization::replaceEnvVariables
     */
    public function testReplaceEnvVariablesWithEmptyDefaultValue(): void
    {
        $value = '${env:TEST_VAR:-}';
        
        $this->mockEnvReader->expects($this->once())
            ->method('read')
            ->with('TEST_VAR')
            ->willReturn(null);
            
        $result = $this->callReplaceEnvVariables($value);
        
        $this->assertNull($result);
    }

    /**
     * @covers \OpenTelemetry\Config\SDK\Configuration\Internal\EnvSubstitutionNormalization::replaceEnvVariables
     */
    public function testReplaceEnvVariablesWithBooleanFilter(): void
    {
        $value = '${env:BOOL_VAR}';
        $envValue = 'true';
        
        $this->mockEnvReader->expects($this->once())
            ->method('read')
            ->with('BOOL_VAR')
            ->willReturn($envValue);
            
        $result = $this->callReplaceEnvVariables($value, FILTER_VALIDATE_BOOLEAN);
        
        $this->assertTrue($result);
    }

    /**
     * @covers \OpenTelemetry\Config\SDK\Configuration\Internal\EnvSubstitutionNormalization::replaceEnvVariables
     */
    public function testReplaceEnvVariablesWithIntegerFilter(): void
    {
        $value = '${env:INT_VAR}';
        $envValue = '42';
        
        $this->mockEnvReader->expects($this->once())
            ->method('read')
            ->with('INT_VAR')
            ->willReturn($envValue);
            
        $result = $this->callReplaceEnvVariables($value, FILTER_VALIDATE_INT);
        
        $this->assertEquals(42, $result);
    }

    /**
     * @covers \OpenTelemetry\Config\SDK\Configuration\Internal\EnvSubstitutionNormalization::replaceEnvVariables
     */
    public function testReplaceEnvVariablesWithFloatFilter(): void
    {
        $value = '${env:FLOAT_VAR}';
        $envValue = '3.14';
        
        $this->mockEnvReader->expects($this->once())
            ->method('read')
            ->with('FLOAT_VAR')
            ->willReturn($envValue);
            
        $result = $this->callReplaceEnvVariables($value, FILTER_VALIDATE_FLOAT);
        
        $this->assertEquals(3.14, $result);
    }

    /**
     * @covers \OpenTelemetry\Config\SDK\Configuration\Internal\EnvSubstitutionNormalization::replaceEnvVariablesRecursive
     */
    public function testReplaceEnvVariablesRecursiveWithString(): void
    {
        $value = '${env:TEST_VAR}';
        $envValue = 'test_value';
        
        $this->mockEnvReader->expects($this->once())
            ->method('read')
            ->with('TEST_VAR')
            ->willReturn($envValue);
            
        $result = $this->callReplaceEnvVariablesRecursive($value);
        
        $this->assertEquals($envValue, $result);
    }

    /**
     * @covers \OpenTelemetry\Config\SDK\Configuration\Internal\EnvSubstitutionNormalization::replaceEnvVariablesRecursive
     */
    public function testReplaceEnvVariablesRecursiveWithNonStringNonArray(): void
    {
        $value = 42;
        
        $result = $this->callReplaceEnvVariablesRecursive($value);
        
        $this->assertEquals(42, $result);
    }

    private function callReplaceEnvVariables(string $value, int $filter = FILTER_DEFAULT): mixed
    {
        $reflection = new \ReflectionClass($this->normalization);
        $method = $reflection->getMethod('replaceEnvVariables');
        $method->setAccessible(true);
        
        return $method->invoke($this->normalization, $value, $filter);
    }

    private function callReplaceEnvVariablesRecursive(mixed $value): mixed
    {
        $reflection = new \ReflectionClass($this->normalization);
        $method = $reflection->getMethod('replaceEnvVariablesRecursive');
        $method->setAccessible(true);
        
        return $method->invoke($this->normalization, $value);
    }
}
