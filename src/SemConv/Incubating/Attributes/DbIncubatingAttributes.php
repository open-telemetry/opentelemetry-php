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
     * The name of the connection pool; unique within the instrumented application. In case the connection pool implementation doesn't provide a name, instrumentation SHOULD use a combination of parameters that would make the name unique, for example, combining attributes `server.address`, `server.port`, and `db.namespace`, formatted as `server.address:server.port/db.namespace`. Instrumentations that generate connection pool name following different patterns SHOULD document it.
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
     * The name of a collection (table, container) within the database.
     * It is RECOMMENDED to capture the value as provided by the application
     * without attempting to do any case normalization.
     *
     * The collection name SHOULD NOT be extracted from `db.query.text`,
     * when the database system supports query text with multiple collections
     * in non-batch operations.
     *
     * For batch operations, if the individual operations are known to have the same
     * collection name then that collection name SHOULD be used.
     *
     * @stable
     */
    public const DB_COLLECTION_NAME = 'db.collection.name';

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
     * The number of queries included in a batch operation.
     * Operations are only considered batches when they contain two or more operations, and so `db.operation.batch.size` SHOULD never be `1`.
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
     * For batch operations, if the individual operations are known to have the same operation name
     * then that operation name SHOULD be used prepended by `BATCH `,
     * otherwise `db.operation.name` SHOULD be `BATCH` or some other database
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
     * without attempting to do any case normalization.
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
     * @stable
     */
    public const DB_QUERY_SUMMARY = 'db.query.summary';

    /**
     * The database query being executed.
     *
     * For sanitization see [Sanitization of `db.query.text`](/docs/db/database-spans.md#sanitization-of-dbquerytext).
     * For batch operations, if the individual operations are known to have the same query text then that query text SHOULD be used, otherwise all of the individual query texts SHOULD be concatenated with separator `; ` or some other database system specific separator if more applicable.
     * Parameterized query text SHOULD NOT be sanitized. Even though parameterized query text can potentially have sensitive data, by using a parameterized query the user is giving a strong signal that any sensitive data will be passed as parameter values, and the benefit to observability of capturing the static part of the query text by default outweighs the risk.
     *
     * @stable
     */
    public const DB_QUERY_TEXT = 'db.query.text';

    /**
     * Number of rows returned by the operation.
     *
     * @experimental
     */
    public const DB_RESPONSE_RETURNED_ROWS = 'db.response.returned_rows';

    /**
     * Database response status code.
     * The status code returned by the database. Usually it represents an error code, but may also represent partial success, warning, or differentiate between various types of successful outcomes.
     * Semantic conventions for individual database systems SHOULD document what `db.response.status_code` means in the context of that system.
     *
     * @stable
     */
    public const DB_RESPONSE_STATUS_CODE = 'db.response.status_code';

    /**
     * The name of a stored procedure within the database.
     * It is RECOMMENDED to capture the value as provided by the application
     * without attempting to do any case normalization.
     *
     * For batch operations, if the individual operations are known to have the same
     * stored procedure name then that stored procedure name SHOULD be used.
     *
     * @stable
     */
    public const DB_STORED_PROCEDURE_NAME = 'db.stored_procedure.name';

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
     * [IBM Db2](https://www.ibm.com/db2)
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

}
