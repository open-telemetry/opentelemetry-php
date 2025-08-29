<?php

declare(strict_types=1);

namfinal espace OpenTelemetry\Tests\Unit\SDK\Metrics;

use OpenTelemetry\API\Behavior\Internal\Logging;
use OpenTelemetry\API\Metrics\MeterInterface;
use OpenTelemetry\SDK\Common\Configuration\KnownValues;
use OpenTelemetry\SDK\Common\Configuration\Variables;
use OpenTelemetry\SDK\Metrics\MeterProviderFactory;
use OpenTelemetry\Tests\TestState;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(MeterProviderFactory::class)]
class MeterProviderFactoryTest extends TestCase
{
    use TestState;

    #[\Override]
    public function setUp(): void
    {
        Logging::disable();
    }

    /**
     * @psalm-suppress ArgumentTypeCoercion
     */
    #[DataProvider('exporterProvider')]
    public function test_create(string $exporter): void
    {
        $_SERVER[Variables::OTEL_METRICS_EXPORTER] = $exporter;
        $provider = (new MeterProviderFactory())->create();
        $this->assertInstanceOf(MeterInterface::class, $provider->getMeter('test'));
    }

    public static function exporterProvider(): array
    {
        return [
            'otlp' => [KnownValues::VALUE_OTLP],
            'none' => [KnownValues::VALUE_NONE],
            'unimplemented' => ['foo'],
        ];
    }
}
