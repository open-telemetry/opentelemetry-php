<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\Otlp;

use InvalidArgumentException;
use OpenTelemetry\API\Signals;
use OpenTelemetry\SDK\Common\Adapter\HttpDiscovery\MessageFactoryResolver;
use OpenTelemetry\SDK\Common\Http\Psr\Message\FactoryResolverInterface;
use Psr\Http\Message\UriInterface;

/**
 * Resolves non-signal-specific OTLP HTTP endpoints to signal-specific ones according to the specification.
 * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/protocol/exporter.md#endpoint-urls-for-otlphttp
 */
class HttpEndpointResolver implements HttpEndpointResolverInterface
{
    private const SCHEME_ATTRIBUTE = 'scheme';
    private const HOST_ATTRIBUTE = 'host';
    private const PORT_ATTRIBUTE = 'port';
    private const USER_ATTRIBUTE = 'user';
    private const PASS_ATTRIBUTE = 'pass';
    private const PATH_ATTRIBUTE = 'path';
    private const DEFAULT_SCHEME = 'https';
    private const ROOT_PATH = '/';

    private readonly FactoryResolverInterface $httpFactoryResolver;

    public function __construct(?FactoryResolverInterface $httpFactoryResolver = null)
    {
        $this->httpFactoryResolver = $httpFactoryResolver ?? MessageFactoryResolver::create();
    }

    public static function create(?FactoryResolverInterface $httpFactoryResolver = null): self
    {
        return new self($httpFactoryResolver);
    }

    #[\Override]
    public function resolve(string $endpoint, string $signal): UriInterface
    {
        $components = self::parseEndpoint($endpoint);

        return self::addPort(
            self::addUserInfo(
                $this->createDefaultUri($components, $signal),
                $components
            ),
            $components
        );
    }

    #[\Override]
    public function resolveToString(string $endpoint, string $signal): string
    {
        return (string) $this->resolve($endpoint, $signal);
    }

    private function createUri(): UriInterface
    {
        return $this->httpFactoryResolver->resolveUriFactory()
            ->createUri();
    }

    private function createDefaultUri(array $components, string $signal): UriInterface
    {
        if (isset($components[self::SCHEME_ATTRIBUTE])) {
            self::validateScheme($components[self::SCHEME_ATTRIBUTE]);
        }

        return $this->createUri()
            ->withScheme($components[self::SCHEME_ATTRIBUTE] ?? self::DEFAULT_SCHEME)
            ->withPath(self::resolvePath($components[self::PATH_ATTRIBUTE] ?? self::ROOT_PATH, $signal))
            ->withHost($components[self::HOST_ATTRIBUTE]);
    }

    private static function validateScheme(string $protocol): void
    {
        if (!in_array($protocol, HttpEndpointResolverInterface::VALID_SCHEMES, true)) {
            throw new InvalidArgumentException(sprintf(
                'Expected protocol to be http or https, given: "%s"',
                $protocol
            ));
        }
    }

    private static function validateSignal(string $signal): void
    {
        if (!in_array($signal, Signals::SIGNALS)) {
            throw new InvalidArgumentException(sprintf(
                'Signal must be one of "%s". Given "%s"',
                implode(', ', Signals::SIGNALS),
                $signal
            ));
        }
    }

    private static function parseEndpoint(string $endpoint): array
    {
        $result = parse_url($endpoint);

        if (!is_array($result) || !isset($result[self::HOST_ATTRIBUTE])) {
            throw new InvalidArgumentException(sprintf(
                'Failed to parse endpoint "%s"',
                $endpoint
            ));
        }

        return $result;
    }

    private static function addUserInfo(UriInterface $uri, array $components): UriInterface
    {
        if (isset($components[self::USER_ATTRIBUTE])) {
            $uri = $uri->withUserInfo(
                $components[self::USER_ATTRIBUTE],
                $components[self::PASS_ATTRIBUTE] ?? null
            );
        }

        return $uri;
    }

    private static function addPort(UriInterface $uri, array $components): UriInterface
    {
        if (isset($components[self::PORT_ATTRIBUTE])) {
            $uri = $uri->withPort(
                $components[self::PORT_ATTRIBUTE]
            );
        }

        return $uri;
    }

    private static function resolvePath(string $path, string $signal): string
    {
        self::validateSignal($signal);

        return str_replace('//', '/', sprintf('%s/%s', $path, self::getDefaultPath($signal)));
    }

    private static function getDefaultPath(string $signal): string
    {
        return HttpEndpointResolverInterface::DEFAULT_PATHS[$signal];
    }
}
