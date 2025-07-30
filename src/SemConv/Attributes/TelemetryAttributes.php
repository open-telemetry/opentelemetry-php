<?php

// DO NOT EDIT, this is an Auto-generated file from script/semantic-conventions

declare(strict_types=1);

namespace OpenTelemetry\SemConv\Attributes;

/**
 * Semantic attributes and corresponding values for telemetry.
 * @see https://opentelemetry.io/docs/specs/semconv/registry/attributes/telemetry/
 */
interface TelemetryAttributes
{
    /**
     * The language of the telemetry SDK.
     *
     * @stable
     */
    public const TELEMETRY_SDK_LANGUAGE = 'telemetry.sdk.language';

    /**
     * none
     * @stable
     */
    public const TELEMETRY_SDK_LANGUAGE_VALUE_CPP = 'cpp';

    /**
     * none
     * @stable
     */
    public const TELEMETRY_SDK_LANGUAGE_VALUE_DOTNET = 'dotnet';

    /**
     * none
     * @stable
     */
    public const TELEMETRY_SDK_LANGUAGE_VALUE_ERLANG = 'erlang';

    /**
     * none
     * @stable
     */
    public const TELEMETRY_SDK_LANGUAGE_VALUE_GO = 'go';

    /**
     * none
     * @stable
     */
    public const TELEMETRY_SDK_LANGUAGE_VALUE_JAVA = 'java';

    /**
     * none
     * @stable
     */
    public const TELEMETRY_SDK_LANGUAGE_VALUE_NODEJS = 'nodejs';

    /**
     * none
     * @stable
     */
    public const TELEMETRY_SDK_LANGUAGE_VALUE_PHP = 'php';

    /**
     * none
     * @stable
     */
    public const TELEMETRY_SDK_LANGUAGE_VALUE_PYTHON = 'python';

    /**
     * none
     * @stable
     */
    public const TELEMETRY_SDK_LANGUAGE_VALUE_RUBY = 'ruby';

    /**
     * none
     * @stable
     */
    public const TELEMETRY_SDK_LANGUAGE_VALUE_RUST = 'rust';

    /**
     * none
     * @stable
     */
    public const TELEMETRY_SDK_LANGUAGE_VALUE_SWIFT = 'swift';

    /**
     * none
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
