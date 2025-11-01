<?php

// DO NOT EDIT, this is an Auto-generated file from script/semantic-conventions

declare(strict_types=1);

namespace OpenTelemetry\SemConv\Incubating\Attributes;

/**
 * Semantic attributes and corresponding values for telemetry.
 * @see https://opentelemetry.io/docs/specs/semconv/registry/attributes/telemetry/
 * May contain @experimental Semantic Conventions which may change or be removed in the future.
 */
interface TelemetryIncubatingAttributes
{
    /**
     * The name of the auto instrumentation agent or distribution, if used.
     *
     * Official auto instrumentation agents and distributions SHOULD set the `telemetry.distro.name` attribute to
     * a string starting with `opentelemetry-`, e.g. `opentelemetry-java-instrumentation`.
     *
     * @experimental
     */
    public const TELEMETRY_DISTRO_NAME = 'telemetry.distro.name';

    /**
     * The version string of the auto instrumentation agent or distribution, if used.
     *
     * @experimental
     */
    public const TELEMETRY_DISTRO_VERSION = 'telemetry.distro.version';

    /**
     * The language of the telemetry SDK.
     *
     * @stable
     */
    public const TELEMETRY_SDK_LANGUAGE = 'telemetry.sdk.language';

    /**
     * @stable
     */
    public const TELEMETRY_SDK_LANGUAGE_VALUE_CPP = 'cpp';

    /**
     * @stable
     */
    public const TELEMETRY_SDK_LANGUAGE_VALUE_DOTNET = 'dotnet';

    /**
     * @stable
     */
    public const TELEMETRY_SDK_LANGUAGE_VALUE_ERLANG = 'erlang';

    /**
     * @stable
     */
    public const TELEMETRY_SDK_LANGUAGE_VALUE_GO = 'go';

    /**
     * @stable
     */
    public const TELEMETRY_SDK_LANGUAGE_VALUE_JAVA = 'java';

    /**
     * @stable
     */
    public const TELEMETRY_SDK_LANGUAGE_VALUE_NODEJS = 'nodejs';

    /**
     * @stable
     */
    public const TELEMETRY_SDK_LANGUAGE_VALUE_PHP = 'php';

    /**
     * @stable
     */
    public const TELEMETRY_SDK_LANGUAGE_VALUE_PYTHON = 'python';

    /**
     * @stable
     */
    public const TELEMETRY_SDK_LANGUAGE_VALUE_RUBY = 'ruby';

    /**
     * @stable
     */
    public const TELEMETRY_SDK_LANGUAGE_VALUE_RUST = 'rust';

    /**
     * @stable
     */
    public const TELEMETRY_SDK_LANGUAGE_VALUE_SWIFT = 'swift';

    /**
     * @stable
     */
    public const TELEMETRY_SDK_LANGUAGE_VALUE_WEBJS = 'webjs';

    /**
     * The name of the telemetry SDK as defined above.
     *
     * The OpenTelemetry SDK MUST set the `telemetry.sdk.name` attribute to `opentelemetry`.
     * If another SDK, like a fork or a vendor-provided implementation, is used, this SDK MUST set the
     * `telemetry.sdk.name` attribute to the fully-qualified class or module name of this SDK's main entry point
     * or another suitable identifier depending on the language.
     * The identifier `opentelemetry` is reserved and MUST NOT be used in this case.
     * All custom identifiers SHOULD be stable across different versions of an implementation.
     *
     * @stable
     */
    public const TELEMETRY_SDK_NAME = 'telemetry.sdk.name';

    /**
     * The version string of the telemetry SDK.
     *
     * @stable
     */
    public const TELEMETRY_SDK_VERSION = 'telemetry.sdk.version';

}
