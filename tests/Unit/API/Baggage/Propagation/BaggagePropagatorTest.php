<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\API\Unit\Baggage\Propagation;

use OpenTelemetry\API\Baggage\Baggage;
use OpenTelemetry\API\Baggage\Metadata;
use OpenTelemetry\API\Baggage\Propagation\BaggagePropagator;
use OpenTelemetry\Context\Context;
use PHPUnit\Framework\TestCase;

/**
 * @covers OpenTelemetry\API\Baggage\Propagation\BaggagePropagator
 */
class BaggagePropagatorTest extends TestCase
{
    public function test_fields(): void
    {
        $this->assertSame(
            ['baggage'],
            BaggagePropagator::getInstance()->fields()
        );
    }

    public function test_inject_empty_baggage(): void
    {
        $carrier = [];

        BaggagePropagator::getInstance()->inject($carrier);

        $this->assertEmpty($carrier);
    }

    public function test_inject(): void
    {
        $carrier = [];

        BaggagePropagator::getInstance()->inject(
            $carrier,
            null,
            Context::getRoot()->withContextValue(
                Baggage::getBuilder()
                    ->set('nometa', 'nometa-value')
                    ->set('meta', 'meta-value', new Metadata('somemetadata; someother=foo'))
                    ->build()
            )
        );

        $this->assertSame(
            ['baggage' => 'nometa=nometa-value,meta=meta-value;somemetadata; someother=foo'],
            $carrier
        );
    }

    public function test_inject_encodes_value(): void
    {
        $carrier = [];

        BaggagePropagator::getInstance()->inject(
            $carrier,
            null,
            Context::getRoot()->withContextValue(
                Baggage::getBuilder()
                    ->set('key1', 'val1')
                    ->set('key2', 'val2:val3')
                    ->set('key3', 'val4@#$val5')
                    ->set('key4', 'key=value', new Metadata('foo=bar=5'))
                    ->build()
            )
        );

        $this->assertSame(
            ['baggage' => 'key1=val1,key2=val2%3Aval3,key3=val4%40%23%24val5,key4=key%3Dvalue;foo=bar=5'],
            $carrier
        );
    }

    public function test_extract_missing_header(): void
    {
        $this->assertEquals(
            Context::getRoot(),
            BaggagePropagator::getInstance()->extract([])
        );
    }

    /** @dataProvider headerProvider */
    public function test_extract(string $header, Baggage $expectedBaggage): void
    {
        $propagator = BaggagePropagator::getInstance();

        $context = $propagator->extract(['baggage' => $header]);

        $this->assertEquals(
            $expectedBaggage,
            Baggage::fromContext($context)
        );
    }

    public function headerProvider(): array
    {
        return [
            'key - duplicate key' => ['key=value1,key=value2', Baggage::getBuilder()->set('key', 'value2')->build()],
            'key - leading spaces' => ['  key=value1', Baggage::getBuilder()->set('key', 'value1')->build()],
            'key - trailing spaces' => ['key    =value1', Baggage::getBuilder()->set('key', 'value1')->build()],
            'key - only spaces' => ['    =value1', Baggage::getEmpty()],
            'key - inner spaces' => ['k ey=value1', Baggage::getEmpty()],
            'key - invalid character' => ['ke?y=value1', Baggage::getEmpty()],
            'key - invalid =' => ['ke%3Dy=value1', Baggage::getEmpty()],
            'key - multiple invalid' => ['ke<y=value1, ;sss,key=value;meta1=value1;meta2=value2,ke(y=value;meta=val ', Baggage::getBuilder()->set('key', 'value', new Metadata('meta1=value1;meta2=value2'))->build()],

            'value - leading spaces' => ['key=  value1', Baggage::getBuilder()->set('key', 'value1')->build()],
            'value - trailing spaces' => ['key=value1  ', Baggage::getBuilder()->set('key', 'value1')->build()],
            'value - trailing spaces with metadata' => ['key=value1      ;meta1=meta2', Baggage::getBuilder()->set('key', 'value1', new Metadata('meta1=meta2'))->build()],
            'value - empty' => ['key=', Baggage::getEmpty()],
            'value - empty with metadata' => ['key1=;metakey=metaval', Baggage::getEmpty()],
            'value - only spaces' => ['key1=   ', Baggage::getEmpty()],
            'value - inner spaces' => ['key1=va lue', Baggage::getEmpty()],
            'value - invalid character' => ['key1=va\\lue', Baggage::getEmpty()],
            'value - urlencoded' => ['key1=val1,key2=val2%3Aval3,key3=val4%40%23%24val5', Baggage::getBuilder()->set('key1', 'val1')->set('key2', 'val2:val3')->set('key3', 'val4@#$val5')->build()],

            'multiple values - leadingSpaces' => ['key=  value1,key1=val', Baggage::getBuilder()->set('key', 'value1')->set('key1', 'val')->build()],
            'multiple values - trailingSpaces' => ['key=value1      ,key1=val', Baggage::getBuilder()->set('key', 'value1')->set('key1', 'val')->build()],
            'multiple values - empty with metadata' => ['key1=;metakey=metaval,key1=val', Baggage::getBuilder()->set('key1', 'val')->build()],
            'multiple values - only spaces' => ['key1=     ,key1=val', Baggage::getBuilder()->set('key1', 'val')->build()],
            'multiple values - inner spaces' => ['key=valu e1,key1=val', Baggage::getBuilder()->set('key1', 'val')->build()],
            'multiple values - invalid characters' => ['key=val\\ue1,key1=val', Baggage::getBuilder()->set('key1', 'val')->build()],

            'equal sign in metadata' => ['key1=val1;prop=1;prop2;prop3=2', Baggage::getBuilder()->set('key1', 'val1', new Metadata('prop=1;prop2;prop3=2'))->build()],
            'empty header' => ['', Baggage::getEmpty()],
            'blank header' => ['   ', Baggage::getEmpty()],
            'valid single value' => ['key=value', Baggage::getBuilder()->set('key', 'value')->build()],
            'valid single value with metadata' => ['key=value;metadata-key=value;othermetadata', Baggage::getBuilder()->set('key', 'value', new Metadata('metadata-key=value;othermetadata'))->build()],
            'valid multiple values' => ['key1=value1,key2=value2', Baggage::getBuilder()->set('key1', 'value1')->set('key2', 'value2')->build()],
            'valid complex value' => [
                "key1= value1; metadata-key = value; othermetadata, key2 =value2 , key3 =\tvalue3 ; ",
                Baggage::getBuilder()
                    ->set('key1', 'value1', new Metadata('metadata-key = value; othermetadata'))
                    ->set('key2', 'value2')
                    ->set('key3', 'value3')
                    ->build(),
            ],
            'valid complex value with some invalid' => [
                "key1= v;alsdf;-asdflkjasdf===asdlfkjadsf ,,a sdf9asdf-alue1; metadata-key = value; othermetadata, key2 =value2, key3 =\tvalue3 ; ",
                Baggage::getBuilder()
                    ->set('key1', 'v', new Metadata('alsdf;-asdflkjasdf===asdlfkjadsf'))
                    ->set('key2', 'value2')
                    ->set('key3', 'value3')
                    ->build(),
            ],
        ];
    }
}
