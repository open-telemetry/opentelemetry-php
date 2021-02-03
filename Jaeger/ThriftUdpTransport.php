<?php

namespace Jaeger;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Thrift\Exception\TTransportException;
use Thrift\Transport\TTransport;

class ThriftUdpTransport extends TTransport
{

    const MAX_UDP_PACKET = 65000;

    private $socket;

    /**
     * @var string
     */
    private $host;

    /**
     * @var int
     */
    private $port;

    protected $buffer = "";
    
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * ThriftUdpTransport constructor.
     * @param string $host
     * @param int $port
     * @param LoggerInterface $logger
     */
    public function __construct(string $host, int $port, LoggerInterface $logger = null)
    {
        $this->host = $host;
        $this->port = $port;
        $this->socket = @socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        $this->logger = $logger ?? new NullLogger();
    }

    /**
     * Whether this transport is open.
     *
     * @return boolean true if open
     */
    public function isOpen()
    {
        return $this->socket !== null;
    }

    /**
     * Open the transport for reading/writing
     *
     * @throws TTransportException if cannot open
     */
    public function open()
    {
        $ok = @socket_connect($this->socket, $this->host, $this->port);
        if ($ok === false) {
            $errorcode = socket_last_error();
            $errormsg = socket_strerror($errorcode);
            throw new TTransportException("socket_connect failed: [$errorcode] $errormsg");
        }
    }

    /**
     * Close the transport.
     */
    public function close()
    {
        @socket_close($this->socket);
        $this->socket = null;
    }

    /**
     * Read some data into the array.
     *
     * @todo
     *
     * @param int $len How much to read
     * @return string The data that has been read
     */
    public function read($len)
    {
    }

    /**
     * Writes the given data out.
     *
     * @param string $buf The data to write
     * @throws TTransportException if writing fails
     */
    public function write($buf)
    {
        // ensure that the data will still fit in a UDP packeg
        if (strlen($this->buffer) + strlen($buf) > self::MAX_UDP_PACKET) {
            throw new TTransportException("Data does not fit within one UDP packet");
        }

        // buffer up some data
        $this->buffer .= $buf;

        if (!$this->isOpen()) {
            $errorcode = socket_last_error();
            $errormsg = socket_strerror($errorcode);
            throw new TTransportException("transport is closed: [$errorcode] $errormsg");
        }

        $ok = @socket_write($this->socket, $this->buffer);
        if ($ok === false) {
            $errorcode = socket_last_error();
            $errormsg = socket_strerror($errorcode);
            throw new TTransportException("socket_write failed: [$errorcode] $errormsg");
        }
    }

    public function flush()
    {
        // no data to send; don't send a packet
        if (strlen($this->buffer) == 0) {
            return;
        }

        // flush the buffer to the socket
        if (!socket_sendto($this->socket, $this->buffer, strlen($this->buffer), 0, $this->server, $this->port)) {
            $errorcode = socket_last_error();
            $errormsg = socket_strerror($errorcode);
            throw new TTransportException("Could not flush data: [$errorcode] $errormsg");
        }

        $this->buffer = ""; // empty the buffer
    }
}