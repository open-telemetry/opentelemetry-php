<?php

// DO NOT EDIT, this is an Auto-generated file from script/semantic-conventions

declare(strict_types=1);

namespace OpenTelemetry\SemConv\Incubating\Attributes;

/**
 * Semantic attributes and corresponding values for feature_flag.
 * @see https://opentelemetry.io/docs/specs/semconv/registry/attributes/feature_flag/
 * May contain @experimental Semantic Conventions which may change or be removed in the future.
 */
interface FeatureFlagIncubatingAttributes
{
    /**
     * The unique identifier for the flag evaluation context. For example, the targeting key.
     *
     * @experimental
     */
    public const FEATURE_FLAG_CONTEXT_ID = 'feature_flag.context.id';

    /**
     * A message providing more detail about an error that occurred during feature flag evaluation in human-readable form.
     *
     * @experimental
     */
    public const FEATURE_FLAG_ERROR_MESSAGE = 'feature_flag.error.message';

    /**
     * The lookup key of the feature flag.
     *
     * @experimental
     */
    public const FEATURE_FLAG_KEY = 'feature_flag.key';

    /**
     * Identifies the feature flag provider.
     *
     * @experimental
     */
    public const FEATURE_FLAG_PROVIDER_NAME = 'feature_flag.provider.name';

    /**
     * The reason code which shows how a feature flag value was determined.
     *
     * @experimental
     */
    public const FEATURE_FLAG_RESULT_REASON = 'feature_flag.result.reason';

    /**
     * The resolved value is static (no dynamic evaluation).
     * @experimental
     */
    public const FEATURE_FLAG_RESULT_REASON_VALUE_STATIC = 'static';

    /**
     * The resolved value fell back to a pre-configured value (no dynamic evaluation occurred or dynamic evaluation yielded no result).
     * @experimental
     */
    public const FEATURE_FLAG_RESULT_REASON_VALUE_DEFAULT = 'default';

    /**
     * The resolved value was the result of a dynamic evaluation, such as a rule or specific user-targeting.
     * @experimental
     */
    public const FEATURE_FLAG_RESULT_REASON_VALUE_TARGETING_MATCH = 'targeting_match';

    /**
     * The resolved value was the result of pseudorandom assignment.
     * @experimental
     */
    public const FEATURE_FLAG_RESULT_REASON_VALUE_SPLIT = 'split';

    /**
     * The resolved value was retrieved from cache.
     * @experimental
     */
    public const FEATURE_FLAG_RESULT_REASON_VALUE_CACHED = 'cached';

    /**
     * The resolved value was the result of the flag being disabled in the management system.
     * @experimental
     */
    public const FEATURE_FLAG_RESULT_REASON_VALUE_DISABLED = 'disabled';

    /**
     * The reason for the resolved value could not be determined.
     * @experimental
     */
    public const FEATURE_FLAG_RESULT_REASON_VALUE_UNKNOWN = 'unknown';

    /**
     * The resolved value is non-authoritative or possibly out of date
     * @experimental
     */
    public const FEATURE_FLAG_RESULT_REASON_VALUE_STALE = 'stale';

    /**
     * The resolved value was the result of an error.
     * @experimental
     */
    public const FEATURE_FLAG_RESULT_REASON_VALUE_ERROR = 'error';

    /**
     * The evaluated value of the feature flag.
     * With some feature flag providers, feature flag results can be quite large or contain private or sensitive details.
     * Because of this, `feature_flag.result.variant` is often the preferred attribute if it is available.
     *
     * It may be desirable to redact or otherwise limit the size and scope of `feature_flag.result.value` if possible.
     * Because the evaluated flag value is unstructured and may be any type, it is left to the instrumentation author to determine how best to achieve this.
     *
     * @experimental
     */
    public const FEATURE_FLAG_RESULT_VALUE = 'feature_flag.result.value';

    /**
     * A semantic identifier for an evaluated flag value.
     *
     * A semantic identifier, commonly referred to as a variant, provides a means
     * for referring to a value without including the value itself. This can
     * provide additional context for understanding the meaning behind a value.
     * For example, the variant `red` maybe be used for the value `#c05543`.
     * @experimental
     */
    public const FEATURE_FLAG_RESULT_VARIANT = 'feature_flag.result.variant';

    /**
     * The identifier of the [flag set](https://openfeature.dev/specification/glossary/#flag-set) to which the feature flag belongs.
     *
     * @experimental
     */
    public const FEATURE_FLAG_SET_ID = 'feature_flag.set.id';

    /**
     * The version of the ruleset used during the evaluation. This may be any stable value which uniquely identifies the ruleset.
     *
     * @experimental
     */
    public const FEATURE_FLAG_VERSION = 'feature_flag.version';

}
