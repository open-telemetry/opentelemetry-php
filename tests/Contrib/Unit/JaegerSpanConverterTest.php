<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Contrib\Unit;

use OpenTelemetry\API\Trace\StatusCode;
use OpenTelemetry\Contrib\Jaeger\SpanConverter;
use OpenTelemetry\SDK\Trace\StatusData;
use OpenTelemetry\Tests\SDK\Util\SpanData;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass SpanConverter
 */
class JaegerSpanConverterTest extends TestCase
{
    /**
     * @test
     */
    public function shouldCorrectlyGenerateJaegerThriftTags()
    {
        $span = (new SpanData())
            ->setStatus(
                new StatusData(
                    StatusCode::STATUS_ERROR,
                    'status_description'
                )
            )
            ->addAttribute('keyForBoolean', true)
            ->addAttribute('keyForArray', ['1stElement', '2ndElement']);

        $converter = new SpanConverter('test.name');
        $jtSpan = $converter->convert($span);

        $this->assertSame($jtSpan->tags[0]->key, 'op.status_code');
        $this->assertSame($jtSpan->tags[0]->vStr, 'Error');
        $this->assertSame($jtSpan->tags[1]->key, 'op.status_description');
        $this->assertSame($jtSpan->tags[1]->vStr, 'status_description');
        $this->assertSame($jtSpan->tags[2]->key, 'keyForBoolean');
        $this->assertSame($jtSpan->tags[2]->vStr, 'true');
        $this->assertSame($jtSpan->tags[3]->key, 'keyForArray');
        $this->assertSame($jtSpan->tags[3]->vStr, '1stElement,2ndElement');
    }
}
