<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\API\Baggage\Propagation;

use OpenTelemetry\API\Baggage\BaggageBuilderInterface;
use OpenTelemetry\API\Baggage\Metadata;
use OpenTelemetry\API\Baggage\Propagation\Parser;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\API\Baggage\Propagation\Parser
 */
class ParserTest extends TestCase
{
    /** @var BaggageBuilderInterface&\PHPUnit\Framework\MockObject\MockObject */
    private BaggageBuilderInterface $builder;

    public function setUp(): void
    {
        $this->builder = $this->createMock(BaggageBuilderInterface::class);
    }

    /**
     * @dataProvider headerProvider
     */
    public function test_parse_into(string $header): void
    {
        $parser = new Parser($header);

        $this->builder
            ->expects($this->exactly(2))
            ->method('set')
            ->withConsecutive(
                [$this->equalTo('key1'), $this->equalTo('value1'), $this->anything()],
                [$this->equalTo('key2'), $this->equalTo('value2'), $this->anything()],
            );

        $parser->parseInto($this->builder);
    }

    public function headerProvider(): array
    {
        return [
            'normal' => ['key1=value1,key2=value2'],
            'encoded' => ['%6b%65%79%31=value1,%6b%65%79%32=value2'],
        ];
    }

    public function test_parse_into_with_properties(): void
    {
        //@see https://www.w3.org/TR/baggage/#example
        $header = 'key1=value1;property1;property2, key2 = value2, key3=value3; propertyKey=propertyValue';
        $parser = new Parser($header);

        $this->builder
            ->expects($this->exactly(3))
            ->method('set')
            ->withConsecutive(
                [
                    $this->equalTo('key1'),
                    $this->equalTo('value1'),
                    $this->callback(function (Metadata $metadata) {
                        $this->assertSame('property1;property2', $metadata->getValue());

                        return true;
                    }),
                ],
                [
                    $this->equalTo('key2'),
                    $this->equalTo('value2'),
                    $this->equalTo(null),
                ],
                [
                    $this->equalTo('key3'),
                    $this->equalTo('value3'),
                    $this->callback(function (Metadata $metadata) {
                        $this->assertSame('propertyKey=propertyValue', $metadata->getValue());

                        return true;
                    }),
                ],
            );

        $parser->parseInto($this->builder);
    }

    /**
     * @dataProvider invalidHeaderProvider
     */
    public function test_parse_into_with_invalid_header(string $header): void
    {
        $parser = new Parser($header);

        $this->builder
            ->expects($this->never())
            ->method('set');

        $parser->parseInto($this->builder);
    }

    public function invalidHeaderProvider(): array
    {
        return [
            'nothing' => [''],
            'empty values' => [',,,,,'],
            'no equals' => ['key1,key2'],
            'empty key' => ['=value'],
            'key with invalid char' => ['@foo=bar'],
            'value with invalid char' => ['foo="bar"'],
        ];
    }
}
