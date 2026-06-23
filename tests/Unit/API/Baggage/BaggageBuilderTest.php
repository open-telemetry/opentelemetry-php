<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\API\Baggage;

use OpenTelemetry\API\Baggage\BaggageBuilder;
use OpenTelemetry\API\Baggage\BaggageInterface;
use OpenTelemetry\API\Baggage\Metadata;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(BaggageBuilder::class)]
class BaggageBuilderTest extends TestCase
{
    public function test_build_returns_baggage(): void
    {
        $builder = new BaggageBuilder();
        $this->assertInstanceOf(BaggageInterface::class, $builder->build());
    }

    public function test_set_adds_entry(): void
    {
        $builder = new BaggageBuilder();
        $result = $builder->set('key', 'value');
        $this->assertSame($builder, $result);
        $baggage = $builder->build();
        $entry = $baggage->getEntry('key');
        $this->assertNotNull($entry);
        $this->assertSame('value', $entry->getValue());
    }

    public function test_set_with_metadata(): void
    {
        $metadata = new Metadata('meta');
        $builder = new BaggageBuilder();
        $builder->set('key', 'value', $metadata);
        $baggage = $builder->build();
        $entry = $baggage->getEntry('key');
        $this->assertNotNull($entry);
        $this->assertSame('meta', $entry->getMetadata()->getValue());
    }

    public function test_set_empty_key_is_ignored(): void
    {
        $builder = new BaggageBuilder();
        $result = $builder->set('', 'value');
        $this->assertSame($builder, $result);
        $baggage = $builder->build();
        $this->assertNull($baggage->getEntry(''));
    }

    public function test_remove_removes_entry(): void
    {
        $builder = new BaggageBuilder();
        $builder->set('key', 'value');
        $result = $builder->remove('key');
        $this->assertSame($builder, $result);
        $baggage = $builder->build();
        $this->assertNull($baggage->getEntry('key'));
    }

    public function test_set_overwrites_existing(): void
    {
        $builder = new BaggageBuilder();
        $builder->set('key', 'first');
        $builder->set('key', 'second');
        $baggage = $builder->build();
        $this->assertSame('second', $baggage->getEntry('key')->getValue());
    }
}
