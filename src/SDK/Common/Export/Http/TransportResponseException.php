<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Export\Http;

use RuntimeException;

/**
 * Thrown when the OTLP HTTP server responds with a non-2xx status code.
 *
 * The response body is captured here (already limited to 4 MiB by
 * {@see PsrUtils::readBodyWithSizeLimit()}) so callers can inspect partial
 * proto-encoded Status messages or plain-text error descriptions.
 */
final class TransportResponseException extends RuntimeException
{
    private int $statusCode;
    private string $responseBody;

    public function __construct(int $statusCode, string $responseBody, string $message = '')
    {
        parent::__construct($message !== '' ? $message : sprintf('HTTP %d', $statusCode));
        $this->statusCode = $statusCode;
        $this->responseBody = $responseBody;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Returns the raw (decoded) response body, already capped at
     * {@see ResponseBodySizeLimit::MAX_BYTES}.
     */
    public function getResponseBody(): string
    {
        return $this->responseBody;
    }
}
