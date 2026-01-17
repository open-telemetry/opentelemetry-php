<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Trace\Sampler;

use Mockery;
use OpenTelemetry\API\Trace as API;
use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Trace\Sampler\AlwaysRecordSampler;
use OpenTelemetry\SDK\Trace\SamplerInterface;
use OpenTelemetry\SDK\Trace\SamplingResult;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(AlwaysRecordSampler::class)]
class AlwaysRecordSamplerTest extends TestCase
{
    private SamplerInterface $rootSampler;

    #[\Override]
    public function setUp(): void
    {
        $this->rootSampler = $this->createMock(SamplerInterface::class);
        $this->rootSampler->method('getDescription')->willReturn('Foo');
    }

    #[DataProvider('shouldSampleProvider')]
    public function test_should_sample(SamplerInterface $rootSampler, int $result): void
    {
        $sampler = new AlwaysRecordSampler($rootSampler);
        $decision = $sampler->shouldSample(
            Context::getRoot(),
            '4bf92f3577b34da6a3ce929d0e0e4736',
            'test.opentelemetry.io',
            API\SpanKind::KIND_INTERNAL,
            Attributes::create([]),
            [],
        );
        $this->assertEquals($result, $decision->getDecision());
    }

    public static function shouldSampleProvider(): iterable
    {
        yield [self::createMockSamplerInvokedOnce(SamplingResult::DROP), SamplingResult::RECORD_ONLY];
        yield [self::createMockSamplerInvokedOnce(SamplingResult::RECORD_ONLY), SamplingResult::RECORD_ONLY];
        yield [self::createMockSamplerInvokedOnce(SamplingResult::RECORD_AND_SAMPLE), SamplingResult::RECORD_AND_SAMPLE];
    }

    public function test_get_description(): void
    {
        $sampler = new AlwaysRecordSampler($this->rootSampler);
        $this->assertSame('AlwaysRecordSampler+Foo', $sampler->getDescription());
    }

    private static function createMockSamplerInvokedOnce(int $resultDecision): SamplerInterface
    {
        return Mockery::mock(SamplerInterface::class, ['shouldSample' => new SamplingResult($resultDecision)]);
    }
}
