<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Integration\Config;

use OpenTelemetry\API\Configuration\Context;
use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\Config\SDK\ComponentProvider\OpenTelemetrySdk;
use OpenTelemetry\Config\SDK\ComponentProvider\Trace\ComposableSamplerAlwaysOff;
use OpenTelemetry\Config\SDK\ComponentProvider\Trace\ComposableSamplerAlwaysOn;
use OpenTelemetry\Config\SDK\ComponentProvider\Trace\ComposableSamplerParentThreshold;
use OpenTelemetry\Config\SDK\ComponentProvider\Trace\ComposableSamplerProbability;
use OpenTelemetry\Config\SDK\ComponentProvider\Trace\ComposableSamplerRuleBased;
use OpenTelemetry\Config\SDK\ComponentProvider\Trace\SamplerComposite;
use OpenTelemetry\Config\SDK\ComponentProvider\Trace\SamplerProbability;
use OpenTelemetry\Config\SDK\Configuration\ConfigurationFactory;
use OpenTelemetry\Config\SDK\Configuration\Environment\EnvSourceReader;
use OpenTelemetry\Context\Context as OtelContext;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\ConsistentSampler;
use OpenTelemetry\SDK\Trace\SamplerInterface;
use OpenTelemetry\SDK\Trace\SamplingResult;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Yaml\Yaml;

#[CoversNothing]
final class ConsistentSamplerConfigurationTest extends TestCase
{
    private const TRACE_ID = '0af7651916cd43dd8448eb211c80319c';

    public function test_probability_development(): void
    {
        $sampler = $this->sampler(<<<'YAML'
            probability/development:
              ratio: 0.0
            YAML);
        $this->assertSame(SamplingResult::DROP, $this->decide($sampler)->getDecision());

        $sampler = $this->sampler(<<<'YAML'
            probability/development:
              ratio: 1.0
            YAML);
        $this->assertSame(SamplingResult::RECORD_AND_SAMPLE, $this->decide($sampler)->getDecision());
    }

    public function test_probability_ratio_defaults_to_one(): void
    {
        $sampler = $this->sampler(<<<'YAML'
            probability/development:
            YAML);
        $this->assertSame(SamplingResult::RECORD_AND_SAMPLE, $this->decide($sampler)->getDecision());
    }

    public function test_composite_always_on_and_off(): void
    {
        $on = $this->sampler("composite/development:\n  always_on:\n");
        $off = $this->sampler("composite/development:\n  always_off:\n");

        $this->assertSame(SamplingResult::RECORD_AND_SAMPLE, $this->decide($on)->getDecision());
        $this->assertSame(SamplingResult::DROP, $this->decide($off)->getDecision());
    }

    public function test_composite_probability_and_parent_threshold(): void
    {
        $sampler = $this->sampler(<<<'YAML'
            composite/development:
              parent_threshold:
                root:
                  probability:
                    ratio: 0.0
            YAML);

        $this->assertInstanceOf(SamplerInterface::class, $sampler);
        $this->assertSame(SamplingResult::DROP, $this->decide($sampler)->getDecision());
    }

    public function test_composite_requires_exactly_one_sampler(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->sampler("composite/development:\n  always_on:\n  always_off:\n");
    }

    public function test_composite_rule_based(): void
    {
        $sampler = $this->sampler(<<<'YAML'
            composite/development:
              rule_based:
                rules:
                - span_kinds: [server]
                  attribute_patterns:
                    key: url.path
                    included: ['/api/*']
                    excluded: ['/api/health*']
                  sampler:
                    always_on:
                - span_kinds: [server]
                  attribute_values:
                    key: http.route
                    values: ['/orders', '/payments']
                  sampler:
                    always_on:
                - parent: [none]
                  sampler:
                    probability:
                      ratio: 0.0
            YAML);

        $server = SpanKind::KIND_SERVER;
        $this->assertSame(SamplingResult::RECORD_AND_SAMPLE, $this->decide($sampler, $server, ['url.path' => '/api/users'])->getDecision(), 'included pattern');
        $this->assertSame(SamplingResult::DROP, $this->decide($sampler, $server, ['url.path' => '/api/health/live'])->getDecision(), 'excluded pattern falls through to root rule with ratio 0');
        $this->assertSame(SamplingResult::RECORD_AND_SAMPLE, $this->decide($sampler, $server, ['http.route' => '/orders'])->getDecision(), 'attribute value');
        $this->assertSame(SamplingResult::DROP, $this->decide($sampler, SpanKind::KIND_CLIENT, ['url.path' => '/api/users'])->getDecision(), 'span kind mismatch');
    }

    public function test_composite_rule_based_without_matching_rule_drops(): void
    {
        $sampler = $this->sampler(<<<'YAML'
            composite/development:
              rule_based:
                rules:
                - span_kinds: [consumer]
                  sampler:
                    always_on:
            YAML);

        $this->assertSame(SamplingResult::DROP, $this->decide($sampler, SpanKind::KIND_SERVER)->getDecision());
        $this->assertSame(SamplingResult::RECORD_AND_SAMPLE, $this->decide($sampler, SpanKind::KIND_CONSUMER)->getDecision());
    }

    public function test_rule_based_matches_boolean_attributes(): void
    {
        $sampler = $this->sampler(<<<'YAML'
            composite/development:
              rule_based:
                rules:
                - attribute_values:
                    key: cache.hit
                    values: [true]
                  sampler:
                    always_on:
                - attribute_patterns:
                    key: cache.stale
                    included: [false]
                  sampler:
                    always_on:
            YAML);

        $this->assertSame(SamplingResult::RECORD_AND_SAMPLE, $this->decide($sampler, attributes: ['cache.hit' => true])->getDecision());
        $this->assertSame(SamplingResult::DROP, $this->decide($sampler, attributes: ['cache.hit' => false])->getDecision());
        $this->assertSame(SamplingResult::RECORD_AND_SAMPLE, $this->decide($sampler, attributes: ['cache.stale' => false])->getDecision());
        $this->assertSame(SamplingResult::DROP, $this->decide($sampler, attributes: ['cache.stale' => true])->getDecision());
    }

    public function test_invalid_ratio_is_rejected(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->sampler("probability/development:\n  ratio: 1.5\n");
    }

    public function test_invalid_span_kind_is_rejected(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->sampler(<<<'YAML'
            composite/development:
              rule_based:
                rules:
                - span_kinds: [bogus]
                  sampler:
                    always_on:
            YAML);
    }

    private function sampler(string $samplerYaml): SamplerInterface
    {
        $factory = new ConfigurationFactory(
            [
                new SamplerProbability(),
                new SamplerComposite(),
                new ComposableSamplerAlwaysOn(),
                new ComposableSamplerAlwaysOff(),
                new ComposableSamplerProbability(),
                new ComposableSamplerParentThreshold(),
                new ComposableSamplerRuleBased(),
            ],
            new OpenTelemetrySdk(),
            new EnvSourceReader([]),
        );

        $indented = preg_replace('/^/m', '    ', $samplerYaml);
        $sdk = $factory->process([Yaml::parse("file_format: \"1.0-rc.2\"\ntracer_provider:\n  processors: []\n  sampler:\n" . $indented)])
            ->create(new Context())
            ->build();

        $tracer = $sdk->getTracerProvider()->getTracer('test');
        $sharedStateProperty = (new \ReflectionClass($tracer))->getProperty('tracerSharedState');
        $sharedState = $sharedStateProperty->getValue($tracer);

        return $sharedState->getSampler();
    }

    private function decide(SamplerInterface $sampler, int $kind = SpanKind::KIND_SERVER, array $attributes = []): SamplingResult
    {
        return $sampler->shouldSample(
            OtelContext::getRoot(),
            self::TRACE_ID,
            'span',
            $kind,
            Attributes::create($attributes),
            [],
        );
    }

    public function test_composable_samplers_are_consistent_samplers(): void
    {
        $this->assertInstanceOf(ConsistentSampler::class, $this->sampler("probability/development:\n  ratio: 0.5\n"));
    }
}
