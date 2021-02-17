<?php

declare(strict_types=1);

namespace Jaeger;

use Jaeger\Thrift\Span;

interface Transport
{

    /**
    * Append converts the span to the wire representation and adds it
    * to sender's internal buffer.  If the buffer exceeds its designated
    * size, the transport should call Flush() and return the number of spans
    * flushed, otherwise return 0.
    *
    * @param Span $span
    */
    public function append(Span $span, $serviceName);

    /**
    * Flush submits the internal buffer to the remote server. It returns the
    * number of spans flushed.
    */
    public function flush();

    /**
    * Does a clean shutdown of the transport, flushing any traces that may
    * remain in the internal buffer.
    */
    public function close();
}
