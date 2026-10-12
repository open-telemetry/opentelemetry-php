<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Integration\Config\ComponentProvider\Trace;

/**
 * Marker for the `ExperimentalComposableSampler` schema type.
 *
 * The composable samplers nested under `composite/development` share their names with the
 * top-level `Sampler` keys (`always_on`, `always_off`, ...) but are a distinct schema type
 * with a distinct set of members. Registering them against {@see \OpenTelemetry\SDK\Trace\SamplerInterface}
 * would collide with the SDK's own providers of those names, so they are registered against
 * this type instead.
 *
 * The SDK ships no composable sampler, so implementations here are no-ops; this type exists
 * only so that the upstream snippets parse.
 */
interface ComposableSamplerInterface
{
}
