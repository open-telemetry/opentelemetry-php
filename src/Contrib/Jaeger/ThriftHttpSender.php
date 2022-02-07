<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\Jaeger;

use Jaeger\Thrift\Batch;
use Jaeger\Thrift\Process;
use Jaeger\Thrift\Span as JTSpan;
use OpenTelemetry\SDK\Behavior\LogsMessagesTrait;
use Thrift\Protocol\TBinaryProtocol;
use Thrift\Protocol\TProtocol;
use Thrift\Transport\THttpClient;

class ThriftHttpSender
{
    use LogsMessagesTrait;

    private string $serviceName;

    private TProtocol $protocol;

    public function __construct(
        string $serviceName,
        string $host,
        int $port,
        string $path,
        string $scheme
    ) {
        $this->serviceName = $serviceName;

        $transport = new THttpClient(
            $host,
            $port,
            $path,
            $scheme
        );
        $this->protocol = new TBinaryProtocol($transport);
    }

    /**
     * @param JTSpan[] $spans
     */
    public function send(array $spans): void
    {
        ///** @var Tag[] $tags */ TODO - uncomment this once the code below is uncommented/adapted
        $tags = [];

        //TODO - determine what of this is still needed and how to adapt it for spec compliance
        // foreach ($this->tracer->getTags() as $k => $v) {
        //     if (!in_array($k, $this->mapper->getSpecialSpanTags())) {
        //         if (strpos($k, $this->mapper->getProcessTagsPrefix()) !== 0) {
        //             continue ;
        //         }

        //         $quoted = preg_quote($this->mapper->getProcessTagsPrefix());
        //         $k = preg_replace(sprintf('/^%s/', $quoted), '', $k);
        //     }

        //     if ($k === JAEGER_HOSTNAME_TAG_KEY) {
        //         $k = "hostname";
        //     }

        //     $tags[] = new Tag([
        //         "key" => $k,
        //         "vType" => TagType::STRING,
        //         "vStr" => $v
        //     ]);
        // }

        // $tags[] = new Tag([
        //     "key" => "format",
        //     "vType" => TagType::STRING,
        //     "vStr" => "jaeger.thrift"
        // ]);

        // $tags[] = new Tag([
        //     "key" => "ip",
        //     "vType" => TagType::STRING,
        //     "vStr" => $this->tracer->getIpAddress()
        // ]);

        $batch = new Batch([
            'spans' => $spans,
            'process' => new Process([
                'serviceName' => $this->serviceName,
                'tags' => $tags,
            ]),
        ]);

        $batch->write($this->protocol);
        $this->protocol->getTransport()->flush();
    }
}
