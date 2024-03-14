<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Integration\SDK;

use OpenTelemetry\API\Trace as API;
use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Trace\Sampler\ParentBased;
use OpenTelemetry\SDK\Trace\SamplerInterface;
use OpenTelemetry\SDK\Trace\SamplingResult;
use PHPUnit\Framework\TestCase;

/**
 * @coversNothing
 */
class ParentBasedTest extends TestCase
{
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

    private function createMockSamplerInvokedOnce(int $resultDecision): SamplerInterface
    {
        $sampler = $this->createMock(SamplerInterface::class);
        $sampler->expects($this->once())->method('shouldSample')
            ->willReturn(new SamplingResult($resultDecision));

        return $sampler;
    }
}
