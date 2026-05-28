<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\API\Baggage\Propagation;

use OpenTelemetry\API\Baggage\BaggageBuilder;
use OpenTelemetry\API\Baggage\BaggageBuilderInterface;
use OpenTelemetry\API\Baggage\Propagation\Parser;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(Parser::class)]
class ParserTest extends TestCase
{
    /** @var BaggageBuilderInterface&MockObject */
    private BaggageBuilderInterface $builder;

    #[\Override]
    public function setUp(): void
    {
        $this->builder = $this->createMock(BaggageBuilderInterface::class);
    }

    public function test_parse_into_splits_by_comma(): void
    {
        $parser = new Parser('key1=value1,key2=value2,key3=value3');
        $this->builder->expects($this->exactly(3))->method('set');
        $parser->parseInto($this->builder);
    }

    #[DataProvider('headerProvider')]
    public function test_parse_into(string $header): void
    {
        $parser = new Parser($header);

        $this->builder
            ->expects($this->exactly(2))
            ->method('set')
            ->with(
                $this->stringStartsWith('key'),
                $this->stringStartsWith('value'),
                $this->anything(),
            );

        $parser->parseInto($this->builder);
    }

    public static function headerProvider(): array
    {
        return [
            'normal' => ['key1=value1,key2=value2'],
            'encoded' => ['%6b%65%79%31=value1,%6b%65%79%32=value2'],
        ];
    }

    public function test_parse_into_with_properties(): void
    {
        $builder = new BaggageBuilder();
        //@see https://www.w3.org/TR/baggage/#example
        $header = 'key1=value1;property1;property2, key2 = value2, key3=value3; propertyKey=propertyValue';
        $parser = new Parser($header);

        $expected = [
            'key1' => ['value1', 'property1;property2'],
            'key2' => ['value2', ''],
            'key3' => ['value3', 'propertyKey=propertyValue'],
        ];
        $parser->parseInto($builder);
        $baggage = $builder->build();
        foreach ($baggage->getAll() as $key => $entry) {
            $this->assertSame($expected[$key][0], $entry->getValue());
            $this->assertSame($expected[$key][1], $entry->getMetadata()->getValue());
        }
    }

    #[DataProvider('invalidHeaderProvider')]
    public function test_parse_into_with_invalid_header(string $header): void
    {
        $parser = new Parser($header);

        $this->builder
            ->expects($this->never())
            ->method('set');

        $parser->parseInto($this->builder);
    }

    public static function invalidHeaderProvider(): array
    {
        return [
            'nothing' => [''],
            'empty values' => [',,,,,'],
            'no equals' => ['key1,key2'],
            'empty key' => ['=value'],
            'key with invalid char' => ['@foo=bar'],
            'value with invalid char' => ['foo="bar"'],
            'missing value' => ['key1='],
        ];
    }

    public function test_parse_into_rejects_header_exceeding_w3c_byte_limit(): void
    {
        // https://www.w3.org/TR/baggage/#limits, max total bytes 8192
        $pairs = [];
        for ($i = 0; $i < 1024; $i++) {
            $pairs[] = "k{$i}=v{$i}";
        }
        $header = implode(',', $pairs);
        $this->assertGreaterThan(8192, strlen($header));

        $parser = new Parser($header);

        $this->builder
            ->expects($this->never())
            ->method('set');

        $parser->parseInto($this->builder);
    }

    public function test_parse_into_caps_entries_at_w3c_list_member_limit(): void
    {
        // https://www.w3.org/TR/baggage/#limits, max list-members 180
        $pairs = [];
        for ($i = 0; $i < 500; $i++) {
            $pairs[] = "k{$i}=v{$i}";
        }
        $header = implode(',', $pairs);
        $this->assertLessThanOrEqual(8192, strlen($header));

        $parser = new Parser($header);

        $this->builder
            ->expects($this->exactly(180))
            ->method('set');

        $parser->parseInto($this->builder);
    }

    public function test_parse_into_accepts_header_at_exactly_w3c_byte_limit(): void
    {
        // The byte cap is inclusive: strlen() > 8192 is rejected, so exactly 8192 is accepted.
        // Single valid pair padded to exactly 8192 bytes with a non-excluded value char.
        $prefix = 'key=';
        $header = $prefix . str_repeat('a', 8192 - strlen($prefix));
        $this->assertSame(8192, strlen($header));

        $parser = new Parser($header);

        $this->builder
            ->expects($this->once())
            ->method('set');

        $parser->parseInto($this->builder);
    }

    public function test_parse_into_rejects_header_one_byte_over_w3c_byte_limit(): void
    {
        // One byte past the inclusive cap (8193 > 8192) discards the entire header.
        $prefix = 'key=';
        $header = $prefix . str_repeat('a', 8193 - strlen($prefix));
        $this->assertSame(8193, strlen($header));

        $parser = new Parser($header);

        $this->builder
            ->expects($this->never())
            ->method('set');

        $parser->parseInto($this->builder);
    }

    public function test_parse_into_accepts_exactly_w3c_list_member_limit(): void
    {
        // 180 valid list-members are all accepted; the >= cap only trips on the 181st.
        $pairs = [];
        for ($i = 0; $i < 180; $i++) {
            $pairs[] = "k{$i}=v{$i}";
        }
        $header = implode(',', $pairs);
        $this->assertLessThanOrEqual(8192, strlen($header));

        $parser = new Parser($header);

        $this->builder
            ->expects($this->exactly(180))
            ->method('set');

        $parser->parseInto($this->builder);
    }

    public function test_parse_into_caps_at_w3c_list_member_limit_plus_one(): void
    {
        // 181 valid list-members: the 181st is dropped once $entries reaches 180.
        $pairs = [];
        for ($i = 0; $i < 181; $i++) {
            $pairs[] = "k{$i}=v{$i}";
        }
        $header = implode(',', $pairs);
        $this->assertLessThanOrEqual(8192, strlen($header));

        $parser = new Parser($header);

        $this->builder
            ->expects($this->exactly(180))
            ->method('set');

        $parser->parseInto($this->builder);
    }
}
