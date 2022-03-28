<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\Jaeger;

use Jaeger\Thrift\Process;
use OpenTelemetry\Contrib\Jaeger\BatchAdapter\BatchAdapterFactory;
use OpenTelemetry\Contrib\Jaeger\BatchAdapter\BatchAdapterFactoryInterface;
use OpenTelemetry\Contrib\Jaeger\BatchAdapter\BatchAdapterInterface;
use OpenTelemetry\Contrib\Jaeger\TagFactory\TagFactory;
use OpenTelemetry\SDK\Behavior\LogsMessagesTrait;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SemConv\ResourceAttributes;
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

    private BatchAdapterFactoryInterface $batchAdapterFactory;

    public function __construct(
        ClientInterface $client,
        RequestFactoryInterface $requestFactory,
        StreamFactoryInterface $streamFactory,
        string $serviceName,
        ParsedEndpointUrl $parsedEndpoint,
        BatchAdapterFactoryInterface $batchAdapterFactory = null
    ) {
        $this->serviceName = $serviceName;

        $this->protocol = new TBinaryProtocol(
            new ThriftHttpTransport(
                $client,
                $requestFactory,
                $streamFactory,
                $parsedEndpoint
            )
        );

        $this->batchAdapterFactory = $batchAdapterFactory ?? new BatchAdapterFactory();
    }

    public function send(iterable $spans): void
    {
        $batches = $this->createBatchesPerResource(
            self::groupSpansByResource($spans)
        );

        foreach ($batches as $batch) {
            $this->sendBatch($batch);
        }
    }

    private static function groupSpansByResource(iterable $spans): array
    {
        $spansGroupedByResource = [];
        foreach ($spans as $span) {
            /** @var ResourceInfo */
            $resource = $span->getResource();
            $resourceAsKey = $resource->serialize();

            if (!isset($spansGroupedByResource[$resourceAsKey])) {
                $spansGroupedByResource[$resourceAsKey] = [
                    'spans' => [],
                    'resource' => $resource,
                ];
            }

            $spansGroupedByResource[$resourceAsKey]['spans'][] = $span;
        }

        return $spansGroupedByResource;
    }

    private function createBatchesPerResource(array $spansGroupedByResource): array
    {
        $batches = [];
        foreach ($spansGroupedByResource as $unused => $dataForBatch) {
            $spans = $dataForBatch['spans'];
            $resource = $dataForBatch['resource'];

            $process = $this->createProcessFromResource($resource);
            $convertedSpans = (new SpanConverter())->convert($spans);

            $batch = $this->batchAdapterFactory->create([
                'spans' => $convertedSpans,
                'process' => $process,
            ]);

            $batches[] = $batch;
        }

        return $batches;
    }

    private function createProcessFromResource(ResourceInfo $resource): Process
    {
        $serviceName = $this->serviceName; //Defaulting to (what should be) the default resource's service name

        $tags = [];
        foreach ($resource->getAttributes() as $key => $value) {
            if ($key === ResourceAttributes::SERVICE_NAME) {
                $serviceName = (string) $value;

                continue;
            }

            $tags[] = TagFactory::createTag($key, $value);
        }

        return new Process([
            'serviceName' => $serviceName,
            'tags' => $tags,
        ]);
    }

    private function sendBatch(BatchAdapterInterface $batch): void
    {
        $batch->write($this->protocol);
        $this->protocol->getTransport()->flush();
    }
}
