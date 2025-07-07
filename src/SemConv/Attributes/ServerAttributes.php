<?php

// DO NOT EDIT, this is an Auto-generated file from script/semantic-conventions

declare(strict_types=1);

namespace OpenTelemetry\SemConv\Attributes;

/**
 * Semantic attributes and corresponding values for server.
 * @see https://opentelemetry.io/docs/specs/semconv/registry/attributes/server/
 */
interface ServerAttributes
{
    /**
     * Server domain name if available without reverse DNS lookup; otherwise, IP address or Unix domain socket name.
     * When observed from the client side, and when communicating through an intermediary, `server.address` SHOULD represent the server address behind any intermediaries, for example proxies, if it's available.
     *
     * @stable
     */
    public const SERVER_ADDRESS = 'server.address';

    /**
     * Server port number.
     * When observed from the client side, and when communicating through an intermediary, `server.port` SHOULD represent the server port behind any intermediaries, for example proxies, if it's available.
     *
     * @stable
     */
    public const SERVER_PORT = 'server.port';

}
