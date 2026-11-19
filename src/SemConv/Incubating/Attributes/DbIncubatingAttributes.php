<?php

// DO NOT EDIT, this is an Auto-generated file from script/semantic-conventions

declare(strict_types=1);

namespace OpenTelemetry\SemConv\Incubating\Attributes;

/**
 * Semantic attributes and corresponding values for db.
 * @see https://opentelemetry.io/docs/specs/semconv/registry/attributes/db/
 * May contain @experimental Semantic Conventions which may change or be removed in the future.
 */
interface DbIncubatingAttributes
{
    /**
     * Deprecated, use `cassandra.consistency.level` instead.
     *
     * @experimental
     * @deprecated Replaced by `cassandra.consistency.level`.
     */
    public const DB_CASSANDRA_CONSISTENCY_LEVEL = 'db.cassandra.consistency_level';

    /**
     * @experimental
     * @deprecated Replaced by `cassandra.consistency.level`.
     */
    public const DB_CASSANDRA_CONSISTENCY_LEVEL_VALUE_ALL = 'all';

    /**
     * @experimental
     * @deprecated Replaced by `cassandra.consistency.level`.
     */
    public const DB_CASSANDRA_CONSISTENCY_LEVEL_VALUE_EACH_QUORUM = 'each_quorum';

    /**
     * @experimental
     * @deprecated Replaced by `cassandra.consistency.level`.
     */
    public const DB_CASSANDRA_CONSISTENCY_LEVEL_VALUE_QUORUM = 'quorum';

    /**
     * @experimental
     * @deprecated Replaced by `cassandra.consistency.level`.
     */
    public const DB_CASSANDRA_CONSISTENCY_LEVEL_VALUE_LOCAL_QUORUM = 'local_quorum';

    /**
     * @experimental
     * @deprecated Replaced by `cassandra.consistency.level`.
     */
    public const DB_CASSANDRA_CONSISTENCY_LEVEL_VALUE_ONE = 'one';

    /**
     * @experimental
     * @deprecated Replaced by `cassandra.consistency.level`.
     */
    public const DB_CASSANDRA_CONSISTENCY_LEVEL_VALUE_TWO = 'two';

    /**
     * @experimental
     * @deprecated Replaced by `cassandra.consistency.level`.
     */
    public const DB_CASSANDRA_CONSISTENCY_LEVEL_VALUE_THREE = 'three';

    /**
     * @experimental
     * @deprecated Replaced by `cassandra.consistency.level`.
     */
    public const DB_CASSANDRA_CONSISTENCY_LEVEL_VALUE_LOCAL_ONE = 'local_one';

    /**
     * @experimental
     * @deprecated Replaced by `cassandra.consistency.level`.
     */
    public const DB_CASSANDRA_CONSISTENCY_LEVEL_VALUE_ANY = 'any';

    /**
     * @experimental
     * @deprecated Replaced by `cassandra.consistency.level`.
     */
    public const DB_CASSANDRA_CONSISTENCY_LEVEL_VALUE_SERIAL = 'serial';

    /**
     * @experimental
     * @deprecated Replaced by `cassandra.consistency.level`.
     */
    public const DB_CASSANDRA_CONSISTENCY_LEVEL_VALUE_LOCAL_SERIAL = 'local_serial';

    /**
     * Deprecated, use `cassandra.coordinator.dc` instead.
     *
     * @experimental
     * @deprecated Replaced by `cassandra.coordinator.dc`.
     */
    public const DB_CASSANDRA_COORDINATOR_DC = 'db.cassandra.coordinator.dc';

    /**
     * Deprecated, use `cassandra.coordinator.id` instead.
     *
     * @experimental
     * @deprecated Replaced by `cassandra.coordinator.id`.
     */
    public const DB_CASSANDRA_COORDINATOR_ID = 'db.cassandra.coordinator.id';

    /**
     * Deprecated, use `cassandra.query.idempotent` instead.
     *
     * @experimental
     * @deprecated Replaced by `cassandra.query.idempotent`.
     */
    public const DB_CASSANDRA_IDEMPOTENCE = 'db.cassandra.idempotence';

    /**
     * Deprecated, use `cassandra.page.size` instead.
     *
     * @experimental
     * @deprecated Replaced by `cassandra.page.size`.
     */
    public const DB_CASSANDRA_PAGE_SIZE = 'db.cassandra.page_size';

    /**
     * Deprecated, use `cassandra.speculative_execution.count` instead.
     *
     * @experimental
     * @deprecated Replaced by `cassandra.speculative_execution.count`.
     */
    public const DB_CASSANDRA_SPECULATIVE_EXECUTION_COUNT = 'db.cassandra.speculative_execution_count';

    /**
     * Deprecated, use `db.collection.name` instead.
     *
     * @experimental
     * @deprecated Replaced by `db.collection.name`.
     */
    public const DB_CASSANDRA_TABLE = 'db.cassandra.table';

    /**
     * The name of the connection pool. If the connection pool does not have a name, instrumentation SHOULD use `db.namespace`; otherwise `server.address`, with `server.port` appended using `:` if set. When appending a port to an IPv6 address, instrumentation MUST enclose the address in square brackets. If `server.address` is not available, instrumentation SHOULD use `db.system.name`; otherwise the connection pool library name.
     *
     * @experimental
     */
    public const DB_CLIENT_CONNECTION_POOL_NAME = 'db.client.connection.pool.name';

    /**
     * The state of a connection in the pool
     *
     * @experimental
     */
    public const DB_CLIENT_CONNECTION_STATE = 'db.client.connection.state';

    /**
     * @experimental
     */
    public const DB_CLIENT_CONNECTION_STATE_VALUE_IDLE = 'idle';

    /**
     * @experimental
     */
    public const DB_CLIENT_CONNECTION_STATE_VALUE_USED = 'used';

    /**
     * Deprecated, use `db.client.connection.pool.name` instead.
     *
     * @experimental
     * @deprecated Replaced by `db.client.connection.pool.name`.
     */
    public const DB_CLIENT_CONNECTIONS_POOL_NAME = 'db.client.connections.pool.name';

    /**
     * Deprecated, use `db.client.connection.state` instead.
     *
     * @experimental
     * @deprecated Replaced by `db.client.connection.state`.
     */
    public const DB_CLIENT_CONNECTIONS_STATE = 'db.client.connections.state';

    /**
     * @experimental
     * @deprecated Replaced by `db.client.connection.state`.
     */
    public const DB_CLIENT_CONNECTIONS_STATE_VALUE_IDLE = 'idle';

    /**
     * @experimental
     * @deprecated Replaced by `db.client.connection.state`.
     */
    public const DB_CLIENT_CONNECTIONS_STATE_VALUE_USED = 'used';

    /**
     * The name of a collection (table, container) within the database.
     * It is RECOMMENDED to capture the value as provided by the application
     * without attempting to do any case normalization.
     *
     * The collection name SHOULD NOT be extracted from `db.query.text`,
     * when the database system supports query text with multiple collections
     * in non-batch operations.
     *
     * For batch operations, if the individual operations would all have the same
     * `db.collection.name` when executed as non-batch operations,
     * then that collection name SHOULD be used.
     *
     * @stable
     */
    public const DB_COLLECTION_NAME = 'db.collection.name';

    /**
     * Deprecated, use `server.address`, `server.port` attributes instead.
     *
     * @experimental
     * @deprecated Replaced by `server.address` and `server.port`.
     */
    public const DB_CONNECTION_STRING = 'db.connection_string';

    /**
     * Deprecated, use `azure.client.id` instead.
     *
     * @experimental
     * @deprecated Replaced by `azure.client.id`.
     */
    public const DB_COSMOSDB_CLIENT_ID = 'db.cosmosdb.client_id';

    /**
     * Deprecated, use `azure.cosmosdb.connection.mode` instead.
     *
     * @experimental
     * @deprecated Replaced by `azure.cosmosdb.connection.mode`.
     */
    public const DB_COSMOSDB_CONNECTION_MODE = 'db.cosmosdb.connection_mode';

    /**
     * Gateway (HTTP) connection.
     * @experimental
     * @deprecated Replaced by `azure.cosmosdb.connection.mode`.
     */
    public const DB_COSMOSDB_CONNECTION_MODE_VALUE_GATEWAY = 'gateway';

    /**
     * Direct connection.
     * @experimental
     * @deprecated Replaced by `azure.cosmosdb.connection.mode`.
     */
    public const DB_COSMOSDB_CONNECTION_MODE_VALUE_DIRECT = 'direct';

    /**
     * Deprecated, use `cosmosdb.consistency.level` instead.
     *
     * @experimental
     * @deprecated Replaced by `azure.cosmosdb.consistency.level`.
     */
    public const DB_COSMOSDB_CONSISTENCY_LEVEL = 'db.cosmosdb.consistency_level';

    /**
     * @experimental
     * @deprecated Replaced by `azure.cosmosdb.consistency.level`.
     */
    public const DB_COSMOSDB_CONSISTENCY_LEVEL_VALUE_STRONG = 'Strong';

    /**
     * @experimental
     * @deprecated Replaced by `azure.cosmosdb.consistency.level`.
     */
    public const DB_COSMOSDB_CONSISTENCY_LEVEL_VALUE_BOUNDED_STALENESS = 'BoundedStaleness';

    /**
     * @experimental
     * @deprecated Replaced by `azure.cosmosdb.consistency.level`.
     */
    public const DB_COSMOSDB_CONSISTENCY_LEVEL_VALUE_SESSION = 'Session';

    /**
     * @experimental
     * @deprecated Replaced by `azure.cosmosdb.consistency.level`.
     */
    public const DB_COSMOSDB_CONSISTENCY_LEVEL_VALUE_EVENTUAL = 'Eventual';

    /**
     * @experimental
     * @deprecated Replaced by `azure.cosmosdb.consistency.level`.
     */
    public const DB_COSMOSDB_CONSISTENCY_LEVEL_VALUE_CONSISTENT_PREFIX = 'ConsistentPrefix';

    /**
     * Deprecated, use `db.collection.name` instead.
     *
     * @experimental
     * @deprecated Replaced by `db.collection.name`.
     */
    public const DB_COSMOSDB_CONTAINER = 'db.cosmosdb.container';

    /**
     * Deprecated, no replacement at this time.
     *
     * @experimental
     * @deprecated Removed, no replacement at this time.
     */
    public const DB_COSMOSDB_OPERATION_TYPE = 'db.cosmosdb.operation_type';

    /**
     * @experimental
     * @deprecated Removed, no replacement at this time.
     */
    public const DB_COSMOSDB_OPERATION_TYPE_VALUE_BATCH = 'batch';

    /**
     * @experimental
     * @deprecated Removed, no replacement at this time.
     */
    public const DB_COSMOSDB_OPERATION_TYPE_VALUE_CREATE = 'create';

    /**
     * @experimental
     * @deprecated Removed, no replacement at this time.
     */
    public const DB_COSMOSDB_OPERATION_TYPE_VALUE_DELETE = 'delete';

    /**
     * @experimental
     * @deprecated Removed, no replacement at this time.
     */
    public const DB_COSMOSDB_OPERATION_TYPE_VALUE_EXECUTE = 'execute';

    /**
     * @experimental
     * @deprecated Removed, no replacement at this time.
     */
    public const DB_COSMOSDB_OPERATION_TYPE_VALUE_EXECUTE_JAVASCRIPT = 'execute_javascript';

    /**
     * @experimental
     * @deprecated Removed, no replacement at this time.
     */
    public const DB_COSMOSDB_OPERATION_TYPE_VALUE_INVALID = 'invalid';

    /**
     * @experimental
     * @deprecated Removed, no replacement at this time.
     */
    public const DB_COSMOSDB_OPERATION_TYPE_VALUE_HEAD = 'head';

    /**
     * @experimental
     * @deprecated Removed, no replacement at this time.
     */
    public const DB_COSMOSDB_OPERATION_TYPE_VALUE_HEAD_FEED = 'head_feed';

    /**
     * @experimental
     * @deprecated Removed, no replacement at this time.
     */
    public const DB_COSMOSDB_OPERATION_TYPE_VALUE_PATCH = 'patch';

    /**
     * @experimental
     * @deprecated Removed, no replacement at this time.
     */
    public const DB_COSMOSDB_OPERATION_TYPE_VALUE_QUERY = 'query';

    /**
     * @experimental
     * @deprecated Removed, no replacement at this time.
     */
    public const DB_COSMOSDB_OPERATION_TYPE_VALUE_QUERY_PLAN = 'query_plan';

    /**
     * @experimental
     * @deprecated Removed, no replacement at this time.
     */
    public const DB_COSMOSDB_OPERATION_TYPE_VALUE_READ = 'read';

    /**
     * @experimental
     * @deprecated Removed, no replacement at this time.
     */
    public const DB_COSMOSDB_OPERATION_TYPE_VALUE_READ_FEED = 'read_feed';

    /**
     * @experimental
     * @deprecated Removed, no replacement at this time.
     */
    public const DB_COSMOSDB_OPERATION_TYPE_VALUE_REPLACE = 'replace';

    /**
     * @experimental
     * @deprecated Removed, no replacement at this time.
     */
    public const DB_COSMOSDB_OPERATION_TYPE_VALUE_UPSERT = 'upsert';

    /**
     * Deprecated, use `azure.cosmosdb.operation.contacted_regions` instead.
     *
     * @experimental
     * @deprecated Replaced by `azure.cosmosdb.operation.contacted_regions`.
     */
    public const DB_COSMOSDB_REGIONS_CONTACTED = 'db.cosmosdb.regions_contacted';

    /**
     * Deprecated, use `azure.cosmosdb.operation.request_charge` instead.
     *
     * @experimental
     * @deprecated Replaced by `azure.cosmosdb.operation.request_charge`.
     */
    public const DB_COSMOSDB_REQUEST_CHARGE = 'db.cosmosdb.request_charge';

    /**
     * Deprecated, use `azure.cosmosdb.request.body.size` instead.
     *
     * @experimental
     * @deprecated Replaced by `azure.cosmosdb.request.body.size`.
     */
    public const DB_COSMOSDB_REQUEST_CONTENT_LENGTH = 'db.cosmosdb.request_content_length';

    /**
     * Deprecated, use `db.response.status_code` instead.
     *
     * @experimental
     * @deprecated Use `db.response.status_code` instead.
     */
    public const DB_COSMOSDB_STATUS_CODE = 'db.cosmosdb.status_code';

    /**
     * Deprecated, use `azure.cosmosdb.response.sub_status_code` instead.
     *
     * @experimental
     * @deprecated Replaced by `azure.cosmosdb.response.sub_status_code`.
     */
    public const DB_COSMOSDB_SUB_STATUS_CODE = 'db.cosmosdb.sub_status_code';

    /**
     * Deprecated, use `db.namespace` instead.
     *
     * @experimental
     * @deprecated Replaced by `db.namespace`.
     */
    public const DB_ELASTICSEARCH_CLUSTER_NAME = 'db.elasticsearch.cluster.name';

    /**
     * Deprecated, use `elasticsearch.node.name` instead.
     *
     * @experimental
     * @deprecated Replaced by `elasticsearch.node.name`.
     */
    public const DB_ELASTICSEARCH_NODE_NAME = 'db.elasticsearch.node.name';

    /**
     * Deprecated, use `db.operation.parameter` instead.
     *
     * @experimental
     * @deprecated Replaced by `db.operation.parameter`.
     */
    public const DB_ELASTICSEARCH_PATH_PARTS = 'db.elasticsearch.path_parts';

    /**
     * Deprecated, no general replacement at this time. For Elasticsearch, use `db.elasticsearch.node.name` instead.
     *
     * @experimental
     * @deprecated Removed, no general replacement at this time. For Elasticsearch, use `db.elasticsearch.node.name` instead.
     */
    public const DB_INSTANCE_ID = 'db.instance.id';

    /**
     * Removed, no replacement at this time.
     *
     * @experimental
     * @deprecated Removed, no replacement at this time.
     */
    public const DB_JDBC_DRIVER_CLASSNAME = 'db.jdbc.driver_classname';

    /**
     * Deprecated, use `db.collection.name` instead.
     *
     * @experimental
     * @deprecated Replaced by `db.collection.name`.
     */
    public const DB_MONGODB_COLLECTION = 'db.mongodb.collection';

    /**
     * Deprecated, SQL Server instance is now populated as a part of `db.namespace` attribute.
     *
     * @experimental
     * @deprecated Removed, no replacement at this time.
     */
    public const DB_MSSQL_INSTANCE_NAME = 'db.mssql.instance_name';

    /**
     * Deprecated, use `db.namespace` instead.
     *
     * @experimental
     * @deprecated Replaced by `db.namespace`.
     */
    public const DB_NAME = 'db.name';

    /**
     * The name of the database, fully qualified within the server address and port.
     *
     * If a database system has multiple namespace components, they SHOULD be concatenated from the most general to the most specific namespace component, using `|` as a separator between the components. Any missing components (and their associated separators) SHOULD be omitted.
     * Semantic conventions for individual database systems SHOULD document what `db.namespace` means in the context of that system.
     * It is RECOMMENDED to capture the value as provided by the application without attempting to do any case normalization.
     *
     * @stable
     */
    public const DB_NAMESPACE = 'db.namespace';

    /**
     * Deprecated, use `db.operation.name` instead.
     *
     * @experimental
     * @deprecated Replaced by `db.operation.name`.
     */
    public const DB_OPERATION = 'db.operation';

    /**
     * The number of database operations included in a batch operation.
     * Except for empty batch requests described below, a batch operation contains two
     * or more database operations explicitly submitted as separate operations in a single
     * client call, protocol message, or database command.
     *
     * Requests to batch APIs that contain only one operation SHOULD be modeled as single
     * operations, not as batch operations.
     *
     * A database call is not a batch operation solely because one operation accepts
     * multiple operands, such as keys, rows, documents, points, or other data elements,
     * including Redis [`MGET`](https://redis.io/docs/latest/commands/mget/) with
     * multiple keys.
     *
     * In batch APIs that execute the same parameterized operation with parameter sets,
     * each parameter set represents one database operation for determining whether the
     * request is a batch operation. Requests with only one parameter set SHOULD be modeled
     * as single operations, not as batch operations.
     *
     * `db.operation.batch.size` SHOULD be set to the number of operations in the batch.
     * It SHOULD NOT be set for non-batch operations.
     *
     * A request to execute a batch operation with no operations SHOULD also be treated
     * as a batch operation, and `db.operation.batch.size` SHOULD be set to `0`.
     *
     * @stable
     */
    public const DB_OPERATION_BATCH_SIZE = 'db.operation.batch.size';

    /**
     * The name of the operation or command being executed.
     *
     * It is RECOMMENDED to capture the value as provided by the application
     * without attempting to do any case normalization.
     *
     * The operation name SHOULD NOT be extracted from `db.query.text`,
     * when the database system supports query text with multiple operations
     * in non-batch operations.
     *
     * If spaces can occur in the operation name, multiple consecutive spaces
     * SHOULD be normalized to a single space.
     *
     * For batch operations, if the individual operations would all have the same
     * `db.operation.name` when executed as non-batch operations,
     * then that operation name SHOULD be used prepended by `BATCH `.
     * Otherwise, `db.operation.name` SHOULD be `BATCH` or some other database
     * system specific term if more applicable.
     *
     * @stable
     */
    public const DB_OPERATION_NAME = 'db.operation.name';

    /**
     * A database operation parameter, with `<key>` being the parameter name, and the attribute value being a string representation of the parameter value.
     *
     * For example, a client-side maximum number of rows to read from the database
     * MAY be recorded as the `db.operation.parameter.max_rows` attribute.
     *
     * `db.query.text` parameters SHOULD be captured using `db.query.parameter.<key>`
     * instead of `db.operation.parameter.<key>`.
     *
     * @experimental
     */
    public const DB_OPERATION_PARAMETER = 'db.operation.parameter';

    /**
     * A database query parameter, with `<key>` being the parameter name, and the attribute value being a string representation of the parameter value.
     *
     * If a query parameter has no name and instead is referenced only by index,
     * then `<key>` SHOULD be the 0-based index.
     *
     * `db.query.parameter.<key>` SHOULD match
     * up with the parameterized placeholders present in `db.query.text`.
     *
     * It is RECOMMENDED to capture the value as provided by the application
     * without attempting to do any case normalization or sanitization.
     *
     * Instrumentations SHOULD NOT capture `db.query.parameter.<key>` by default
     * since values may contain PII or sensitive details.
     * Application operators are expected to enable specific keys depending
     * on their privacy and security considerations.
     *
     * `db.query.parameter.<key>` SHOULD NOT be captured on batch operations.
     *
     * Examples:
     *
     * - For a query `SELECT * FROM users where username =  %s` with the parameter `"jdoe"`,
     *   the attribute `db.query.parameter.0` SHOULD be set to `"jdoe"`.
     * - For a query `"SELECT * FROM users WHERE username = %(userName)s;` with parameter
     *   `userName = "jdoe"`, the attribute `db.query.parameter.userName` SHOULD be set to `"jdoe"`.
     *
     * @experimental
     */
    public const DB_QUERY_PARAMETER = 'db.query.parameter';

    /**
     * Low cardinality summary of a database query.
     *
     * The query summary describes a class of database queries and is useful
     * as a grouping key, especially when analyzing telemetry for database
     * calls involving complex queries.
     *
     * Summary may be available to the instrumentation through
     * instrumentation hooks or other means. If it is not available, instrumentations
     * that support query parsing SHOULD generate a summary following
     * [Generating query summary](/docs/db/database-spans.md#generating-a-summary-of-the-query)
     * section.
     *
     * For batch operations, if the individual operations would all have the same
     * `db.query.summary` when executed as non-batch operations,
     * then that query summary SHOULD be used prepended by `BATCH `.
     * Otherwise, `db.query.summary` SHOULD be `BATCH` or some other database
     * system specific term if more applicable.
     *
     * @stable
     */
    public const DB_QUERY_SUMMARY = 'db.query.summary';

    /**
     * The database query being executed.
     *
     * For sanitization see [Sanitization of `db.query.text`](/docs/db/database-spans.md#sanitization-of-dbquerytext).
     * For batch operations, if the individual operations would all have the same `db.query.text` when executed as non-batch operations, then that query text SHOULD be used. Otherwise, all of the individual query texts SHOULD be concatenated with separator `; ` or some other database system specific separator if more applicable.
     * Parameterized query text SHOULD NOT be sanitized. Even though parameterized query text can potentially have sensitive data, by using a parameterized query the user is giving a strong signal that any sensitive data will be passed as parameter values, and the benefit to observability of capturing the static part of the query text by default outweighs the risk.
     *
     * @stable
     */
    public const DB_QUERY_TEXT = 'db.query.text';

    /**
     * Deprecated, use `db.namespace` instead.
     *
     * @experimental
     * @deprecated Replaced by `db.namespace` (string).
     */
    public const DB_REDIS_DATABASE_INDEX = 'db.redis.database_index';

    /**
     * Number of rows returned by the operation.
     *
     * @experimental
     */
    public const DB_RESPONSE_RETURNED_ROWS = 'db.response.returned_rows';

    /**
     * Database response status code.
     * The status code returned by the database. Usually it represents an error code, but may also represent partial success, warning, or differentiate between various types of successful outcomes.
     * The value MAY use a standardized string representation of a numeric status code, if one exists, instead of the numeric code. The value MAY be a synthetic status provided by the client library rather than returned by the remote service.
     * Semantic conventions for individual database systems SHOULD document what `db.response.status_code` means in the context of that system.
     *
     * @stable
     */
    public const DB_RESPONSE_STATUS_CODE = 'db.response.status_code';

    /**
     * Deprecated, use `db.collection.name` instead.
     *
     * @experimental
     * @deprecated Replaced by `db.collection.name`, but only if not extracting the value from `db.query.text`.
     */
    public const DB_SQL_TABLE = 'db.sql.table';

    /**
     * The database statement being executed.
     *
     * @experimental
     * @deprecated Replaced by `db.query.text`.
     */
    public const DB_STATEMENT = 'db.statement';

    /**
     * The name of a stored procedure within the database.
     * It is RECOMMENDED to capture the value as provided by the application
     * without attempting to do any case normalization.
     *
     * For batch operations, if the individual operations would all have the same
     * `db.stored_procedure.name` when executed as non-batch operations,
     * then that stored procedure name SHOULD be used.
     *
     * @stable
     */
    public const DB_STORED_PROCEDURE_NAME = 'db.stored_procedure.name';

    /**
     * Deprecated, use `db.system.name` instead.
     *
     * @experimental
     * @deprecated Replaced by `db.system.name`.
     */
    public const DB_SYSTEM = 'db.system';

    /**
     * Some other SQL database. Fallback only. See notes.
     * @experimental
     * @deprecated Replaced by `db.system.name`.
     */
    public const DB_SYSTEM_VALUE_OTHER_SQL = 'other_sql';

    /**
     * Adabas (Adaptable Database System)
     * @experimental
     * @deprecated Replaced by `db.system.name`.
     */
    public const DB_SYSTEM_VALUE_ADABAS = 'adabas';

    /**
     * Deprecated, use `intersystems_cache` instead.
     * @experimental
     * @deprecated Replaced by `intersystems_cache`.
     */
    public const DB_SYSTEM_VALUE_CACHE = 'cache';

    /**
     * InterSystems Caché
     * @experimental
     * @deprecated Replaced by `db.system.name`.
     */
    public const DB_SYSTEM_VALUE_INTERSYSTEMS_CACHE = 'intersystems_cache';

    /**
     * Apache Cassandra
     * @experimental
     * @deprecated Replaced by `db.system.name`.
     */
    public const DB_SYSTEM_VALUE_CASSANDRA = 'cassandra';

    /**
     * ClickHouse
     * @experimental
     * @deprecated Replaced by `db.system.name`.
     */
    public const DB_SYSTEM_VALUE_CLICKHOUSE = 'clickhouse';

    /**
     * Deprecated, use `other_sql` instead.
     * @experimental
     * @deprecated Replaced by `other_sql`.
     */
    public const DB_SYSTEM_VALUE_CLOUDSCAPE = 'cloudscape';

    /**
     * CockroachDB
     * @experimental
     * @deprecated Replaced by `db.system.name`.
     */
    public const DB_SYSTEM_VALUE_COCKROACHDB = 'cockroachdb';

    /**
     * Deprecated, no replacement at this time.
     * @experimental
     * @deprecated Obsoleted.
     */
    public const DB_SYSTEM_VALUE_COLDFUSION = 'coldfusion';

    /**
     * Microsoft Azure Cosmos DB
     * @experimental
     * @deprecated Replaced by `db.system.name`.
     */
    public const DB_SYSTEM_VALUE_COSMOSDB = 'cosmosdb';

    /**
     * Couchbase
     * @experimental
     * @deprecated Replaced by `db.system.name`.
     */
    public const DB_SYSTEM_VALUE_COUCHBASE = 'couchbase';

    /**
     * CouchDB
     * @experimental
     * @deprecated Replaced by `db.system.name`.
     */
    public const DB_SYSTEM_VALUE_COUCHDB = 'couchdb';

    /**
     * IBM Db2
     * @experimental
     * @deprecated Replaced by `db.system.name`.
     */
    public const DB_SYSTEM_VALUE_DB2 = 'db2';

    /**
     * Apache Derby
     * @experimental
     * @deprecated Replaced by `db.system.name`.
     */
    public const DB_SYSTEM_VALUE_DERBY = 'derby';

    /**
     * Amazon DynamoDB
     * @experimental
     * @deprecated Replaced by `db.system.name`.
     */
    public const DB_SYSTEM_VALUE_DYNAMODB = 'dynamodb';

    /**
     * EnterpriseDB
     * @experimental
     * @deprecated Replaced by `db.system.name`.
     */
    public const DB_SYSTEM_VALUE_EDB = 'edb';

    /**
     * Elasticsearch
     * @experimental
     * @deprecated Replaced by `db.system.name`.
     */
    public const DB_SYSTEM_VALUE_ELASTICSEARCH = 'elasticsearch';

    /**
     * FileMaker
     * @experimental
     * @deprecated Replaced by `db.system.name`.
     */
    public const DB_SYSTEM_VALUE_FILEMAKER = 'filemaker';

    /**
     * Firebird
     * @experimental
     * @deprecated Replaced by `db.system.name`.
     */
    public const DB_SYSTEM_VALUE_FIREBIRD = 'firebird';

    /**
     * Deprecated, use `other_sql` instead.
     * @experimental
     * @deprecated Replaced by `other_sql`.
     */
    public const DB_SYSTEM_VALUE_FIRSTSQL = 'firstsql';

    /**
     * Apache Geode
     * @experimental
     * @deprecated Replaced by `db.system.name`.
     */
    public const DB_SYSTEM_VALUE_GEODE = 'geode';

    /**
     * H2
     * @experimental
     * @deprecated Replaced by `db.system.name`.
     */
    public const DB_SYSTEM_VALUE_H2 = 'h2';

    /**
     * SAP HANA
     * @experimental
     * @deprecated Replaced by `db.system.name`.
     */
    public const DB_SYSTEM_VALUE_HANADB = 'hanadb';

    /**
     * Apache HBase
     * @experimental
     * @deprecated Replaced by `db.system.name`.
     */
    public const DB_SYSTEM_VALUE_HBASE = 'hbase';

    /**
     * Apache Hive
     * @experimental
     * @deprecated Replaced by `db.system.name`.
     */
    public const DB_SYSTEM_VALUE_HIVE = 'hive';

    /**
     * HyperSQL DataBase
     * @experimental
     * @deprecated Replaced by `db.system.name`.
     */
    public const DB_SYSTEM_VALUE_HSQLDB = 'hsqldb';

    /**
     * InfluxDB
     * @experimental
     * @deprecated Replaced by `db.system.name`.
     */
    public const DB_SYSTEM_VALUE_INFLUXDB = 'influxdb';

    /**
     * Informix
     * @experimental
     * @deprecated Replaced by `db.system.name`.
     */
    public const DB_SYSTEM_VALUE_INFORMIX = 'informix';

    /**
     * Ingres
     * @experimental
     * @deprecated Replaced by `db.system.name`.
     */
    public const DB_SYSTEM_VALUE_INGRES = 'ingres';

    /**
     * InstantDB
     * @experimental
     * @deprecated Replaced by `db.system.name`.
     */
    public const DB_SYSTEM_VALUE_INSTANTDB = 'instantdb';

    /**
     * InterBase
     * @experimental
     * @deprecated Replaced by `db.system.name`.
     */
    public const DB_SYSTEM_VALUE_INTERBASE = 'interbase';

    /**
     * MariaDB
     * @experimental
     * @deprecated Replaced by `db.system.name`.
     */
    public const DB_SYSTEM_VALUE_MARIADB = 'mariadb';

    /**
     * SAP MaxDB
     * @experimental
     * @deprecated Replaced by `db.system.name`.
     */
    public const DB_SYSTEM_VALUE_MAXDB = 'maxdb';

    /**
     * Memcached
     * @experimental
     * @deprecated Replaced by `db.system.name`.
     */
    public const DB_SYSTEM_VALUE_MEMCACHED = 'memcached';

    /**
     * MongoDB
     * @experimental
     * @deprecated Replaced by `db.system.name`.
     */
    public const DB_SYSTEM_VALUE_MONGODB = 'mongodb';

    /**
     * Microsoft SQL Server
     * @experimental
     * @deprecated Replaced by `db.system.name`.
     */
    public const DB_SYSTEM_VALUE_MSSQL = 'mssql';

    /**
     * Deprecated, Microsoft SQL Server Compact is discontinued.
     * @experimental
     * @deprecated Replaced by `other_sql`.
     */
    public const DB_SYSTEM_VALUE_MSSQLCOMPACT = 'mssqlcompact';

    /**
     * MySQL
     * @experimental
     * @deprecated Replaced by `db.system.name`.
     */
    public const DB_SYSTEM_VALUE_MYSQL = 'mysql';

    /**
     * Neo4j
     * @experimental
     * @deprecated Replaced by `db.system.name`.
     */
    public const DB_SYSTEM_VALUE_NEO4J = 'neo4j';

    /**
     * Netezza
     * @experimental
     * @deprecated Replaced by `db.system.name`.
     */
    public const DB_SYSTEM_VALUE_NETEZZA = 'netezza';

    /**
     * OpenSearch
     * @experimental
     * @deprecated Replaced by `db.system.name`.
     */
    public const DB_SYSTEM_VALUE_OPENSEARCH = 'opensearch';

    /**
     * Oracle Database
     * @experimental
     * @deprecated Replaced by `db.system.name`.
     */
    public const DB_SYSTEM_VALUE_ORACLE = 'oracle';

    /**
     * Pervasive PSQL
     * @experimental
     * @deprecated Replaced by `db.system.name`.
     */
    public const DB_SYSTEM_VALUE_PERVASIVE = 'pervasive';

    /**
     * PointBase
     * @experimental
     * @deprecated Replaced by `db.system.name`.
     */
    public const DB_SYSTEM_VALUE_POINTBASE = 'pointbase';

    /**
     * PostgreSQL
     * @experimental
     * @deprecated Replaced by `db.system.name`.
     */
    public const DB_SYSTEM_VALUE_POSTGRESQL = 'postgresql';

    /**
     * Progress Database
     * @experimental
     * @deprecated Replaced by `db.system.name`.
     */
    public const DB_SYSTEM_VALUE_PROGRESS = 'progress';

    /**
     * Redis
     * @experimental
     * @deprecated Replaced by `db.system.name`.
     */
    public const DB_SYSTEM_VALUE_REDIS = 'redis';

    /**
     * Amazon Redshift
     * @experimental
     * @deprecated Replaced by `db.system.name`.
     */
    public const DB_SYSTEM_VALUE_REDSHIFT = 'redshift';

    /**
     * Cloud Spanner
     * @experimental
     * @deprecated Replaced by `db.system.name`.
     */
    public const DB_SYSTEM_VALUE_SPANNER = 'spanner';

    /**
     * SQLite
     * @experimental
     * @deprecated Replaced by `db.system.name`.
     */
    public const DB_SYSTEM_VALUE_SQLITE = 'sqlite';

    /**
     * Sybase
     * @experimental
     * @deprecated Replaced by `db.system.name`.
     */
    public const DB_SYSTEM_VALUE_SYBASE = 'sybase';

    /**
     * Teradata
     * @experimental
     * @deprecated Replaced by `db.system.name`.
     */
    public const DB_SYSTEM_VALUE_TERADATA = 'teradata';

    /**
     * Trino
     * @experimental
     * @deprecated Replaced by `db.system.name`.
     */
    public const DB_SYSTEM_VALUE_TRINO = 'trino';

    /**
     * Vertica
     * @experimental
     * @deprecated Replaced by `db.system.name`.
     */
    public const DB_SYSTEM_VALUE_VERTICA = 'vertica';

    /**
     * The database management system (DBMS) product as identified by the client instrumentation.
     * The actual DBMS may differ from the one identified by the client. For example, when using PostgreSQL client libraries to connect to a CockroachDB, the `db.system.name` is set to `postgresql` based on the instrumentation's best knowledge.
     *
     * @stable
     */
    public const DB_SYSTEM_NAME = 'db.system.name';

    /**
     * Some other SQL database. Fallback only.
     * @stable
     */
    public const DB_SYSTEM_NAME_VALUE_OTHER_SQL = 'other_sql';

    /**
     * [Adabas (Adaptable Database System)](https://documentation.softwareag.com/?pf=adabas)
     * @stable
     */
    public const DB_SYSTEM_NAME_VALUE_SOFTWAREAG_ADABAS = 'softwareag.adabas';

    /**
     * [Actian Ingres](https://www.actian.com/databases/ingres/)
     * @stable
     */
    public const DB_SYSTEM_NAME_VALUE_ACTIAN_INGRES = 'actian.ingres';

    /**
     * [Amazon DynamoDB](https://aws.amazon.com/pm/dynamodb/)
     * @stable
     */
    public const DB_SYSTEM_NAME_VALUE_AWS_DYNAMODB = 'aws.dynamodb';

    /**
     * [Amazon Redshift](https://aws.amazon.com/redshift/)
     * @stable
     */
    public const DB_SYSTEM_NAME_VALUE_AWS_REDSHIFT = 'aws.redshift';

    /**
     * [Azure Cosmos DB](https://learn.microsoft.com/azure/cosmos-db)
     * @stable
     */
    public const DB_SYSTEM_NAME_VALUE_AZURE_COSMOSDB = 'azure.cosmosdb';

    /**
     * [InterSystems Caché](https://www.intersystems.com/products/cache/)
     * @stable
     */
    public const DB_SYSTEM_NAME_VALUE_INTERSYSTEMS_CACHE = 'intersystems.cache';

    /**
     * [Apache Cassandra](https://cassandra.apache.org/)
     * @stable
     */
    public const DB_SYSTEM_NAME_VALUE_CASSANDRA = 'cassandra';

    /**
     * [ClickHouse](https://clickhouse.com/)
     * @stable
     */
    public const DB_SYSTEM_NAME_VALUE_CLICKHOUSE = 'clickhouse';

    /**
     * [CockroachDB](https://www.cockroachlabs.com/)
     * @stable
     */
    public const DB_SYSTEM_NAME_VALUE_COCKROACHDB = 'cockroachdb';

    /**
     * [Couchbase](https://www.couchbase.com/)
     * @stable
     */
    public const DB_SYSTEM_NAME_VALUE_COUCHBASE = 'couchbase';

    /**
     * [Apache CouchDB](https://couchdb.apache.org/)
     * @stable
     */
    public const DB_SYSTEM_NAME_VALUE_COUCHDB = 'couchdb';

    /**
     * [Apache Derby](https://db.apache.org/derby/)
     * @stable
     */
    public const DB_SYSTEM_NAME_VALUE_DERBY = 'derby';

    /**
     * [Elasticsearch](https://www.elastic.co/elasticsearch)
     * @stable
     */
    public const DB_SYSTEM_NAME_VALUE_ELASTICSEARCH = 'elasticsearch';

    /**
     * [Firebird](https://www.firebirdsql.org/)
     * @stable
     */
    public const DB_SYSTEM_NAME_VALUE_FIREBIRDSQL = 'firebirdsql';

    /**
     * [Google Cloud Spanner](https://cloud.google.com/spanner)
     * @stable
     */
    public const DB_SYSTEM_NAME_VALUE_GCP_SPANNER = 'gcp.spanner';

    /**
     * [Apache Geode](https://geode.apache.org/)
     * @stable
     */
    public const DB_SYSTEM_NAME_VALUE_GEODE = 'geode';

    /**
     * [H2 Database](https://h2database.com/)
     * @stable
     */
    public const DB_SYSTEM_NAME_VALUE_H2DATABASE = 'h2database';

    /**
     * [Apache HBase](https://hbase.apache.org/)
     * @stable
     */
    public const DB_SYSTEM_NAME_VALUE_HBASE = 'hbase';

    /**
     * [Apache Hive](https://hive.apache.org/)
     * @stable
     */
    public const DB_SYSTEM_NAME_VALUE_HIVE = 'hive';

    /**
     * [HyperSQL Database](https://hsqldb.org/)
     * @stable
     */
    public const DB_SYSTEM_NAME_VALUE_HSQLDB = 'hsqldb';

    /**
     * [IBM Db2](https://www.ibm.com/products/db2)
     * @stable
     */
    public const DB_SYSTEM_NAME_VALUE_IBM_DB2 = 'ibm.db2';

    /**
     * [IBM Informix](https://www.ibm.com/products/informix)
     * @stable
     */
    public const DB_SYSTEM_NAME_VALUE_IBM_INFORMIX = 'ibm.informix';

    /**
     * [IBM Netezza](https://www.ibm.com/products/netezza)
     * @stable
     */
    public const DB_SYSTEM_NAME_VALUE_IBM_NETEZZA = 'ibm.netezza';

    /**
     * [InfluxDB](https://www.influxdata.com/)
     * @stable
     */
    public const DB_SYSTEM_NAME_VALUE_INFLUXDB = 'influxdb';

    /**
     * [Instant](https://www.instantdb.com/)
     * @stable
     */
    public const DB_SYSTEM_NAME_VALUE_INSTANTDB = 'instantdb';

    /**
     * [MariaDB](https://mariadb.org/)
     * @stable
     */
    public const DB_SYSTEM_NAME_VALUE_MARIADB = 'mariadb';

    /**
     * [Memcached](https://memcached.org/)
     * @stable
     */
    public const DB_SYSTEM_NAME_VALUE_MEMCACHED = 'memcached';

    /**
     * [MongoDB](https://www.mongodb.com/)
     * @stable
     */
    public const DB_SYSTEM_NAME_VALUE_MONGODB = 'mongodb';

    /**
     * [Microsoft SQL Server](https://www.microsoft.com/sql-server)
     * @stable
     */
    public const DB_SYSTEM_NAME_VALUE_MICROSOFT_SQL_SERVER = 'microsoft.sql_server';

    /**
     * [MySQL](https://www.mysql.com/)
     * @stable
     */
    public const DB_SYSTEM_NAME_VALUE_MYSQL = 'mysql';

    /**
     * [Neo4j](https://neo4j.com/)
     * @stable
     */
    public const DB_SYSTEM_NAME_VALUE_NEO4J = 'neo4j';

    /**
     * [OpenSearch](https://opensearch.org/)
     * @stable
     */
    public const DB_SYSTEM_NAME_VALUE_OPENSEARCH = 'opensearch';

    /**
     * [Oracle Database](https://www.oracle.com/database/)
     * @stable
     */
    public const DB_SYSTEM_NAME_VALUE_ORACLE_DB = 'oracle.db';

    /**
     * [PostgreSQL](https://www.postgresql.org/)
     * @stable
     */
    public const DB_SYSTEM_NAME_VALUE_POSTGRESQL = 'postgresql';

    /**
     * [Redis](https://redis.io/)
     * @stable
     */
    public const DB_SYSTEM_NAME_VALUE_REDIS = 'redis';

    /**
     * [SAP HANA](https://www.sap.com/products/technology-platform/hana/what-is-sap-hana.html)
     * @stable
     */
    public const DB_SYSTEM_NAME_VALUE_SAP_HANA = 'sap.hana';

    /**
     * [SAP MaxDB](https://maxdb.sap.com/)
     * @stable
     */
    public const DB_SYSTEM_NAME_VALUE_SAP_MAXDB = 'sap.maxdb';

    /**
     * [SQLite](https://www.sqlite.org/)
     * @stable
     */
    public const DB_SYSTEM_NAME_VALUE_SQLITE = 'sqlite';

    /**
     * [Teradata](https://www.teradata.com/)
     * @stable
     */
    public const DB_SYSTEM_NAME_VALUE_TERADATA = 'teradata';

    /**
     * [Trino](https://trino.io/)
     * @stable
     */
    public const DB_SYSTEM_NAME_VALUE_TRINO = 'trino';

    /**
     * Deprecated, no replacement at this time.
     *
     * @experimental
     * @deprecated Removed, no replacement at this time.
     */
    public const DB_USER = 'db.user';

}
