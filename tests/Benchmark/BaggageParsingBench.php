<?php

declare(strict_types=1);

use OpenTelemetry\API\Baggage\BaggageBuilder;
use OpenTelemetry\API\Baggage\Propagation\Parser;

class BaggageParsingBench
{
    /**
     * @Revs(1000)
     * @Iterations(10)
     * @OutputTimeUnit("microseconds")
     */
    public function benchParseInto(): void
    {
        $builder = new BaggageBuilder();
        $header = 'value1=foo,value2=bar';
        $parser = new Parser($header);
        $parser->parseInto($builder);
    }

    /**
     * @Revs(1000)
     * @Iterations(10)
     * @OutputTimeUnit("microseconds")
     */
    public function benchParseWithMetadata(): void
    {
        $builder = new BaggageBuilder();
        $header = 'value1=foo;metadata1;metadata2,value2=bar;meta=baz';
        $parser = new Parser($header);
        $parser->parseInto($builder);
    }
}
