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
     * @experimental
     */
    public const PEER_SERVICE = 'peer.service';

}
