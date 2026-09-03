<?php

// DO NOT EDIT, this is an Auto-generated file from script/semantic-conventions

declare(strict_types=1);

namespace OpenTelemetry\SemConv\Incubating\Attributes;

/**
 * Semantic attributes and corresponding values for oracle_cloud.
 * @see https://opentelemetry.io/docs/specs/semconv/registry/attributes/oracle_cloud/
 * May contain @experimental Semantic Conventions which may change or be removed in the future.
 */
interface OracleCloudIncubatingAttributes
{
    /**
     * The OCI realm identifier that indicates the isolated partition in which the tenancy and its resources reside.
     *
     * See [OCI documentation on realms](https://docs.oracle.com/iaas/Content/General/Concepts/regions.htm)
     *
     * @experimental
     */
    public const ORACLE_CLOUD_REALM = 'oracle_cloud.realm';

}
