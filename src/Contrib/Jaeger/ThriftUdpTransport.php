<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\Jaeger;

use Thrift\Exception\TTransportException;
use Thrift\Transport\TTransport;

class ThriftUdpTransport extends TTransport
{
    const MAX_UDP_PACKET = 65000;

    protected $server;
    protected $port;

    protected $socket = null;
    protected $buffer = '';

    // this implements a TTransport over UDP
    public function __construct($server, $port)
    {
        $this->server = $server;
        $this->port = $port;

        // open a UDP socket to somewhere
        if (!($this->socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP))) {
            $errorcode = socket_last_error();
            $errormsg = socket_strerror($errorcode);

            error_log("jaeger: transport: Couldn't create socket: [$errorcode] $errormsg");

            throw new TTransportException('unable to open UDP socket', TTransportException::UNKNOWN);
        }
    }

    public function isOpen()
    {
        return $this->socket != null;
    }

    // Open does nothing as connection is opened on creation
    // Required to maintain thrift.TTransport interface
    public function open()
    {
    }

    public function close()
    {
        socket_close($this->socket);
        $this->socket = null;
    }

    public function read($len)
    {
        // not implemented
    }

    public function write($buf)
    {
        // ensure that the data will still fit in a UDP packeg
        if (strlen($this->buffer) + strlen($buf) > self::MAX_UDP_PACKET) {
            throw new TTransportException('Data does not fit within one UDP packet', TTransportException::UNKNOWN);
        }

        // buffer up some data
        $this->buffer .= $buf;
    }

    public function flush()
    {
        // no data to send; don't send a packet
        if (strlen($this->buffer) == 0) {
            return;
        }

        // TODO(tylerc): This assumes that the whole buffer successfully sent... I believe
        // that this should always be the case for UDP packets, but I could be wrong.

        // flush the buffer to the socket
        if (!socket_sendto($this->socket, $this->buffer, strlen($this->buffer), 0, $this->server, $this->port)) {
            $errorcode = socket_last_error();
            $errormsg = socket_strerror($errorcode);
            error_log("jaeger: transport: Could not flush data: [$errorcode] $errormsg");
        }

        $this->buffer = ''; // empty the buffer
    }
} 