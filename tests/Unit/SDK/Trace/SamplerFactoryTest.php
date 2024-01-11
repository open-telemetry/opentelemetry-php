<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Trace;

use AssertWell\PHPUnitGlobalState\EnvironmentVariables;
use Exception;
use OpenTelemetry\SDK\Trace\SamplerFactory;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\SDK\Trace\SamplerFactory
 */
class SamplerFactoryTest extends TestCase
{
    use EnvironmentVariables;

    protected function tearDown(): void
    {
        $this->restoreEnvironmentVariables();
    }

    /**
     * @dataProvider samplerProvider
     */
    public function test_create_sampler_from_environment(string $samplerName, string $expected, string $arg = null): void
    {
        $this->setEnvironmentVariable('OTEL_TRACES_SAMPLER', $samplerName);
        $this->setEnvironmentVariable('OTEL_TRACES_SAMPLER_ARG', $arg);
        $factory = new SamplerFactory();
        $sampler = $factory->create();
        $this->assertStringContainsString($expected, $sampler->getDescription());
    }

    public static function samplerProvider(): array
    {
        return [
            'default sampler' => ['', 'ParentBased+AlwaysOn'],
            'always on' => ['always_on', 'AlwaysOn'],
            'always off' => ['always_off', 'AlwaysOff'],
            'trace id ratio' => ['traceidratio', 'TraceIdRatio', '0.95'],
            'parent based always on' => ['parentbased_always_on', 'ParentBased+AlwaysOn'],
            'parent based always off' => ['parentbased_always_off', 'ParentBased+AlwaysOff'],
            'parent based trade id ratio' => ['parentbased_traceidratio', 'ParentBased+TraceIdRatio', '0.95'],
        ];
    }

    /**
     * @dataProvider invalidSamplerProvider
     */
    public function test_throws_exception_for_invalid_or_unsupported(?string $sampler, string $arg = null): void
    {
        $this->setEnvironmentVariable('OTEL_TRACES_SAMPLER', $sampler);
        $this->setEnvironmentVariable('OTEL_TRACES_SAMPLER_ARG', $arg);
        $factory = new SamplerFactory();
        $this->expectException(Exception::class);
        $factory->create();
    }

    public static function invalidSamplerProvider(): array
    {
        return [
            'ratio without arg' => ['traceidratio'],
            'parent ratio without arg' => ['parentbased_traceidratio'],
            'ratio with invalid arg' => ['traceidratio', 'foo'],
            'parent ratio with invalid arg' => ['parentbased_traceidratio', 'foo'],
            'unknown sampler' => ['foo'],
        ];
    }

    /**
     * @dataProvider compositeProvider
     */
    public function test_create_composite_sampler(string $sampler, string $arg, string $expected): void
    {
        $this->setEnvironmentVariable('OTEL_TRACES_SAMPLER', $sampler);
        $arg && $this->setEnvironmentVariable('OTEL_TRACES_SAMPLER_ARG', $arg);
        $factory = new SamplerFactory();
        $sampler = $factory->create();
        $this->assertSame($expected, $sampler->getDescription());
    }

    public static function compositeProvider(): array
    {
        return [
            [
                'parentbased,attribute,always_on',
                'attribute.mode=allow,attribute.name=http.path,attribute.pattern=foo',
                'ParentBased+AttributeSampler{mode=allow,attribute=http.path,pattern=/foo/}+AlwaysOnSampler',
            ],
            [
                'parentbased,traceidratio',
                'traceidratio.probability=0.1',
                'ParentBased+TraceIdRatioBasedSampler{0.100000}',
            ],
            [
                'parentbased,traceidratio',
                '0.2',
                'ParentBased+TraceIdRatioBasedSampler{0.200000}',
            ],
            [
                'parentbased,always_on',
                '',
                'ParentBased+AlwaysOnSampler',
            ],
            [
                'parentbased,always_off',
                '',
                'ParentBased+AlwaysOffSampler',
            ],
        ];
    }

    public function test_create_composite_sampler_invalid(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->setEnvironmentVariable('OTEL_TRACES_SAMPLER', 'parentbased,foo');

        (new SamplerFactory())->create();
    }

    public function test_create_composite_sampler_invalid_args(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->setEnvironmentVariable('OTEL_TRACES_SAMPLER', 'parentbased,attribute');
        $this->setEnvironmentVariable('OTEL_TRACES_SAMPLER_ARG', 'attribute.foo-bar'); //no equals sign

        (new SamplerFactory())->create();
    }

}
