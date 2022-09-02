<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Metrics\Exemplar;

use OpenTelemetry\API\Trace\AbstractSpan;
use OpenTelemetry\API\Trace\SpanContextFactory;
use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Metrics\Data\Exemplar;
use OpenTelemetry\SDK\Metrics\Exemplar\BucketStorage;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\SDK\Metrics\Exemplar\BucketStorage
 */
final class BucketStorageTest extends TestCase
{
    public function test_empty_storage_returns_no_exemplars(): void
    {
        $storage = new BucketStorage(Attributes::factory());

        $this->assertEquals([], $storage->collect([], 0, 1));
    }

    public function test_storage_returns_stored_exemplars(): void
    {
        $storage = new BucketStorage(Attributes::factory());

        $storage->store(0, 0, 5, Attributes::create([]), Context::getRoot(), 7, 0);
        $storage->store(1, 1, 3, Attributes::create([]), Context::getRoot(), 8, 0);
        $storage->store(2, 0, 4, Attributes::create([]), Context::getRoot(), 9, 0);

        $this->assertEquals([
            0 => [
                new Exemplar(5, 7, Attributes::create([]), null, null),
                new Exemplar(4, 9, Attributes::create([]), null, null),
            ],
            1 => [
                new Exemplar(3, 8, Attributes::create([]), null, null),
            ],
        ], $storage->collect([0 => Attributes::create([]), 1 => Attributes::create([])], 0, 1));
    }

    public function test_storage_stores_trace_information(): void
    {
        $storage = new BucketStorage(Attributes::factory());

        $context = AbstractSpan::wrap(SpanContextFactory::create('12345678901234567890123456789012', '1234567890123456'))
            ->storeInContext(Context::getRoot());

        $storage->store(0, 0, 5, Attributes::create([]), $context, 7, 0);

        $this->assertEquals([
            0 => [
                new Exemplar(5, 7, Attributes::create([]), '12345678901234567890123456789012', '1234567890123456'),
            ],
        ], $storage->collect([0 => Attributes::create([])], 0, 1));
    }

    public function test_storage_returns_filtered_attributes(): void
    {
        $storage = new BucketStorage(Attributes::factory());

        $storage->store(0, 0, 5, Attributes::create(['foo' => 5, 'bar' => 7]), Context::getRoot(), 7, 0);

        $this->assertEquals([
            0 => [
                new Exemplar(5, 7, Attributes::create(['bar' => 7]), null, null),
            ],
        ], $storage->collect([0 => Attributes::create(['foo' => 5]), 1 => Attributes::create([])], 0, 1));
    }

    public function test_storage_doesnt_return_exemplars_with_revision_out_of_requested_range(): void
    {
        $storage = new BucketStorage(Attributes::factory());

        $storage->store(0, 0, 5, Attributes::create([]), Context::getRoot(), 7, 0);
        $storage->store(1, 1, 3, Attributes::create([]), Context::getRoot(), 8, 0);
        $storage->store(2, 0, 4, Attributes::create([]), Context::getRoot(), 9, 1);

        $this->assertEquals([
            0 => [
                new Exemplar(5, 7, Attributes::create([]), null, null),
            ],
            1 => [
                new Exemplar(3, 8, Attributes::create([]), null, null),
            ],
        ], $storage->collect([0 => Attributes::create([]), 1 => Attributes::create([])], 0, 1));
    }
}
