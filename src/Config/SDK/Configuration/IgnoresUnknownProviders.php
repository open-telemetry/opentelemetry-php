<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\Configuration;

/**
 * Drops a component-map key with no registered provider, instead of failing the parse. Opt in with
 * `componentMap(...)->attribute(IgnoresUnknownProviders::ATTRIBUTE, true)`.
 *
 * It also drops typos, so leave it off wherever an unrecognized key should stay fatal. Currently set
 * on `distribution` only; see `README.md`.
 */
interface IgnoresUnknownProviders
{
    public const ATTRIBUTE = 'otel.ignore_unknown_providers';

    public function ignoresUnknownProviders(): bool;
}
