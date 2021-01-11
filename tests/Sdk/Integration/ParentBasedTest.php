<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Sdk\Integration;

use OpenTelemetry\Sdk\Trace\Sampler;
use OpenTelemetry\Sdk\Trace\Sampler\ParentBased;
use OpenTelemetry\Sdk\Trace\SamplingResult;
use OpenTelemetry\Sdk\Trace\SpanContext;
use OpenTelemetry\Trace as API;
use PHPUnit\Framework\TestCase;

class ParentBasedTest extends TestCase
{
    /**
     * @test
     */
    public function testParentBasedRootSpan()
    {
        $rootSampler = $this->createMockSamplerInvokedOnce(SamplingResult::RECORD_AND_SAMPLED);

        $sampler = new ParentBased($rootSampler);
        $sampler->shouldSample(
            null,
            '4bf92f3577b34da6a3ce929d0e0e4736',
            '00f067aa0ba902b7',
            'test.opentelemetry.io',
            API\SpanKind::KIND_INTERNAL
        );
    }

    /**
     * @dataProvider parentContextData
     */
    public function testParentBased(
        $parentContext,
        ?Sampler $remoteParentSampled = null,
        ?Sampler $remoteParentNotSampled = null,
        ?Sampler $localParentSampled = null,
        ?Sampler $localParentNotSampled = null,
        $expectedDdecision
    ) {
        $rootSampler = $this->createMockSamplerNeverInvoked();

        $sampler = new ParentBased($rootSampler, $remoteParentSampled, $remoteParentNotSampled, $localParentSampled, $localParentNotSampled);
        $decision = $sampler->shouldSample(
            $parentContext,
            '4bf92f3577b34da6a3ce929d0e0e4736',
            '00f067aa0ba902b7',
            'test.opentelemetry.io',
            API\SpanKind::KIND_INTERNAL
        );
        $this->assertEquals($expectedDdecision, $decision->getDecision());
    }

    public function parentContextData(): array
    {
        return [
            // remote, sampled, default sampler
            [$this->createParentContext(true, true), null, null, null, null, SamplingResult::RECORD_AND_SAMPLED],
            // remote, not sampled, default sampler
            [$this->createParentContext(false, true), null, null, null, null, SamplingResult::NOT_RECORD],
            // local, sampled, default sampler
            [$this->createParentContext(true, false), null, null, null, null, SamplingResult::RECORD_AND_SAMPLED],
            // local, not sampled, default sampler
            [$this->createParentContext(false, false), null, null, null, null, SamplingResult::NOT_RECORD],
            // remote, sampled
            [$this->createParentContext(true, true), $this->createMockSamplerInvokedOnce(SamplingResult::RECORD_AND_SAMPLED), null, null, null, SamplingResult::RECORD_AND_SAMPLED],
            // remote, not sampled
            [$this->createParentContext(false, true), null, $this->createMockSamplerInvokedOnce(SamplingResult::NOT_RECORD), null, null, SamplingResult::NOT_RECORD],
            // local, sampled
            [$this->createParentContext(true, false), null, null, $this->createMockSamplerInvokedOnce(SamplingResult::RECORD_AND_SAMPLED), null, SamplingResult::RECORD_AND_SAMPLED],
            // local, not sampled
            [$this->createParentContext(false, false), null, null, null, $this->createMockSamplerInvokedOnce(SamplingResult::NOT_RECORD), SamplingResult::NOT_RECORD],
        ];
    }

    /**
     * @test
     */
    public function testParentBasedDescription()
    {
        $rootSampler = self::createMock(Sampler::class);
        $sampler = new ParentBased($rootSampler);
        $this->assertEquals('ParentBased', $sampler->getDescription());
    }

    private function createParentContext(bool $sampled, bool $isRemote): SpanContext
    {
        return SpanContext::restore(
            '4bf92f3577b34da6a3ce929d0e0e4736',
            '00f067aa0ba902b7',
            $sampled,
            $isRemote
        );
    }

    private function createMockSamplerNeverInvoked(): Sampler
    {
        $sampler = self::createMock(Sampler::class);
        $sampler->expects($this->never())->method('shouldSample');

        return $sampler;
    }

    private function createMockSamplerInvokedOnce(int $resultDecision): Sampler
    {
        $sampler = self::createMock(Sampler::class);
        $sampler->expects($this->once())->method('shouldSample')
            ->willReturn(new SamplingResult($resultDecision));

        return $sampler;
    }
}
