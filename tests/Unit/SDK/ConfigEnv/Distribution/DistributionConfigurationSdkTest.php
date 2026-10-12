<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\ConfigEnv\Distribution;

use OpenTelemetry\API\Configuration\Context;
use OpenTelemetry\SDK\Common\Configuration\EnvComponentLoaderRegistry;
use OpenTelemetry\SDK\Common\Configuration\EnvResolver;
use OpenTelemetry\SDK\Common\Configuration\Variables;
use OpenTelemetry\SDK\Common\Distribution\SdkDistribution;
use OpenTelemetry\SDK\ConfigEnv\Distribution\DistributionConfigurationSdk;
use OpenTelemetry\SDK\Trace\SpanSuppression\NoopSuppressionStrategy\NoopSuppressionStrategy;
use OpenTelemetry\Tests\TestState;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * The environment-based counterpart of
 * {@see \OpenTelemetry\Config\SDK\ComponentProvider\Distribution\DistributionConfigurationSdk}: both
 * must land on the same {@see SdkDistribution} fields, so that one setting gates self-observability
 * whichever way the SDK was configured.
 */
#[CoversClass(DistributionConfigurationSdk::class)]
final class DistributionConfigurationSdkTest extends TestCase
{
    use TestState;

    #[DataProvider('internalMetricsProvider')]
    public function test_internal_metrics_enabled(?string $value, bool $expected): void
    {
        $this->setEnvironmentVariable(Variables::OTEL_PHP_INTERNAL_METRICS_ENABLED, $value);

        $distribution = (new DistributionConfigurationSdk())->load(
            new EnvResolver(),
            new EnvComponentLoaderRegistry(),
            new Context(),
        );

        $this->assertInstanceOf(SdkDistribution::class, $distribution);
        $this->assertSame($expected, $distribution->internalMetricsEnabled);
    }

    public static function internalMetricsProvider(): iterable
    {
        yield 'unset' => [null, false];
        yield 'true' => ['true', true];
        yield 'false' => ['false', false];
    }

    public function test_default_span_suppression_strategy(): void
    {
        $this->setEnvironmentVariable(Variables::OTEL_EXPERIMENTAL_SPAN_SUPPRESSION_STRATEGY, null);

        $distribution = (new DistributionConfigurationSdk())->load(
            new EnvResolver(),
            new EnvComponentLoaderRegistry(),
            new Context(),
        );

        $this->assertInstanceOf(SdkDistribution::class, $distribution);
        $this->assertInstanceOf(NoopSuppressionStrategy::class, $distribution->spanSuppressionStrategy);
    }
}
