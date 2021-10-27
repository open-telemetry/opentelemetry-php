<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\SDK\Unit\Trace\SpanProcessor;


use PHPUnit\Framework\TestCase;
use OpenTelemetry\SDK\Trace\SpanProcessor\ConsoleSpanExporter;
use OpenTelemetry\Tests\SDK\Util\SpanData;
use OpenTelemetry\SDK\Trace\StatusData;
use OpenTelemetry\API\Trace\StatusCode;
use OpenTelemetry\SDK\Trace\Attributes;
use OpenTelemetry\SDK\Trace\SpanContext;
use Opentelemetry\SDK\InstrumentationLibrary;


class ConsoleSpanExporterTest extends TestCase
{

    public function testConsoleExporterWorks()
    {

        $span = (new SpanData())
        ->setName('my.service')
        ->setParentContext(
            SpanContext::create(
                '10000000000000000000000000000000',
                '1000000000000000'
            )
        )
        ->setStatus(
            new StatusData(
                StatusCode::STATUS_ERROR,
                'status_description'
            )
        )
        ->setInstrumentationLibrary(new InstrumentationLibrary(
            'instrumentation_library_name',
            'instrumentation_library_version'
        ))
        ->addAttribute('fruit', 'apple')
        ->addEvent('validators.list', new Attributes(['job' => 'stage.updateTime']), 1505855799433901068)
        ->setHasEnded(true);

        $exp = new ConsoleSpanExporter();
        $exp->export([$span]);


        $expected = <<<EOF
        {
            "name": "my.service",
            "context": {
                "trace_id": "00000000000000000000000000000000",
                "span_id": "0000000000000000",
                "trace_state": null
            },
            "parent_span_id": "1000000000000000",
            "kind": "KIND_INTERNAL",
            "start": 1505855794194009601,
            "end": 1505855799465726528,
            "attributes": [
                {
                    "key": "fruit",
                    "value": "apple"
                }
            ],
            "status": {
                "code": "Error",
                "description": "status_description"
            },
            "events": [
                {
                    "name": "validators.list",
                    "timestamp": 1505855799433901068,
                    "attributes": [
                        {
                            "key": "job",
                            "value": "stage.updateTime"
                        }
                    ]
                }
            ]
        }

        EOF;

        $this->expectOutputString($expected);
    }

    public function test_shutdown(): void
    {
        $this->assertTrue((new ConsoleSpanExporter())->shutdown());
    }

    public function test_forceFlush(): void
    {
        $this->assertTrue((new ConsoleSpanExporter())->forceFlush());
    }
}