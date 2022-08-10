<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Contrib\Jaeger;

use OpenTelemetry\Contrib\Jaeger\BatchAdapter\BatchAdapterFactoryInterface;
use OpenTelemetry\Contrib\Jaeger\BatchAdapter\BatchAdapterInterface;
use OpenTelemetry\Contrib\Jaeger\HttpSender;
use OpenTelemetry\Contrib\Jaeger\ParsedEndpointUrl;
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
            'mockBatchAdapterFactory' => $mockBatchAdapterFactory,
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
        return new class() implements BatchAdapterFactoryInterface {
            //Just enough spy functionality for what was needed for now. Generalize and extend as needed
            private array $interceptedValues = [];

            public function getInterceptedValues()
            {
                return $this->interceptedValues;
            }

            public function create(array $values): BatchAdapterInterface
            {
                $this->interceptedValues[] = $values;

                $mockBatchAdapter = new class() implements BatchAdapterInterface {
                    public function write(TProtocol $output): void
                    {
                    }
                };

                return $mockBatchAdapter;
            }
        };
    }

    public function test_span_and_process_data_are_batched_by_resource(): void
    {
        [
            'sender' => $sender,
            'mockBatchAdapterFactory' => $mockBatchAdapterFactory
        ] = $this->createSenderAndMocks([
            'serviceName' => 'nameOfThe1stLogicalApp',
        ]);

        $spans = [
            (new SpanData())->setResource(ResourceInfo::create(
                Attributes::create([]), //code should default service.name from how its set above
            )),
            (new SpanData())->setResource(ResourceInfo::create(
                Attributes::create([
                    'service.name' => 'nameOfThe2ndLogicalApp',
                ]),
            )),
        ];

        $sender->send($spans);

        $interceptedValues = $mockBatchAdapterFactory->getInterceptedValues();
        $this->assertSame(2, count($interceptedValues));

        //1st batch
        $this->assertSame(1, count($interceptedValues[0]['spans'])); //Detailed tests for the span conversion live elsewhere

        //2nd batch
        $this->assertSame(1, count($interceptedValues[1]['spans'])); //Detailed tests for the span conversion live elsewhere
    }

    public function test_process_service_names_are_correctly_set_from_resource_attributes_or_the_default_service_name(): void
    {
        [
            'sender' => $sender,
            'mockBatchAdapterFactory' => $mockBatchAdapterFactory
        ] = $this->createSenderAndMocks([
            'serviceName' => 'nameOfThe1stLogicalApp',
        ]);

        $spans = [
            (new SpanData())->setResource(ResourceInfo::create(
                Attributes::create([]), //code should default service.name from how its set above
            )),
            (new SpanData())->setResource(ResourceInfo::create(
                Attributes::create([
                    'service.name' => 'nameOfThe2ndLogicalApp',
                ]),
            )),
        ];

        $sender->send($spans);

        $interceptedValues = $mockBatchAdapterFactory->getInterceptedValues();

        //1st batch
        $this->assertSame('nameOfThe1stLogicalApp', $interceptedValues[0]['process']->serviceName);

        //2nd batch
        $this->assertSame('nameOfThe2ndLogicalApp', $interceptedValues[1]['process']->serviceName);
    }

    public function test_tags_are_correctly_set_from_resource_attributes(): void
    {
        [
            'sender' => $sender,
            'mockBatchAdapterFactory' => $mockBatchAdapterFactory
        ] = $this->createSenderAndMocks([
            'serviceName' => 'someServiceName',
        ]);

        $spans = [
            (new SpanData())->setResource(ResourceInfo::create(
                Attributes::create([]),
            )),
            (new SpanData())->setResource(ResourceInfo::create(
                Attributes::create([
                    'telemetry.sdk.name' => 'opentelemetry',
                ]),
            )),
        ];

        $sender->send($spans);

        $interceptedValues = $mockBatchAdapterFactory->getInterceptedValues();

        //1st batch
        $this->assertSame(0, count($interceptedValues[0]['process']->tags));

        //2nd batch
        $this->assertSame(1, count($interceptedValues[1]['process']->tags));

        $this->assertSame('telemetry.sdk.name', $interceptedValues[1]['process']->tags[0]->key);
        $this->assertSame('opentelemetry', $interceptedValues[1]['process']->tags[0]->vStr);
    }
}
