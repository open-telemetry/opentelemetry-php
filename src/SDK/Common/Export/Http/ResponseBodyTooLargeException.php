<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Export\Http;

use RuntimeException;
use function sprintf;

/**
 * Thrown when an OTLP/HTTP response body exceeds the size limit, either as received or after decompression.
 *
 * @internal
 */
final class ResponseBodyTooLargeException extends RuntimeException
{
    public function __construct(public readonly int $limit)
    {
        parent::__construct(sprintf('Response body exceeds the limit of %d bytes', $limit));
    }
}
