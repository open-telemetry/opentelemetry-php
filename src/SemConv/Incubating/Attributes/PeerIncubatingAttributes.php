<?php

// DO NOT EDIT, this is an Auto-generated file from script/semantic-conventions

declare(strict_types=1);

namespace OpenTelemetry\SemConv\Incubating\Attributes;

/**
 * Semantic attributes and corresponding values for peer.
 * @see https://opentelemetry.io/docs/specs/semconv/registry/attributes/peer/
 * May contain @experimental Semantic Conventions which may change or be removed in the future.
 */
interface PeerIncubatingAttributes
{
    /**
     * The [`service.name`](/docs/resource/README.md#service) of the remote service. SHOULD be equal to the actual `service.name` resource attribute of the remote service if any.
     *
     * Examples of `peer.service` that users may specify:
     *
     * - A Redis cache of auth tokens as `peer.service="AuthTokenCache"`.
     * - A gRPC service `rpc.service="io.opentelemetry.AuthService"` may be hosted in both a gateway, `peer.service="ExternalApiService"` and a backend, `peer.service="AuthService"`.
     *
     * @experimental
     * @deprecated Use `server.service.name` on the client side or `client.service.name` on the server side.
     */
    public const PEER_SERVICE = 'peer.service';

}
