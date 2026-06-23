<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\API\Baggage;

use OpenTelemetry\API\Baggage\Metadata;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Metadata::class)]
class MetadataTest extends TestCase
{
    public function test_get_value(): void
    {
        $metadata = new Metadata('some-metadata');

        $this->assertSame('some-metadata', $metadata->getValue());
    }

    public function test_get_empty_returns_metadata_with_empty_value(): void
    {
        $metadata = Metadata::getEmpty();

        $this->assertSame('', $metadata->getValue());
    }

    public function test_get_empty_returns_same_instance(): void
    {
        $this->assertSame(Metadata::getEmpty(), Metadata::getEmpty());
    }

    public function test_constructor_with_empty_string(): void
    {
        $metadata = new Metadata('');

        $this->assertSame('', $metadata->getValue());
    }

    public function test_constructor_with_complex_value(): void
    {
        $value = 'key1=value1;key2=value2';
        $metadata = new Metadata($value);

        $this->assertSame($value, $metadata->getValue());
    }
}
