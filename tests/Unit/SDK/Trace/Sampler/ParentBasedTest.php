<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Trace\Sampler;

use OpenTelemetry\API\Trace as API;
use OpenTelemetry\API\Trace\NonRecordingSpan;
use OpenTelemetry\API\Trace\SpanContext;
use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Trace\Sampler\ParentBased;
use OpenTelemetry\SDK\Trace\SamplerInterface;
use OpenTelemetry\SDK\Trace\SamplingResult;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass OpenTelemetry\SDK\Trace\Sampler\ParentBased
 */
class ParentBasedTest extends TestCase
{
    private SamplerInterface $rootSampler;

    public function setUp(): void
    {
        $this->rootSampler = $this->createMock(SamplerInterface::class);
        $this->rootSampler->method('getDescription')->willReturn('Foo');
    }

    /**
     * @covers ::getDescription
     */
    public function test_get_description(): void
    {
        $sampler = new ParentBased($this->rootSampler);
        $this->assertSame('ParentBased+Foo', $sampler->getDescription());
    }

    /**
     * @covers ::shouldSample
     * @covers ::__construct
     * @group trace-compliance
     */
    public function test_parent_based_root_span(): void
    {
        $rootSampler = $this->createMockSamplerInvokedOnce(SamplingResult::RECORD_AND_SAMPLE);

        $sampler = new ParentBased($rootSampler);
        $sampler->shouldSample(
            Context::getRoot(),
            '4bf92f3577b34da6a3ce929d0e0e4736',
            'test.opentelemetry.io',
            API\SpanKind::KIND_INTERNAL,
            Attributes::create([]),
            [],
        );
    }

    /**
     * @covers ::shouldSample
     * @dataProvider parentContextProvider
     */
    public function test_should_sample_parent_based(
        $parentContext,
        ?SamplerInterface $remoteParentSampled = null,
        ?SamplerInterface $remoteParentNotSampled = null,
        ?SamplerInterface $localParentSampled = null,
        ?SamplerInterface $localParentNotSampled = null,
        ?int $expectedDecision = null
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

    public function parentContextProvider(): array
    {
        return [
            'remote, sampled, default sampler' => [$this->createParentContext(true, true), null, null, null, null, SamplingResult::RECORD_AND_SAMPLE],
            'remote, not sampled, default sampler' => [$this->createParentContext(false, true), null, null, null, null, SamplingResult::DROP],
            'local, sampled, default sampler' => [$this->createParentContext(true, false), null, null, null, null, SamplingResult::RECORD_AND_SAMPLE],
            'local, not sampled, default sampler' => [$this->createParentContext(false, false), null, null, null, null, SamplingResult::DROP],
            'remote, sampled' => [$this->createParentContext(true, true), $this->createMockSamplerInvokedOnce(SamplingResult::RECORD_AND_SAMPLE), null, null, null, SamplingResult::RECORD_AND_SAMPLE],
            'remote, not sampled' => [$this->createParentContext(false, true), null, $this->createMockSamplerInvokedOnce(SamplingResult::DROP), null, null, SamplingResult::DROP],
            'local, sampled' => [$this->createParentContext(true, false), null, null, $this->createMockSamplerInvokedOnce(SamplingResult::RECORD_AND_SAMPLE), null, SamplingResult::RECORD_AND_SAMPLE],
            'local, not sampled' => [$this->createParentContext(false, false), null, null, null, $this->createMockSamplerInvokedOnce(SamplingResult::DROP), SamplingResult::DROP],
        ];
    }

    private function createParentContext(bool $sampled, bool $isRemote, ?API\TraceStateInterface $traceState = null): Context
    {
        $traceFlag = $sampled ? API\SpanContextInterface::TRACE_FLAG_SAMPLED : API\SpanContextInterface::TRACE_FLAG_DEFAULT;

        if ($isRemote) {
            $spanContext = SpanContext::create(
                '4bf92f3577b34da6a3ce929d0e0e4736',
                '00f067aa0ba902b7',
                $traceFlag,
                $traceState,
                true
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

    private function createMockSamplerInvokedOnce(int $resultDecision): SamplerInterface
    {
        $sampler = $this->createMock(SamplerInterface::class);
        $sampler->expects($this->once())->method('shouldSample')
            ->willReturn(new SamplingResult($resultDecision));

        return $sampler;
    }
}
