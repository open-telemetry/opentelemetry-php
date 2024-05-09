<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Metrics\Exemplar;

use OpenTelemetry\API\Trace\Span;
use OpenTelemetry\API\Trace\SpanContext;
use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Metrics\Data\Exemplar;
use OpenTelemetry\SDK\Metrics\Exemplar\BucketStorage;
use PHPUnit\Framework\TestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\OpenTelemetry\SDK\Metrics\Exemplar\BucketStorage::class)]
final class BucketStorageTest extends TestCase
{
    public function test_empty_storage_returns_no_exemplars(): void
    {
        $storage = new BucketStorage();

        $this->assertEquals([], $storage->collect([]));
    }

    public function test_storage_returns_stored_exemplars(): void
    {
        $storage = new BucketStorage();

        $storage->store(0, 0, 5, Attributes::create([]), Context::getRoot(), 7);
        $storage->store(1, 1, 3, Attributes::create([]), Context::getRoot(), 8);
        $storage->store(2, 0, 4, Attributes::create([]), Context::getRoot(), 9);

        $this->assertEquals([
            0 => [
                new Exemplar(0, 5, 7, Attributes::create([]), null, null),
                new Exemplar(0, 4, 9, Attributes::create([]), null, null),
            ],
            1 => [
                new Exemplar(1, 3, 8, Attributes::create([]), null, null),
            ],
        ], Exemplar::groupByIndex($storage->collect([0 => Attributes::create([]), 1 => Attributes::create([])])));
    }

    public function test_storage_stores_trace_information(): void
    {
        $storage = new BucketStorage();

        $context = Span::wrap(SpanContext::create('12345678901234567890123456789012', '1234567890123456'))
            ->storeInContext(Context::getRoot());

        $storage->store(0, 0, 5, Attributes::create([]), $context, 7);

        $this->assertEquals([
            0 => [
                new Exemplar(0, 5, 7, Attributes::create([]), '12345678901234567890123456789012', '1234567890123456'),
            ],
        ], Exemplar::groupByIndex($storage->collect([0 => Attributes::create([])])));
    }

    public function test_storage_returns_filtered_attributes(): void
    {
        $storage = new BucketStorage();

        $storage->store(0, 0, 5, Attributes::create(['foo' => 5, 'bar' => 7]), Context::getRoot(), 7);

        $this->assertEquals([
            0 => [
                new Exemplar(0, 5, 7, Attributes::create(['bar' => 7]), null, null),
            ],
        ], Exemplar::groupByIndex($storage->collect([0 => Attributes::create(['foo' => 5]), 1 => Attributes::create([])])));
    }
}
