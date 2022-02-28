<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\Jaeger;

use BadMethodCallException;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Thrift\Factory\TStringFuncFactory;
use Thrift\Transport\TTransport;

/**
 * This was created in lieu of Thrift\Transport\THttpClient because of it's usage of fopen for making network requests
 */
class ThriftHttpTransport extends TTransport
{
    private ClientInterface $psr18Client;

    private RequestFactoryInterface $requestFactory;

    private StreamFactoryInterface $streamFactory;

    private string $endpointUrl;

    private string $buffer = '';

    public function __construct(
        ClientInterface $client,
        RequestFactoryInterface $requestFactory,
        StreamFactoryInterface $streamFactory,
        string $endpointUrl
    ) {
        $this->psr18Client = $client;
        $this->requestFactory = $requestFactory;
        $this->streamFactory = $streamFactory;

        $this->endpointUrl = $endpointUrl;
    }

    public function isOpen()
    {
        throw new BadMethodCallException(__FUNCTION__ . " is unused as of this writing. See Thrift\Transport\THttpClient for a reference implementation.");
    }

    public function open()
    {
        throw new BadMethodCallException(__FUNCTION__ . " is unused as of this writing. See Thrift\Transport\THttpClient for a reference implementation.");
    }

    public function close()
    {
        throw new BadMethodCallException(__FUNCTION__ . " is unused as of this writing. See Thrift\Transport\THttpClient for a reference implementation.");
    }

    public function read($len)
    {
        throw new BadMethodCallException(__FUNCTION__ . " is unused as of this writing. See Thrift\Transport\THttpClient for a reference implementation.");
    }

    /**
     * Writes some data into the pending buffer
     *
     * @param string $buf The data to write
     */
    public function write($buf)
    {
        $this->buffer .= $buf;
    }

    /**
     * Opens and sends the actual request over the HTTP connection
     */
    public function flush()
    {
        $parsedDsn = parse_url($this->endpointUrl);
        $host = $parsedDsn['host'];

        $request = $this->requestFactory->createRequest('POST', $this->endpointUrl);

        $headers = [
            'Host' => $host, //Port will be implied - https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Host
            'Accept' => 'application/x-thrift',
            'User-Agent' => 'PHP/THttpClient',
            'Content-Type' => 'application/x-thrift',
            'Content-Length' => TStringFuncFactory::create()->strlen($this->buffer),
        ];
        foreach ($headers as $key => $value) {
            $request = $request->withAddedHeader($key, $value);
        }

        $request = $request->withBody(
            $this->streamFactory->createStream($this->buffer)
        );

        $this->psr18Client->sendRequest($request);

        $this->buffer = '';
    }
}
