<?php

// DO NOT EDIT, this is an Auto-generated file from script/semantic-conventions

declare(strict_types=1);

namespace OpenTelemetry\SemConv\Incubating\Metrics;

/**
 * Metrics for db.
 * May contain @experimental Semantic Conventions which may change or be removed in the future.
 */
interface DbIncubatingMetrics
{
    /**
     * The number of connections that are currently in state described by the `state` attribute.
     *
     * Instrument: updowncounter
     * Unit: {connection}
     * @experimental
     */
    public const DB_CLIENT_CONNECTION_COUNT = 'db.client.connection.count';

    /**
     * The time it took to create a new connection.
     *
     * Instrument: histogram
     * Unit: s
     * @experimental
     */
    public const DB_CLIENT_CONNECTION_CREATE_TIME = 'db.client.connection.create_time';

    /**
     * The maximum number of idle open connections allowed.
     *
     * Instrument: updowncounter
     * Unit: {connection}
     * @experimental
     */
    public const DB_CLIENT_CONNECTION_IDLE_MAX = 'db.client.connection.idle.max';

    /**
     * The minimum number of idle open connections allowed.
     *
     * Instrument: updowncounter
     * Unit: {connection}
     * @experimental
     */
    public const DB_CLIENT_CONNECTION_IDLE_MIN = 'db.client.connection.idle.min';

    /**
     * The maximum number of open connections allowed.
     *
     * Instrument: updowncounter
     * Unit: {connection}
     * @experimental
     */
    public const DB_CLIENT_CONNECTION_MAX = 'db.client.connection.max';

    /**
     * The number of current pending requests for an open connection.
     *
     * Instrument: updowncounter
     * Unit: {request}
     * @experimental
     */
    public const DB_CLIENT_CONNECTION_PENDING_REQUESTS = 'db.client.connection.pending_requests';

    /**
     * The number of connection timeouts that have occurred trying to obtain a connection from the pool.
     *
     * Instrument: counter
     * Unit: {timeout}
     * @experimental
     */
    public const DB_CLIENT_CONNECTION_TIMEOUTS = 'db.client.connection.timeouts';

    /**
     * The time between borrowing a connection and returning it to the pool.
     *
     * Instrument: histogram
     * Unit: s
     * @experimental
     */
    public const DB_CLIENT_CONNECTION_USE_TIME = 'db.client.connection.use_time';

    /**
     * The time it took to obtain an open connection from the pool.
     *
     * Instrument: histogram
     * Unit: s
     * @experimental
     */
    public const DB_CLIENT_CONNECTION_WAIT_TIME = 'db.client.connection.wait_time';

    /**
     * Deprecated, use `db.client.connection.create_time` instead. Note: the unit also changed from `ms` to `s`.
     *
     * Instrument: histogram
     * Unit: ms
     * @experimental
     */
    public const DB_CLIENT_CONNECTIONS_CREATE_TIME = 'db.client.connections.create_time';

    /**
     * Deprecated, use `db.client.connection.idle.max` instead.
     *
     * Instrument: updowncounter
     * Unit: {connection}
     * @experimental
     */
    public const DB_CLIENT_CONNECTIONS_IDLE_MAX = 'db.client.connections.idle.max';

    /**
     * Deprecated, use `db.client.connection.idle.min` instead.
     *
     * Instrument: updowncounter
     * Unit: {connection}
     * @experimental
     */
    public const DB_CLIENT_CONNECTIONS_IDLE_MIN = 'db.client.connections.idle.min';

    /**
     * Deprecated, use `db.client.connection.max` instead.
     *
     * Instrument: updowncounter
     * Unit: {connection}
     * @experimental
     */
    public const DB_CLIENT_CONNECTIONS_MAX = 'db.client.connections.max';

    /**
     * Deprecated, use `db.client.connection.pending_requests` instead.
     *
     * Instrument: updowncounter
     * Unit: {request}
     * @experimental
     */
    public const DB_CLIENT_CONNECTIONS_PENDING_REQUESTS = 'db.client.connections.pending_requests';

    /**
     * Deprecated, use `db.client.connection.timeouts` instead.
     *
     * Instrument: counter
     * Unit: {timeout}
     * @experimental
     */
    public const DB_CLIENT_CONNECTIONS_TIMEOUTS = 'db.client.connections.timeouts';

    /**
     * Deprecated, use `db.client.connection.count` instead.
     *
     * Instrument: updowncounter
     * Unit: {connection}
     * @experimental
     */
    public const DB_CLIENT_CONNECTIONS_USAGE = 'db.client.connections.usage';

    /**
     * Deprecated, use `db.client.connection.use_time` instead. Note: the unit also changed from `ms` to `s`.
     *
     * Instrument: histogram
     * Unit: ms
     * @experimental
     */
    public const DB_CLIENT_CONNECTIONS_USE_TIME = 'db.client.connections.use_time';

    /**
     * Deprecated, use `db.client.connection.wait_time` instead. Note: the unit also changed from `ms` to `s`.
     *
     * Instrument: histogram
     * Unit: ms
     * @experimental
     */
    public const DB_CLIENT_CONNECTIONS_WAIT_TIME = 'db.client.connections.wait_time';

    /**
     * Deprecated, use `azure.cosmosdb.client.active_instance.count` instead.
     *
     * Instrument: updowncounter
     * Unit: {instance}
     * @experimental
     */
    public const DB_CLIENT_COSMOSDB_ACTIVE_INSTANCE_COUNT = 'db.client.cosmosdb.active_instance.count';

    /**
     * Deprecated, use `azure.cosmosdb.client.operation.request_charge` instead.
     *
     * Instrument: histogram
     * Unit: {request_unit}
     * @experimental
     */
    public const DB_CLIENT_COSMOSDB_OPERATION_REQUEST_CHARGE = 'db.client.cosmosdb.operation.request_charge';

    /**
     * Duration of database client operations.
     * Batch operations SHOULD be recorded as a single operation.
     *
     * Instrument: histogram
     * Unit: s
     * @stable
     */
    public const DB_CLIENT_OPERATION_DURATION = 'db.client.operation.duration';

    /**
     * The actual number of records returned by the database operation.
     *
     * Instrument: histogram
     * Unit: {row}
     * @experimental
     */
    public const DB_CLIENT_RESPONSE_RETURNED_ROWS = 'db.client.response.returned_rows';

}
