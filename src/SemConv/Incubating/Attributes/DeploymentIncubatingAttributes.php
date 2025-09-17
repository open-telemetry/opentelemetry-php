<?php

// DO NOT EDIT, this is an Auto-generated file from script/semantic-conventions

declare(strict_types=1);

namespace OpenTelemetry\SemConv\Incubating\Attributes;

/**
 * Semantic attributes and corresponding values for deployment.
 * @see https://opentelemetry.io/docs/specs/semconv/registry/attributes/deployment/
 * May contain @experimental Semantic Conventions which may change or be removed in the future.
 */
interface DeploymentIncubatingAttributes
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
     * @experimental
     */
    public const DEPLOYMENT_ENVIRONMENT_NAME = 'deployment.environment.name';

    /**
     * The id of the deployment.
     *
     * @experimental
     */
    public const DEPLOYMENT_ID = 'deployment.id';

    /**
     * The name of the deployment.
     *
     * @experimental
     */
    public const DEPLOYMENT_NAME = 'deployment.name';

    /**
     * The status of the deployment.
     *
     * @experimental
     */
    public const DEPLOYMENT_STATUS = 'deployment.status';

    /**
     * failed
     * @experimental
     */
    public const DEPLOYMENT_STATUS_VALUE_FAILED = 'failed';

    /**
     * succeeded
     * @experimental
     */
    public const DEPLOYMENT_STATUS_VALUE_SUCCEEDED = 'succeeded';

}
