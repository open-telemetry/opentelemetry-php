<?php

declare(strict_types=1);

namespace OpenTelemetry\Test\Unit\Config\SDK\Configuration\Environment;

use OpenTelemetry\Config\SDK\Configuration\Environment\EnvSource;
use OpenTelemetry\Config\SDK\Configuration\Environment\EnvSourceReader;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(EnvSourceReader::class)]
final class EnvSourceReaderTest extends TestCase
{
    public function test_read_returns_string_value_from_source(): void
    {
        $source = $this->createMock(EnvSource::class);
        $source->method('readRaw')->with('MY_VAR')->willReturn('value');

        $reader = new EnvSourceReader([$source]);
        $this->assertSame('value', $reader->read('MY_VAR'));
    }

    public function test_read_returns_null_when_no_sources_have_value(): void
    {
        $source = $this->createMock(EnvSource::class);
        $source->method('readRaw')->willReturn(null);

        $reader = new EnvSourceReader([$source]);
        $this->assertNull($reader->read('MISSING'));
    }

    public function test_read_returns_null_for_empty_sources(): void
    {
        $reader = new EnvSourceReader([]);
        $this->assertNull($reader->read('ANY'));
    }

    public function test_read_trims_whitespace_and_tabs(): void
    {
        $source = $this->createMock(EnvSource::class);
        $source->method('readRaw')->willReturn("  value\t ");

        $reader = new EnvSourceReader([$source]);
        $this->assertSame('value', $reader->read('MY_VAR'));
    }

    public function test_read_returns_null_for_empty_string_after_trim(): void
    {
        $source = $this->createMock(EnvSource::class);
        $source->method('readRaw')->willReturn('   ');

        $reader = new EnvSourceReader([$source]);
        $this->assertNull($reader->read('MY_VAR'));
    }

    public function test_read_skips_non_string_values(): void
    {
        $source1 = $this->createMock(EnvSource::class);
        $source1->method('readRaw')->willReturn(false);

        $source2 = $this->createMock(EnvSource::class);
        $source2->method('readRaw')->willReturn('value');

        $reader = new EnvSourceReader([$source1, $source2]);
        $this->assertSame('value', $reader->read('MY_VAR'));
    }

    public function test_read_returns_first_matching_source(): void
    {
        $source1 = $this->createMock(EnvSource::class);
        $source1->method('readRaw')->willReturn('first');

        $source2 = $this->createMock(EnvSource::class);
        $source2->expects($this->never())->method('readRaw');

        $reader = new EnvSourceReader([$source1, $source2]);
        $this->assertSame('first', $reader->read('MY_VAR'));
    }

    public function test_read_skips_array_values(): void
    {
        $source1 = $this->createMock(EnvSource::class);
        $source1->method('readRaw')->willReturn(['array_value']);

        $source2 = $this->createMock(EnvSource::class);
        $source2->method('readRaw')->willReturn('string_value');

        $reader = new EnvSourceReader([$source1, $source2]);
        $this->assertSame('string_value', $reader->read('MY_VAR'));
    }
}
