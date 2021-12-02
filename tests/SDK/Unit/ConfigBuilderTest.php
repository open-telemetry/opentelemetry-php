<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\SDK;

use AssertWell\PHPUnitGlobalState\EnvironmentVariables;
use Nette\Schema\ValidationException;
use OpenTelemetry\SDK\ConfigBuilder;
use PHPUnit\Framework\TestCase;

class ConfigBuilderTest extends TestCase
{
    use EnvironmentVariables;

    private $builder;

    public function setUp(): void
    {
        $this->builder = new ConfigBuilder();
    }

    public function tearDown(): void
    {
        $this->restoreEnvironmentVariables();
    }

    /**
     * @test
     * @testdox It splits resource attributes into an array from environment
     */
    public function resourceAttributes(): void
    {
        $this->setEnvironmentVariable('OTEL_RESOURCE_ATTRIBUTES', 'foo=foo,bar=bar,baz=baz');
        $config = $this->builder->build();
        $this->assertSame(['foo' => 'foo', 'bar' => 'bar', 'baz' => 'baz'], $config->resource->attributes);
    }

    /**
     * @test
     * @testdox It combines resource attributes from input and environment
     */
    public function combineResourceAttributes(): void
    {
        $this->setEnvironmentVariable('OTEL_RESOURCE_ATTRIBUTES', 'foo=foo');
        $config = $this->builder->build(['resource' => ['attributes' => ['bar' => 'bar']]]);
        $this->assertSame(['foo' => 'foo', 'bar' => 'bar'], $config->resource->attributes);
    }

    /**
     * @test
     * @testdox User values have higher priority than env vars
     */
    public function userValuesBeatEnvVars(): void
    {
        $this->setEnvironmentVariable('OTEL_LOG_LEVEL', 'error');
        $config = $this->builder->build(['log' => ['level' => 'warning']]);
        $this->assertSame('warning', $config->log->level);
    }

    /**
     * @test
     * @testdox It errors on input data that does not match schema
     */
    public function failsSchemaValidation(): void
    {
        $this->expectException(ValidationException::class);
        $this->builder->build(['foo' => 'bar']);
    }

    /**
     * @test
     * @testdox It treats empty env vars as missing/null
     */
    public function ignoreEmptyEnvVars(): void
    {
        $this->setEnvironmentVariable('OTEL_EXPORTER_OTLP_INSECURE', '');
        $config = $this->builder->build();
        $this->assertNull($config->trace->exporters->otlp->insecure);
    }

    /**
     * @test
     * @testdox It casts integer values
     */
    public function castsIntegers(): void
    {
        $this->setEnvironmentVariable('OTEL_LINK_ATTRIBUTE_COUNT_LIMIT', '22');
        $config = $this->builder->build();
        $this->assertSame(22, $config->span->limits->attribute_per_link);
    }
}
