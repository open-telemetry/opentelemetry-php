<?php

// DO NOT EDIT, this is an Auto-generated file from script/semantic-conventions

declare(strict_types=1);

namespace OpenTelemetry\SemConv\Attributes;

/**
 * Semantic attributes and corresponding values for client.
 * @see https://opentelemetry.io/docs/specs/semconv/registry/attributes/client/
 */
interface ClientAttributes
{
    /**
     * Client address - domain name if available without reverse DNS lookup; otherwise, IP address or Unix domain socket name.
     * When observed from the server side, and when communicating through an intermediary, `client.address` SHOULD represent the client address behind any intermediaries,  for example proxies, if it's available.
     *
     * @stable
     */
    public const CLIENT_ADDRESS = 'client.address';

    /**
     * Client port number.
     * When observed from the server side, and when communicating through an intermediary, `client.port` SHOULD represent the client port behind any intermediaries,  for example proxies, if it's available.
     *
     * @stable
     */
    public const CLIENT_PORT = 'client.port';

}
