<?php

declare(strict_types=1);

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Trace\Sampler;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use OpenTelemetry\API\Trace\NonRecordingSpan;
use OpenTelemetry\API\Trace\SpanContext;
use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Trace\Sampler\ParentBased;
use OpenTelemetry\SDK\Trace\SamplerInterface;
use OpenTelemetry\SDK\Trace\SamplingResult;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

#[CoversClass(ParentBased::class)]
class ParentBasedTest extends MockeryTestCase
{
    private SamplerInterface $rootSampler;

    #[\Override]
    public function setUp(): void
    {
        $this->rootSampler = $this->createMock(SamplerInterface::class);
        $this->rootSampler->method('getDescription')->willReturn('Foo');
    }

    public function test_get_description(): void
    {
        $sampler = new ParentBased($this->rootSampler);
        $this->assertSame('ParentBased+Foo', $sampler->getDescription());
    }

    #[Group('trace-compliance')]
    public function test_parent_based_root_span(): void
    {
        $rootSampler = self::createMockSamplerInvokedOnce(SamplingResult::RECORD_AND_SAMPLE);

        $sampler = new ParentBased($rootSampler);
        $decision = $sampler->shouldSample(
            Context::getRoot(),
            '4bf92f3577b34da6a3ce929d0e0e4736',
            'test.opentelemetry.io',
            API\SpanKind::KIND_INTERNAL,
            Attributes::create([]),
            [],
        );

        $this->assertSame(SamplingResult::RECORD_AND_SAMPLE, $decision->getDecision());
    }

    #[DataProvider('parentContextProvider')]
    public function test_should_sample_parent_based(
        $parentContext,
        ?SamplerInterface $remoteParentSampled = null,
        ?SamplerInterface $remoteParentNotSampled = null,
        ?SamplerInterface $localParentSampled = null,
        ?SamplerInterface $localParentNotSampled = null,
        ?int $expectedDecision = null,
    ): void {
        $rootSampler = $this->createMockSamplerNeverInvoked();

        $sampler = new ParentBased($rootSampler, $remoteParentSampled, $remoteParentNotSampled, $localParentSampled, $localParentNotSampled);
        $decision = $sampler->shouldSample(
            $parentContext,
            '4bf92f3577b34da6a3ce929d0e0e4736',
            'test.opentelemetry.io',
            API\SpanKind::KIND_INTERNAL,
            Attributes::create([]),
            [],
        );
        $this->assertEquals($expectedDecision, $decision->getDecision());
    }

    public static function parentContextProvider(): array
    {
        return [
            'remote, sampled, default sampler' => [self::createParentContext(true, true), null, null, null, null, SamplingResult::RECORD_AND_SAMPLE],
            'remote, not sampled, default sampler' => [self::createParentContext(false, true), null, null, null, null, SamplingResult::DROP],
            'local, sampled, default sampler' => [self::createParentContext(true, false), null, null, null, null, SamplingResult::RECORD_AND_SAMPLE],
            'local, not sampled, default sampler' => [self::createParentContext(false, false), null, null, null, null, SamplingResult::DROP],
            'remote, sampled' => [self::createParentContext(true, true), self::createMockSamplerInvokedOnce(SamplingResult::RECORD_AND_SAMPLE), null, null, null, SamplingResult::RECORD_AND_SAMPLE],
            'remote, not sampled' => [self::createParentContext(false, true), null, self::createMockSamplerInvokedOnce(SamplingResult::DROP), null, null, SamplingResult::DROP],
            'local, sampled' => [self::createParentContext(true, false), null, null, self::createMockSamplerInvokedOnce(SamplingResult::RECORD_AND_SAMPLE), null, SamplingResult::RECORD_AND_SAMPLE],
            'local, not sampled' => [self::createParentContext(false, false), null, null, null, self::createMockSamplerInvokedOnce(SamplingResult::DROP), SamplingResult::DROP],
        ];
    }

    private static function createParentContext(bool $sampled, bool $isRemote, ?API\TraceStateInterface $traceState = null): ContextInterface
    {
        $traceFlag = $sampled ? API\TraceFlags::SAMPLED : API\TraceFlags::DEFAULT;

        if ($isRemote) {
            $spanContext = SpanContext::createFromRemoteParent(
                '4bf92f3577b34da6a3ce929d0e0e4736',
                '00f067aa0ba902b7',
                $traceFlag,
                $traceState
            );
        } else {
            $spanContext = SpanContext::create(
                '4bf92f3577b34da6a3ce929d0e0e4736',
                '00f067aa0ba902b7',
                $traceFlag,
                $traceState
            );
        }

        return Context::getRoot()->withContextValue(new NonRecordingSpan($spanContext));
    }

    private function createMockSamplerNeverInvoked(): SamplerInterface
    {
        $sampler = $this->createMock(SamplerInterface::class);
        $sampler->expects($this->never())->method('shouldSample');

        return $sampler;
    }

    private static function createMockSamplerInvokedOnce(int $resultDecision): SamplerInterface
    {
        return Mockery::mock(SamplerInterface::class, ['shouldSample' => new SamplingResult($resultDecision)]);
    }
}
