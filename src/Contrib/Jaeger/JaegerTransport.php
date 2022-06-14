<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\Jaeger;

use Jaeger\Thrift\Agent\AgentClient;
use Jaeger\Thrift\Batch;
use Jaeger\Thrift\Process;
use Jaeger\Thrift\Span;
use OpenTelemetry\SDK\Behavior\EmitsEventsTrait;
use OpenTelemetry\SDK\Common\Event\Event\ErrorEvent;
use Thrift\Exception\TTransportException;
use Thrift\Protocol\TCompactProtocol;

final class JaegerTransport implements TransportInterface
{
    use EmitsEventsTrait;

    // DEFAULT_BUFFER_SIZE indicates the default maximum buffer size, or the size threshold
    // at which the buffer will be flushed to the agent.
    const DEFAULT_BUFFER_SIZE = 1;

    private $transport;
    private $client;

    private $buffer = [];
    private $process = null;
    private $maxBufferSize = 0;

    public function __construct(ParsedEndpointUrl $parsedEndpoint, $maxBufferSize = 0)
    {
        $this->transport = new ThriftUdpTransport($parsedEndpoint);
        $p = new TCompactProtocol($this->transport);
        $this->client = new AgentClient($p, $p);

        $this->maxBufferSize = ($maxBufferSize > 0 ? $maxBufferSize : self::DEFAULT_BUFFER_SIZE);
    }

    /**
    * Submits a new span to collectors, possibly delayed and/or with buffering.
    *
    * @param Span $span
    */
    public function append(Span $span, $serviceName): int
    {
        // Grab a copy of the process data, if we didn't already.
        if ($this->process == null) {
            $this->process = new Process([
                'serviceName' => $serviceName,
                'tags' => $span->tags,
            ]);
        }

        $this->buffer[] = $span;

        // TODO(tylerc): Buffer spans and send them in as few UDP packets as possible.
        return $this->flush();
    }

    /**
    * Flush submits the internal buffer to the remote server. It returns the
    * number of spans flushed.
    *
    * @param bool $force - force a flush, even on a partial buffer
    */
    public function flush($force = false): int
    {
        $spans = count($this->buffer);

        // buffer not full yet
        if (!$force && $spans < $this->maxBufferSize) {
            return 0;
        }

        // no spans to flush
        if ($spans <= 0) {
            return 0;
        }

        try {
            // emit a batch
            $this->client->emitBatch(new Batch([
                'process' => $this->process,
                'spans' => $this->buffer,
            ]));

            // flush the UDP data
            $this->transport->flush();

            // reset the internal buffer
            $this->buffer = [];

            // reset the process tag
            $this->process = null;
        } catch (TTransportException $e) {
            self::emit(new ErrorEvent('jaeger transport failure', $e));

            return 0;
        }

        return $spans;
    }

    /**
    * Does a clean shutdown of the reporter, flushing any traces that may be
    * buffered in memory.
    */
    public function close(): void
    {
        $this->flush(true); // flush all remaining data
        $this->transport->close();
    }
}
