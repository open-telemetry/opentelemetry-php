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

/**
 * Class ZipkinExporter - implements the export interface for data transfer via Zipkin protocol
 * @package OpenTelemetry\Exporter
 */
class ZipkinExporter implements Exporter
{
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
        $parsedDsn = parse_url($endpointUrl);

        if (!is_array($parsedDsn)) {
            throw new InvalidArgumentException('Unable to parse provided DSN');
        }

        if (
            !isset($parsedDsn['scheme'])
            || !isset($parsedDsn['host'])
            || !isset($parsedDsn['port'])
            || !isset($parsedDsn['path'])
        ) {
            throw new InvalidArgumentException('Endpoint should have scheme, host, port and path');
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
    public function export(iterable $spans): int
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
            $response = $client->send($request, ['timeout' => 30]);

            // Used for debugging output for exporters
            foreach ((array) $container as $transaction) {
                echo (string) $transaction['request']->getBody();
            }
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
