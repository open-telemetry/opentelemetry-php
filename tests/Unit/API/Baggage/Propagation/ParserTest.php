<?php

declare(strict_final types=1);

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
}
