<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Trace;

use Exception;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use InvalidArgumentException;
use OpenTelemetry\Sdk\Trace\Zipkin\SpanConverter;
use OpenTelemetry\Trace as API;

class JaegerExporter implements Exporter
{
    const IMPLEMENTED_FORMATS = [
        '/api/v1/spans',
        '/api/v2/spans',
    ];

    /**
     * @var string
     */
    private $endpointUrl;

    /**
     * @var SpanConverter
     */
    private $spanConverter;

    public function __construct($name, string $endpointUrl, SpanConverter $spanConverter = null)
    {
        $url = parse_url($endpointUrl);

        if (!is_array($url)) {
            throw new InvalidArgumentException('Unable to parse provided DSN');
        }

        if (!isset($url['scheme'])
            || !isset($url['host'])
            || !isset($url['port'])
            || !isset($url['path'])
        ) {
            throw new InvalidArgumentException('Endpoint should have scheme, host, port and path');
        }

        if (!in_array($url['path'], self::IMPLEMENTED_FORMATS)) {
            throw new InvalidArgumentException(
                sprintf("Current implementation supports only '%s' format", implode(' or ', self::IMPLEMENTED_FORMATS))
            );
        }

        $this->endpointUrl = $endpointUrl;

        $this->spanConverter = $spanConverter ?? new SpanConverter($name);
    }

    /**
     * Exports the provided Span data via the Zipkin protocol
     *
     * @param iterable<API\Span> $spans Array of Spans
     * @return int return code, defined on the Exporter interface
     */
    public function export(iterable $spans) : int
    {
        if (empty($spans)) {
            return Exporter::SUCCESS;
        }

        $convertedSpans = [];
        foreach ($spans as &$span) {
            array_push($convertedSpans, $this->spanConverter->convert($span));
        }

        try {
            $container = [];
            $history = Middleware::history($container);
            $stack = HandlerStack::create();
            // Add the history middleware to the handler stack.
            $stack->push($history);
            $json = json_encode($convertedSpans);
            $client = new \GuzzleHttp\Client(['handler' => $stack]);
            $headers = ['content-type' => 'application/json'];
            $request = new Request('POST', $this->endpointUrl, $headers, $json);
            $response = $client->send($request, ['timeout' => 30, 'debug' => true]);

            /* used for body debugging:
            foreach ($container as $transaction) {
                echo (string) $transaction['request']->getBody();
            }
            */
        } catch (Exception $e) {
            return Exporter::FAILED_RETRYABLE;
        }

        return Exporter::SUCCESS;
    }

    public function shutdown(): void
    {
        // TODO: Implement shutdown() method.
    }
}
