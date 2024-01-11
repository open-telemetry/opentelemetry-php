<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK;

use OpenTelemetry\API\LoggerHolder;
use OpenTelemetry\API\Trace as API;
use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Trace\Sampler\AttributeBasedSampler;
use OpenTelemetry\SDK\Trace\SamplerInterface;
use OpenTelemetry\SDK\Trace\SamplingResult;
use OpenTelemetry\SemConv\TraceAttributes;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Psr\Log\NullLogger;

/**
 * @covers \OpenTelemetry\SDK\Trace\Sampler\AttributeBasedSampler
 */
class AttributeBasedSamplerTest extends TestCase
{
    public function setUp(): void
    {
        LoggerHolder::set(new NullLogger());
    }

    public function tearDown(): void
    {
        LoggerHolder::unset();
    }

    /**
     * @dataProvider shouldSampleProvider
     */
    public function test_should_sample(string $mode, string $attribute, string $value, string $pattern, int $expected, bool $delegated): void
    {
        $mockSampler = $this->createMock(SamplerInterface::class);
        if ($delegated) {
            $mockSampler->expects($this->once())->method('shouldSample')->willReturn(new SamplingResult(SamplingResult::RECORD_AND_SAMPLE));
        } else {
            $mockSampler->expects($this->never())->method('shouldSample');
        }
        $parentContext = Context::getRoot();
        $sampler = new AttributeBasedSampler($mockSampler, $mode, $attribute, $pattern);
        $decision = $sampler->shouldSample(
            $parentContext,
            '4bf92f3577b34da6a3ce929d0e0e4736',
            'test-span',
            API\SpanKind::KIND_SERVER,
            Attributes::create([
                $attribute => $value,
            ]),
            [],
        );

        $this->assertEquals($expected, $decision->getDecision());
    }

    public static function shouldSampleProvider(): array
    {
        return [
            [
                AttributeBasedSampler::DENY,
                TraceAttributes::URL_PATH,
                '/health',
                '/\/health/',
                SamplingResult::DROP,
                false,
            ],
            [
                AttributeBasedSampler::DENY,
                TraceAttributes::URL_PATH,
                '/health',
                '/healthy/',
                SamplingResult::RECORD_AND_SAMPLE,
                true,
            ],
            [
                AttributeBasedSampler::ALLOW,
                TraceAttributes::HTTP_REQUEST_METHOD,
                'POST',
                '/(POST)|(PUT)/',
                SamplingResult::RECORD_AND_SAMPLE,
                false,
            ],
            [
                AttributeBasedSampler::ALLOW,
                TraceAttributes::HTTP_REQUEST_METHOD,
                'GET',
                '/(POST)|(PUT)/',
                SamplingResult::RECORD_AND_SAMPLE,
                true,
            ],
        ];
    }

    public function test_deny_with_missing_attribute_will_defer(): void
    {
        $delegate = $this->createMock(SamplerInterface::class);
        $delegate->expects($this->once())->method('shouldSample')->willReturn(new SamplingResult(SamplingResult::RECORD_AND_SAMPLE));
        $sampler = new AttributeBasedSampler(
            $delegate,
            AttributeBasedSampler::DENY,
            'http.path',
            '/\/health/',
        );
        $decision = $sampler->shouldSample(
            Context::getRoot(),
            '4bf92f3577b34da6a3ce929d0e0e4736',
            'test-span',
            API\SpanKind::KIND_SERVER,
            Attributes::create([]),
            [],
        );
    }

    public function test_allow_with_missing_attribute_will_not_sample(): void
    {
        $delegate = $this->createMock(SamplerInterface::class);
        $delegate->expects($this->never())->method('shouldSample');
        $sampler = new AttributeBasedSampler(
            $delegate,
            AttributeBasedSampler::ALLOW,
            'http.path',
            '/\/checkout/',
        );
        $decision = $sampler->shouldSample(
            Context::getRoot(),
            '4bf92f3577b34da6a3ce929d0e0e4736',
            'test-span',
            API\SpanKind::KIND_SERVER,
            Attributes::create([]),
            [],
        );
        $this->assertSame(SamplingResult::DROP, $decision->getDecision());
    }

    /**
     * @dataProvider descriptionProvider
     */
    public function test_get_description(string $mode, string $expected): void
    {
        $child = $this->createMock(SamplerInterface::class);
        $child->method('getDescription')->willReturn('FooSampler');
        $sampler = new AttributeBasedSampler($child, $mode, 'foo', 'bar');
        $this->assertEquals($expected, $sampler->getDescription());
    }

    public static function descriptionProvider(): array
    {
        return [
            [
                AttributeBasedSampler::ALLOW,
                'AttributeSampler{mode=allow,attribute=foo,pattern=bar}+FooSampler',
            ],
            [
                AttributeBasedSampler::DENY,
                'AttributeSampler{mode=deny,attribute=foo,pattern=bar}+FooSampler',
            ],
        ];
    }

    public function test_invalid_mode(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new AttributeBasedSampler($this->createMock(SamplerInterface::class), 'invalid-mode', 'http.path', 'foo');
    }

    public function test_logs_warning_on_regex_error(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        LoggerHolder::set($logger);
        $logger->expects($this->once())->method('log');//->with($this->equalTo(LogLevel::WARNING), $this->anything(), $this->anything());
        $delegate = $this->createMock(SamplerInterface::class);
        $delegate->expects($this->once())->method('shouldSample')->willReturn(new SamplingResult(SamplingResult::RECORD_AND_SAMPLE));
        $sampler = new AttributeBasedSampler($delegate, 'allow', 'url.path', '/invalid-regex');

        $sampler->shouldSample(
            Context::getRoot(),
            '4bf92f3577b34da6a3ce929d0e0e4736',
            'test-span',
            API\SpanKind::KIND_SERVER,
            Attributes::create([
                TraceAttributes::URL_PATH => '/health',
            ]),
            [],
        );
    }
}
