<?php

// DO NOT EDIT, this is an Auto-generated file from script/semantic-conventions

declare(strict_types=1);

namespace OpenTelemetry\SemConv\Metrics;

/**
 * Metrics for db.
 */
interface DbMetrics
{
    /**
     * Duration of database client operations.
     * Batch operations SHOULD be recorded as a single operation.
     *
     * Instrument: histogram
     * Unit: s
     * @stable
     */
    public const DB_CLIENT_OPERATION_DURATION = 'db.client.operation.duration';

}
