<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\SDK\Integration;

use OpenTelemetry\API\Trace as API;
use OpenTelemetry\API\Trace\NonRecordingSpan;
use OpenTelemetry\API\Trace\SpanContext;
use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Trace\Sampler\ParentBased;
use OpenTelemetry\SDK\Trace\SamplerInterface;
use OpenTelemetry\SDK\Trace\SamplingResult;
use PHPUnit\Framework\TestCase;

class ParentBasedTest extends TestCase
{
    public function testParentBasedRootSpan(): void
    {
        $rootSampler = $this->createMockSamplerInvokedOnce(SamplingResult::RECORD_AND_SAMPLE);

        $sampler = new ParentBased($rootSampler);
        $sampler->shouldSample(
            new Context(),
            '4bf92f3577b34da6a3ce929d0e0e4736',
            'test.opentelemetry.io',
            API\SpanKind::KIND_INTERNAL
        );
    }

    /**
     * @dataProvider parentContextData
     */
    public function testParentBased(
        $parentContext,
        ?SamplerInterface $remoteParentSampled = null,
        ?SamplerInterface $remoteParentNotSampled = null,
        ?SamplerInterface $localParentSampled = null,
        ?SamplerInterface $localParentNotSampled = null,
        $expectedDdecision
    ): void {
        $rootSampler = $this->createMockSamplerNeverInvoked();

        $sampler = new ParentBased($rootSampler, $remoteParentSampled, $remoteParentNotSampled, $localParentSampled, $localParentNotSampled);
        $decision = $sampler->shouldSample(
            $parentContext,
            '4bf92f3577b34da6a3ce929d0e0e4736',
            'test.opentelemetry.io',
            API\SpanKind::KIND_INTERNAL
        );
        $this->assertEquals($expectedDdecision, $decision->getDecision());
    }

    public function parentContextData(): array
    {
        return [
            // remote, sampled, default sampler
            [$this->createParentContext(true, true), null, null, null, null, SamplingResult::RECORD_AND_SAMPLE],
            // remote, not sampled, default sampler
            [$this->createParentContext(false, true), null, null, null, null, SamplingResult::DROP],
            // local, sampled, default sampler
            [$this->createParentContext(true, false), null, null, null, null, SamplingResult::RECORD_AND_SAMPLE],
            // local, not sampled, default sampler
            [$this->createParentContext(false, false), null, null, null, null, SamplingResult::DROP],
            // remote, sampled
            [$this->createParentContext(true, true), $this->createMockSamplerInvokedOnce(SamplingResult::RECORD_AND_SAMPLE), null, null, null, SamplingResult::RECORD_AND_SAMPLE],
            // remote, not sampled
            [$this->createParentContext(false, true), null, $this->createMockSamplerInvokedOnce(SamplingResult::DROP), null, null, SamplingResult::DROP],
            // local, sampled
            [$this->createParentContext(true, false), null, null, $this->createMockSamplerInvokedOnce(SamplingResult::RECORD_AND_SAMPLE), null, SamplingResult::RECORD_AND_SAMPLE],
            // local, not sampled
            [$this->createParentContext(false, false), null, null, null, $this->createMockSamplerInvokedOnce(SamplingResult::DROP), SamplingResult::DROP],
        ];
    }

    public function testParentBasedDescription(): void
    {
        $rootSampler = $this->createMock(SamplerInterface::class);
        $rootSampler->expects($this->once())->method('getDescription')->willReturn('Foo');
        $sampler = new ParentBased($rootSampler);
        $this->assertEquals('ParentBased+Foo', $sampler->getDescription());
    }

    private function createParentContext(bool $sampled, bool $isRemote, ?API\TraceStateInterface $traceState = null): Context
    {
        $traceFlag = $sampled ? API\SpanContextInterface::TRACE_FLAG_SAMPLED : API\SpanContextInterface::TRACE_FLAG_DEFAULT;

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

        return (new Context())->withContextValue(new NonRecordingSpan($spanContext));
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
