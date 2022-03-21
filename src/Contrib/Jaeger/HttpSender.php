<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\Jaeger;

use Jaeger\Thrift\Batch;
use Jaeger\Thrift\Process;
use Jaeger\Thrift\Span as JTSpan;
use OpenTelemetry\SDK\Behavior\LogsMessagesTrait;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SDK\Trace\SpanDataInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Thrift\Protocol\TBinaryProtocol;
use Thrift\Protocol\TProtocol;

class HttpSender
{
    use LogsMessagesTrait;

    private string $serviceName;

    private TProtocol $protocol;

    public function __construct(
        ClientInterface $client,
        RequestFactoryInterface $requestFactory,
        StreamFactoryInterface $streamFactory,
        string $serviceName,
        ParsedEndpointUrl $parsedEndpoint
    ) {
        $this->serviceName = $serviceName;

        $transport = (new ThriftHttpTransport(
            $client,
            $requestFactory,
            $streamFactory,
            $parsedEndpoint
        ));

        $this->protocol = new TBinaryProtocol($transport);
    }

    /**
     * @param SpanDataInterface[] $spans
     */
    public function send(array $spans): void
    {
        $spansGroupedByResource = self::groupSpansByResource($spans);
        $batchArray = $this->createBatchesPerResource($spansGroupedByResource);

        foreach ($batchArray as $batch) {
            $this->sendBatch($batch);
        }
    }

    private static function groupSpansByResource(array $spans): array
    {
        $spansGroupedByResource = [];
        foreach ($spans as $span) {
            $resource = $span->getResource();
            $resourceAsKey = json_encode($resource); //TODO - find a better way to make these work as keys in a map for deduplication

            if(!isset($spansGroupedByResource[$resourceAsKey])) {
                $spansGroupedByResource[$resourceAsKey] = [
                    'spans' => [],
                    'resource' => $resource
                ];
            }

            $spansGroupedByResource[$resourceAsKey]['spans'][] = $span;
        }

        return $spansGroupedByResource;
    }

    private function createBatchesPerResource(array $spansGroupedByResource): array
    {
        $batches = [];
        foreach ($spansGroupedByResource as $resourceKey => $data) { //TODO - name this better
            /** @var SpanDataInterface[] */
            $spans = $data['spans'];
            /** @var ResourceInfo */
            $resource = $data['resource'];

            $process = $this->createProcessFromResource($resource);
            /** @var JTSpan[] */
            $convertedSpans = (new SpanConverter())->convert($spans);

            $batch = new Batch([
                'spans' => $convertedSpans,
                'process' => $process
            ]);

            $batches[] = $batch;
        }

        return $batches;
    }

    private function createProcessFromResource(ResourceInfo $resource): Process
    {
        $serviceName = $this->serviceName; //TODO - figure out if this really comes from the default resource in practice - https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/resource/sdk.md#sdk-provided-resource-attributes
        
        foreach ($resource->getAttributes() as $key => $value) {

        }
    }

    private function sendBatch(Batch $batch): void 
    {
        $batch->write($this->protocol);
        $this->protocol->getTransport()->flush();
    }


    //TODO - delete this
    // /**
    //  * @param JTSpan[] $spans
    //  */
    // public function sendOld(array $spans): void
    // {
    //     ///** @var Tag[] $tags */ TODO - uncomment this once the code below is uncommented/adapted
    //     $tags = [];

    //     //TODO - determine what of this is still needed and how to adapt it for spec compliance
    //     // foreach ($this->tracer->getTags() as $k => $v) {
    //     //     if (!in_array($k, $this->mapper->getSpecialSpanTags())) {
    //     //         if (strpos($k, $this->mapper->getProcessTagsPrefix()) !== 0) {
    //     //             continue ;
    //     //         }

    //     //         $quoted = preg_quote($this->mapper->getProcessTagsPrefix());
    //     //         $k = preg_replace(sprintf('/^%s/', $quoted), '', $k);
    //     //     }

    //     //     if ($k === JAEGER_HOSTNAME_TAG_KEY) {
    //     //         $k = "hostname";
    //     //     }

    //     //     $tags[] = new Tag([
    //     //         "key" => $k,
    //     //         "vType" => TagType::STRING,
    //     //         "vStr" => $v
    //     //     ]);
    //     // }

    //     // $tags[] = new Tag([
    //     //     "key" => "format",
    //     //     "vType" => TagType::STRING,
    //     //     "vStr" => "jaeger.thrift"
    //     // ]);

    //     // $tags[] = new Tag([
    //     //     "key" => "ip",
    //     //     "vType" => TagType::STRING,
    //     //     "vStr" => $this->tracer->getIpAddress()
    //     // ]);

    //     $batch = new Batch([
    //         'spans' => $spans,
    //         'process' => new Process([
    //             'serviceName' => $this->serviceName,
    //             'tags' => $tags,
    //         ]),
    //     ]);

    //     $batch->write($this->protocol);
    //     $this->protocol->getTransport()->flush();
    // }
}
