<?php

// DO NOT EDIT, this is an Auto-generated file from script/semantic-conventions

declare(strict_types=1);

namespace OpenTelemetry\SemConv\Incubating\Attributes;

/**
 * Semantic attributes and corresponding values for nfs.
 * @see https://opentelemetry.io/docs/specs/semconv/registry/attributes/nfs/
 * May contain @experimental Semantic Conventions which may change or be removed in the future.
 */
interface NfsIncubatingAttributes
{
    /**
     * NFSv4+ operation name.
     *
     * @experimental
     */
    public const NFS_OPERATION_NAME = 'nfs.operation.name';

    /**
     * Linux: one of "hit" (NFSD_STATS_RC_HITS), "miss" (NFSD_STATS_RC_MISSES), or "nocache" (NFSD_STATS_RC_NOCACHE -- uncacheable)
     *
     * @experimental
     */
    public const NFS_SERVER_REPCACHE_STATUS = 'nfs.server.repcache.status';

}
