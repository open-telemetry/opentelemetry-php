<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Export\Http;

use function assert;
use BadMethodCallException;
use function count;
use function explode;
use function in_array;
use InvalidArgumentException;
use OpenTelemetry\API\Behavior\LogsMessagesTrait;
use OpenTelemetry\SDK\Common\Export\TransportInterface;
use OpenTelemetry\SDK\Common\Future\CancellationInterface;
use OpenTelemetry\SDK\Common\Future\CompletedFuture;
use OpenTelemetry\SDK\Common\Future\ErrorFuture;
use OpenTelemetry\SDK\Common\Future\FutureInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Client\NetworkExceptionInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use RuntimeException;
use function strtolower;
use Throwable;
use function time_nanosleep;
use function trim;

/**
 * @template CONTENT_TYPE of string
 * @implements TransportInterface<CONTENT_TYPE>
 */
final class PsrTransport implements TransportInterface
{
    use LogsMessagesTrait;

    /**
     * Default limit for the response body, also after decompression.
     *
     * @see https://github.com/open-telemetry/opentelemetry-proto/blob/v1.11.1/docs/specification.md#otlphttp-response
     */
    public const DEFAULT_MAX_RESPONSE_BODY_SIZE = 4 * 1024 * 1024;

    /**
     * Distinct reasons a response body was ignored, which have been logged as a warning already.
     */
    private const MAX_REPORTED_BODY_ERRORS = 16;

    private bool $closed = false;

    /** @var array<string, true> */
    private array $reportedBodyErrors = [];

    /**
     * @param CONTENT_TYPE $contentType
     */
    public function __construct(
        private readonly ClientInterface $client,
        private readonly RequestFactoryInterface $requestFactory,
        private readonly StreamFactoryInterface $streamFactory,
        private readonly string $endpoint,
        private readonly string $contentType,
        private readonly array $headers,
        private readonly array $compression,
        private readonly int $retryDelay,
        private readonly int $maxRetries,
        private readonly int $maxResponseBodySize = self::DEFAULT_MAX_RESPONSE_BODY_SIZE,
    ) {
        if ($maxResponseBodySize < 1) {
            throw new InvalidArgumentException('The maximum response body size must be positive');
        }
    }

    #[\Override]
    public function contentType(): string
    {
        return $this->contentType;
    }

    /**
     * @psalm-suppress ArgumentTypeCoercion
     */
    #[\Override]
    public function send(string $payload, ?CancellationInterface $cancellation = null): FutureInterface
    {
        if ($this->closed) {
            return new ErrorFuture(new BadMethodCallException('Transport closed'));
        }

        $body = PsrUtils::encode($payload, $this->compression, $appliedEncodings);
        $request = $this->requestFactory
            ->createRequest('POST', $this->endpoint)
            ->withBody($this->streamFactory->createStream($body))
            ->withHeader('Content-Type', $this->contentType)
        ;
        if ($appliedEncodings) {
            $request = $request->withHeader('Content-Encoding', $appliedEncodings);
        }
        foreach ($this->headers as $header => $value) {
            $request = $request->withAddedHeader($header, $value);
        }
        if (trim($request->getHeaderLine('Accept-Encoding')) === '') {
            // A client that adds its own Accept-Encoding decodes the response, and may leave Content-Encoding in
            // place, so that the body is decoded again here. Asking for an identity response avoids both.
            $request = $request->withHeader('Accept-Encoding', 'identity');
        }

        for ($retries = 0;; $retries++) {
            $response = null;
            $e = null;

            try {
                $response = $this->client->sendRequest($request);
                if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {
                    break;
                }

                // An oversized response is not retried, whatever its status.
                $this->checkBodySize($response);

                if ($response->getStatusCode() >= 400 && $response->getStatusCode() < 600 && !in_array($response->getStatusCode(), [429, 502, 503, 504], true)) {
                    throw new RuntimeException($response->getReasonPhrase(), $response->getStatusCode());
                }
            } catch (NetworkExceptionInterface $e) {
            } catch (Throwable $e) {
                return new ErrorFuture($e);
            }

            if ($retries >= $this->maxRetries) {
                return new ErrorFuture(new RuntimeException('Export retry limit exceeded', 0, $e));
            }

            $delay = PsrUtils::retryDelay($retries, $this->retryDelay, $response);
            $sec = (int) $delay;
            $nsec = (int) (($delay - (float) $sec) * 1e9);

            /** @psalm-suppress ArgumentTypeCoercion */
            if (time_nanosleep($sec, $nsec) !== true) {
                return new ErrorFuture(new RuntimeException('Export cancelled', 0, $e));
            }
        }

        assert(isset($response));

        // A null response is a success without a payload, which the exporters don't hydrate. An empty string
        // would be hydrated, and is not valid JSON.
        try {
            $body = $this->readBody($response);
            if ($body === '') {
                $body = null;
            }
        } catch (ResponseBodyTooLargeException $e) {
            return new ErrorFuture($e);
        } catch (RuntimeException $e) {
            // The server accepted the export, and the body is only used for a partial success.
            $this->logIgnoredBody($e, $response);
            $body = null;
        } catch (Throwable $e) {
            return new ErrorFuture($e);
        }

        return new CompletedFuture($body);
    }

    /**
     * A server that always answers in an encoding that can't be decoded would fill the log with a warning for
     * every export, so only the first one for each reason is a warning, the rest are debug messages.
     */
    private function logIgnoredBody(RuntimeException $e, ResponseInterface $response): void
    {
        $message = 'OTLP/HTTP response body ignored, it can not be read or decoded: ' . $e->getMessage();
        $context = ['status_code' => $response->getStatusCode()];

        if (isset($this->reportedBodyErrors[$message]) || count($this->reportedBodyErrors) >= self::MAX_REPORTED_BODY_ERRORS) {
            self::logDebug($message, $context);

            return;
        }

        $this->reportedBodyErrors[$message] = true;
        self::logWarning($message, $context);
    }

    /**
     * Checks the body of a response that is not going to be used, such as an error. An unreadable body is
     * ignored, so that the status of the response stays the reason for the failure.
     *
     * @throws ResponseBodyTooLargeException if the body exceeds the limit, after logging a warning
     */
    private function checkBodySize(ResponseInterface $response): void
    {
        try {
            $this->readBody($response);
        } catch (ResponseBodyTooLargeException $e) {
            throw $e;
        } catch (RuntimeException $e) {
            self::logDebug('OTLP/HTTP error response body could not be checked: ' . $e->getMessage());
        }
    }

    /**
     * @throws ResponseBodyTooLargeException if the body exceeds the limit, after logging a warning
     * @throws RuntimeException if the body can't be read or decoded
     */
    private function readBody(ResponseInterface $response): string
    {
        try {
            return PsrUtils::readBody(
                $response,
                self::parseContentEncoding($response),
                $this->maxResponseBodySize,
            );
        } catch (ResponseBodyTooLargeException $e) {
            self::logWarning('OTLP/HTTP response body discarded, it exceeds the size limit', [
                'limit' => $e->limit,
                'status_code' => $response->getStatusCode(),
            ]);

            throw $e;
        }
    }

    /**
     * @return list<string>
     */
    private static function parseContentEncoding(ResponseInterface $response): array
    {
        $encodings = [];
        foreach (explode(',', $response->getHeaderLine('Content-Encoding')) as $encoding) {
            if (($encoding = trim($encoding, " \t")) !== '') {
                $encodings[] = strtolower($encoding);
            }
        }

        return $encodings;
    }

    #[\Override]
    public function shutdown(?CancellationInterface $cancellation = null): bool
    {
        if ($this->closed) {
            return false;
        }

        $this->closed = true;

        return true;
    }

    #[\Override]
    public function forceFlush(?CancellationInterface $cancellation = null): bool
    {
        return !$this->closed;
    }
}
