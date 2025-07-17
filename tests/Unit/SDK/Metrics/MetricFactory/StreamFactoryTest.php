<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Metrics\MetricFactory;

use function func_get_args;
use OpenTelemetry\API\Common\Time\TestClock;
use OpenTelemetry\API\Metrics\ObserverInterface;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScope;
use OpenTelemetry\SDK\Metrics\Aggregation\SumAggregation;
use OpenTelemetry\SDK\Metrics\Data\Metric;
use OpenTelemetry\SDK\Metrics\Data\NumberDataPoint;
use OpenTelemetry\SDK\Metrics\Data\Sum;
use OpenTelemetry\SDK\Metrics\Data\Temporality;
use OpenTelemetry\SDK\Metrics\Exemplar\ExemplarFilter\NoneExemplarFilter;
use OpenTelemetry\SDK\Metrics\Instrument;
use OpenTelemetry\SDK\Metrics\InstrumentType;
use OpenTelemetry\SDK\Metrics\MetricFactory\StreamFactory;
use OpenTelemetry\SDK\Metrics\MetricFactory\StreamMetricSource;
use OpenTelemetry\SDK\Metrics\MetricFactory\StreamMetricSourceProvider;
use OpenTelemetry\SDK\Metrics\MetricMetadataInterface;
use OpenTelemetry\SDK\Metrics\MetricRegistration\RegistryRegistration;
use OpenTelemetry\SDK\Metrics\MetricRegistry\MetricRegistry;
use OpenTelemetry\SDK\Metrics\MetricSourceProviderInterface;
use OpenTelemetry\SDK\Metrics\MetricSourceRegistryInterface;
use OpenTelemetry\SDK\Metrics\StalenessHandler\NoopStalenessHandler;
use OpenTelemetry\SDK\Metrics\StalenessHandlerInterface;
use OpenTelemetry\SDK\Metrics\ViewProjection;
use OpenTelemetry\SDK\Resource\ResourceInfoFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(StreamFactory::class)]
#[CoversClass(StreamMetricSource::class)]
#[CoversClass(StreamMetricSourceProvider::class)]
final class StreamFactoryTest extends TestCase
{
    public function test_create_asynchronous_observer(): void
    {
        $resource = ResourceInfoFactory::emptyResource();
        $instrumentationScope = new InstrumentationScope('test', null, null, Attributes::create([]));

        $clock = new TestClock();
        $registry = new MetricRegistry(null, Attributes::factory(), $clock);
        $instrument = new Instrument(InstrumentType::ASYNCHRONOUS_UP_DOWN_COUNTER, 'name', '{unit}', 'description');
        $sourceRegistry = new CollectingSourceRegistry();
        $streamIds = (new StreamFactory())->createAsynchronousObserver(
            $registry,
            $resource,
            $instrumentationScope,
            $instrument,
            1,
            [
                [new ViewProjection(
                    'view-name',
                    'view-unit',
                    'view-description',
                    null,
                    new SumAggregation(),
                ), new RegistryRegistration($sourceRegistry, new NoopStalenessHandler())],
            ],
        );

        $this->assertCount(1, $sourceRegistry->sources);
        [$provider, $metadata] = $sourceRegistry->sources[0];

        $this->assertSame(InstrumentType::ASYNCHRONOUS_UP_DOWN_COUNTER, $metadata->instrumentType());
        $this->assertSame('view-name', $metadata->name());
        $this->assertSame('view-unit', $metadata->unit());
        $this->assertSame('view-description', $metadata->description());
        $this->assertSame(Temporality::CUMULATIVE, $metadata->temporality());

        $source = $provider->create(Temporality::CUMULATIVE);
        $registry->registerCallback(static fn (ObserverInterface $observer) => $observer->observe(5), $instrument);

        $clock->setTime(3);
        $registry->collectAndPush($streamIds);

        $this->assertEquals(
            new Metric(
                $instrumentationScope,
                $resource,
                'view-name',
                'view-unit',
                'view-description',
                new Sum(
                    [
                        new NumberDataPoint(5, Attributes::create([]), 1, 3),
                    ],
                    Temporality::CUMULATIVE,
                    false
                ),
            ),
            $source->collect(),
        );
        $this->assertSame(3, $source->collectionTimestamp());
    }

    public function test_create_synchronous_observer(): void
    {
        $resource = ResourceInfoFactory::emptyResource();
        $instrumentationScope = new InstrumentationScope('test', null, null, Attributes::create([]));

        $clock = new TestClock();
        $registry = new MetricRegistry(null, Attributes::factory(), $clock);
        $instrument = new Instrument(InstrumentType::UP_DOWN_COUNTER, 'name', '{unit}', 'description');
        $sourceRegistry = new CollectingSourceRegistry();
        $streamIds = (new StreamFactory())->createSynchronousWriter(
            $registry,
            $resource,
            $instrumentationScope,
            $instrument,
            1,
            [
                [new ViewProjection(
                    'view-name',
                    'view-unit',
                    'view-description',
                    null,
                    new SumAggregation(),
                ), new RegistryRegistration($sourceRegistry, new NoopStalenessHandler())],
            ],
            new NoneExemplarFilter(),
        );

        $this->assertCount(1, $sourceRegistry->sources);
        [$provider, $metadata] = $sourceRegistry->sources[0];

        $this->assertSame(InstrumentType::UP_DOWN_COUNTER, $metadata->instrumentType());
        $this->assertSame('view-name', $metadata->name());
        $this->assertSame('view-unit', $metadata->unit());
        $this->assertSame('view-description', $metadata->description());
        $this->assertSame(Temporality::DELTA, $metadata->temporality());

        $source = $provider->create(Temporality::DELTA);
        $registry->record($instrument, 5);

        $clock->setTime(3);
        $registry->collectAndPush($streamIds);

        $this->assertEquals(
            new Metric(
                $instrumentationScope,
                $resource,
                'view-name',
                'view-unit',
                'view-description',
                new Sum(
                    [
                        new NumberDataPoint(5, Attributes::create([]), 1, 3),
                    ],
                    Temporality::DELTA,
                    false
                ),
            ),
            $source->collect(),
        );
        $this->assertSame(3, $source->collectionTimestamp());
    }
}

final class CollectingSourceRegistry implements MetricSourceRegistryInterface
{
    /**
     * @var list<array{MetricSourceProviderInterface, MetricMetadataInterface, StalenessHandlerInterface}>
     */
    public array $sources = [];

    /**
     * @psalm-suppress InvalidPropertyAssignmentValue
     */
    #[\Override]
    public function add(MetricSourceProviderInterface $provider, MetricMetadataInterface $metadata, StalenessHandlerInterface $stalenessHandler): void
    {
        $this->sources[] = func_get_args();
    }
}
