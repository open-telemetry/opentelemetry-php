<?php

namespace Jaeger;

use OpenTelemetry\Trace\Span;
use Jaeger\Thrift\Agent\AgentClient;
use Jaeger\Thrift\Batch;
use Jaeger\Thrift\Span as JTSpan;
use Thrift\Protocol\TCompactProtocol;
use Thrift\Exception\TTransportException;

final class JaegerTransport
{

    const STATUS_CODE_TAG_KEY = 'op.status_code';
    const STATUS_DESCRIPTION_TAG_KEY = 'op.status_description';

    private $transport;
    private $client;

    private $buffer = [];
    private $process = null;

    public function __construct($address = "127.0.0.1", $port = 6831)
    {
        $this->transport = new ThriftUdpTransport($address, $port);
        $p = new TCompactProtocol($this->transport);
        $this->client = new AgentClient($p, $p);
    }

    /**
    * Encode it to Jaeger Thrift Span format
    */
    private function encode(Span $span)
    {
        $spanParent = $span->getParent();

        $startTime = (int) ($span->getStartEpochTimestamp() / 1e3); // microseconds
        $duration = (int) (($span->getEnd() - $span->getStart()) / 1e3); // microseconds

        $references = '';
        $tags = array(
            self::STATUS_CODE_TAG_KEY => $span->getStatus()->getCanonicalStatusCode(),
            self::STATUS_DESCRIPTION_TAG_KEY => $span->getStatus()->getStatusDescription()
        );
        $logs = '';

        $traceId = $span->getContext()->getTraceID();
        $parentSpanId = $spanParent ? $spanParent->getSpanId() : null;

        return new JTSpan([
            "traceIdLow" => (is_array($traceId) ? $traceId["low"] : $traceId),
            "traceIdHigh" => (is_array($traceId) ? $traceId["high"] : 0),
            "spanId" => $span->getContext()->getSpanID(),
            "parentSpanId" => (is_numeric($parentSpanId) ? $parentSpanId : 0),
            "operationName" => $this->serviceName,
            "references" => $references,
            "flags" => $span->getContext()->getTraceFlags(),
            "startTime" => $startTime,
            "duration" => $duration,
            'tags' => $tags,
            "logs" => $logs,
        ]);
    }

    /**
    * Flush submits the internal buffer to the remote server. It returns the
    * number of spans flushed.
    */
    public function flush($spans)
    {

        foreach ($spans as $span) {
            array_push($this->buffer[], $this->encode($span));
        }

        $spans = count($this->buffer);

        // no spans to flush
        if ($spans <= 0) {
            return 0;
        }

        try {
            // emit a batch
            $this->client->emitBatch(new Batch([
                "process" => $this->process,
                "spans" => $this->buffer,
            ]));

            // flush & close the UDP
            $this->transport->flush();
            $this->transport->close();

            // reset the internal buffer
            $this->buffer = [];
        } catch (TTransportException $e) {
            error_log("jaeger: transport failure: " . $e->getMessage());
            return 0;
        }

        return $spans;
    }
}