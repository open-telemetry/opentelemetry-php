<?php

// DO NOT EDIT, this is an Auto-generated file from script/semantic-conventions

declare(strict_types=1);

namespace OpenTelemetry\SemConv\Incubating\Attributes;

/**
 * Semantic attributes and corresponding values for user.
 * @see https://opentelemetry.io/docs/specs/semconv/registry/attributes/user/
 * May contain @experimental Semantic Conventions which may change or be removed in the future.
 */
interface UserIncubatingAttributes
{
    /**
     * User email address.
     *
     * @experimental
     */
    public const USER_EMAIL = 'user.email';

    /**
     * User's full name
     *
     * @experimental
     */
    public const USER_FULL_NAME = 'user.full_name';

    /**
     * Unique user hash to correlate information for a user in anonymized form.
     *
     * Useful if `user.id` or `user.name` contain confidential information and cannot be used.
     *
     * @experimental
     */
    public const USER_HASH = 'user.hash';

    /**
     * Unique identifier of the user.
     *
     * @experimental
     */
    public const USER_ID = 'user.id';

    /**
     * Short name or login/username of the user.
     *
     * @experimental
     */
    public const USER_NAME = 'user.name';

    /**
     * Array of user roles at the time of the event.
     *
     * @experimental
     */
    public const USER_ROLES = 'user.roles';

}
