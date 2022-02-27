<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\Jaeger;

use Psr\Http\Client\ClientInterface;
use Thrift\Transport\THttpClient;
use Thrift\Exception\TTransportException;
use Thrift\Factory\TStringFuncFactory;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

class CustomizedTHttpClient extends THttpClient {

    private ClientInterface $psr18Client;

    private RequestFactoryInterface $requestFactory;

    private StreamFactoryInterface $streamFactory;

    private string $endpointUrl;

    public function setPsr18HttpClient(ClientInterface $client): self
    {
        $this->psr18Client = $client;

        return $this;
    }

    public function setPsr7RequestFactory(RequestFactoryInterface $requestFactory): self
    {
        $this->requestFactory = $requestFactory;

        return $this;
    }

    public function setPsr7StreamFactory(StreamFactoryInterface $streamFactory): self
    {
        $this->streamFactory = $streamFactory;

        return $this;
    }

    public function setEndpointURL(string $endpointUrl): self 
    {
        $this->endpointUrl = $endpointUrl;

        return $this;
    }

    /**
     * Opens and sends the actual request over the HTTP connection
     *
     * @throws TTransportException if a writing error occurs
     */
    public function flush()
    {
        $request = $this->requestFactory->createRequest('POST', $this->endpointUrl);

        $headers = [
            'Host' => $this->host_, //Port will be implied - https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Host
            'Accept' => 'application/x-thrift',
            'User-Agent' => 'PHP/THttpClient',
            'Content-Type' => 'application/x-thrift',
            'Content-Length' => TStringFuncFactory::create()->strlen($this->buf_)
        ];
        foreach ($headers as $key => $value) {
            $request = $request->withAddedHeader($key, $value);
        }

        $request = $request->withBody(
            $this->streamFactory->createStream($this->buf_)
        );

        $this->psr18Client->sendRequest($request);

        $this->buf_ = '';
    }
}