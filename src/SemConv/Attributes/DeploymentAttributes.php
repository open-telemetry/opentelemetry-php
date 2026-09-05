<?php

// DO NOT EDIT, this is an Auto-generated file from script/semantic-conventions

declare(strict_types=1);

namespace OpenTelemetry\SemConv\Attributes;

/**
 * Semantic attributes and corresponding values for deployment.
 * @see https://opentelemetry.io/docs/specs/semconv/registry/attributes/deployment/
 */
interface DeploymentAttributes
{
    /**
     * Name of the [deployment environment](https://wikipedia.org/wiki/Deployment_environment) (aka deployment tier).
     *
     * `deployment.environment.name` does not affect the uniqueness constraints defined through
     * the `service.namespace`, `service.name` and `service.instance.id` resource attributes.
     * This implies that resources carrying the following attribute combinations MUST be
     * considered to be identifying the same service:
     *
     * - `service.name=frontend`, `deployment.environment.name=production`
     * - `service.name=frontend`, `deployment.environment.name=staging`.
     *
     * @stable
     */
    public const DEPLOYMENT_ENVIRONMENT_NAME = 'deployment.environment.name';

    /**
     * Production environment
     * @stable
     */
    public const DEPLOYMENT_ENVIRONMENT_NAME_VALUE_PRODUCTION = 'production';

    /**
     * Staging environment
     * @stable
     */
    public const DEPLOYMENT_ENVIRONMENT_NAME_VALUE_STAGING = 'staging';

    /**
     * Testing environment
     * @stable
     */
    public const DEPLOYMENT_ENVIRONMENT_NAME_VALUE_TEST = 'test';

    /**
     * Development environment
     * @stable
     */
    public const DEPLOYMENT_ENVIRONMENT_NAME_VALUE_DEVELOPMENT = 'development';

}
