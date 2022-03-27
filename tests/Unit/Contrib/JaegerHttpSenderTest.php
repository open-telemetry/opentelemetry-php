<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Contrib;

use OpenTelemetry\Contrib\Jaeger\HttpSender;
use OpenTelemetry\Contrib\Jaeger\ParsedEndpointUrl;
use OpenTelemetry\Contrib\Jaeger\BatchAdapter\BatchAdapterFactoryInterface;
use OpenTelemetry\Contrib\Jaeger\BatchAdapter\BatchAdapterInterface;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\Tests\Unit\SDK\Util\SpanData;
use PHPUnit\Framework\TestCase;
use Thrift\Protocol\TProtocol;

/**
 * @covers OpenTelemetry\Contrib\Jaeger\HttpSender
 */
class JaegerHttpSenderTest extends TestCase
{
    use UsesHttpClientTrait;

    private function createSenderAndMocks(array $inputs): array
    {
        [
            'serviceName' => $serviceName
        ] = $inputs;

        $mockBatchAdapterFactory = $this->createBatchAdapterFactoryMock();

        /**
         * @psalm-suppress PossiblyInvalidArgument
         */
        $sender = new HttpSender(
            $this->getClientInterfaceMock(),
            $this->getRequestFactoryInterfaceMock(),
            $this->getStreamFactoryInterfaceMock(),
            $serviceName,
            $this->createParsedEndpointUrlMock(),
            $mockBatchAdapterFactory
        );

        return [
            'sender' => $sender,
            'mockBatchAdapterFactory' => $mockBatchAdapterFactory
        ];
    }

    private function createParsedEndpointUrlMock(): ParsedEndpointUrl
    {
        /** @var ParsedEndpointUrl */
        $mock = $this->createMock(ParsedEndpointUrl::class);

        return $mock;
    }

    private function createBatchAdapterFactoryMock(): BatchAdapterFactoryInterface
    {
        return new class implements BatchAdapterFactoryInterface
        {
            //Just enough spy functionality for what was needed for now. Generalize and extend as needed
            private array $interceptedVals = [];

            public function getInterceptedVals()
            {
                return $this->interceptedVals;
            }

            public function createBatchAdapter(array $vals): BatchAdapterInterface 
            {
                $this->interceptedVals[] = $vals;

                $mockBatchAdapter = new class implements BatchAdapterInterface
                {
                    public function write(TProtocol $output): void { }
                };

                return $mockBatchAdapter;
            }
        };
    }

    public function test_span_and_process_data_are_batched_by_resource()
    {
        [
            'sender' => $sender,
            'mockBatchAdapterFactory' => $mockBatchAdapterFactory
        ] = $this->createSenderAndMocks([
            'serviceName' => 'nameOfThe1stLogicalApp'
        ]);

        $spans = [
            (new SpanData())->setResource(ResourceInfo::create(
                new Attributes(), //code should default service.name from how its set above
            )),
            (new SpanData())->setResource(ResourceInfo::create(
                new Attributes([
                    'service.name' => 'nameOfThe2ndLogicalApp',
                    'telemetry.sdk.name' => 'opentelemetry'
                ]),
            ))
        ];

        $sender->send($spans);

        $interceptedVals = $mockBatchAdapterFactory->getInterceptedVals();
        $this->assertSame(2, count($interceptedVals));

        //1st batch
        $this->assertSame(1, count($interceptedVals[0]['spans'])); //Detailed tests for the conversion live elsewhere

        $this->assertSame('nameOfThe1stLogicalApp', $interceptedVals[0]['process']->serviceName);

        $this->assertSame(0, count($interceptedVals[0]['process']->tags));

        //2nd batch
        $this->assertSame(1, count($interceptedVals[1]['spans'])); //Detailed tests for the conversion live elsewhere

        $this->assertSame('nameOfThe2ndLogicalApp', $interceptedVals[1]['process']->serviceName);

        $this->assertSame(1, count($interceptedVals[1]['process']->tags));
        $this->assertSame('telemetry.sdk.name', $interceptedVals[1]['process']->tags[0]->key);
        $this->assertSame('opentelemetry', $interceptedVals[1]['process']->tags[0]->vStr);
    }
}
