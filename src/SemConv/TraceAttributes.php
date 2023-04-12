<?php

// DO NOT EDIT, this is an Auto-generated file from script/semantic-convention/templates/Attributes.php.j2

declare(strict_types=1);

namespace OpenTelemetry\SemConv;

interface TraceAttributes
{
    /**
     * The URL of the OpenTelemetry schema for these keys and values.
     */
    public const SCHEMA_URL = 'https://opentelemetry.io/schemas/1.19.0';

    /**
     * The type of the exception (its fully-qualified class name, if applicable). The dynamic type of the exception should be preferred over the static type in languages that support it.
     *
     * @example java.net.ConnectException
     * @example OSError
     */
    public const EXCEPTION_TYPE = 'exception.type';

    /**
     * The exception message.
     *
     * @example Division by zero
     * @example Can't convert 'int' object to str implicitly
     */
    public const EXCEPTION_MESSAGE = 'exception.message';

    /**
     * A stacktrace as a string in the natural representation for the language runtime. The representation is to be determined and documented by each language SIG.
     *
     * @example Exception in thread "main" java.lang.RuntimeException: Test exception\n at com.example.GenerateTrace.methodB(GenerateTrace.java:13)\n at com.example.GenerateTrace.methodA(GenerateTrace.java:9)\n at com.example.GenerateTrace.main(GenerateTrace.java:5)
     */
    public const EXCEPTION_STACKTRACE = 'exception.stacktrace';

    /**
     * HTTP request method.
     *
     * @example GET
     * @example POST
     * @example HEAD
     */
    public const HTTP_METHOD = 'http.method';

    /**
     * HTTP response status code.
     *
     * @example 200
     */
    public const HTTP_STATUS_CODE = 'http.status_code';

    /**
     * Kind of HTTP protocol used.
     */
    public const HTTP_FLAVOR = 'http.flavor';

    /**
     * Host identifier of the &quot;URI origin&quot; HTTP request is sent to.
     *
     * Determined by using the first of the following that applies<ul>
     * <li>Host identifier of the request target
     * if it's sent in absolute-form</li>
     * <li>Host identifier of the `Host` header</li>
     * </ul>
     * SHOULD NOT be set if capturing it would require an extra DNS lookup.
     *
     * @example example.com
     */
    public const NET_PEER_NAME = 'net.peer.name';

    /**
     * Port identifier of the &quot;URI origin&quot; HTTP request is sent to.
     *
     * When request target is absolute URI, `net.peer.name` MUST match URI port identifier, otherwise it MUST match `Host` header port identifier.
     *
     * @example 80
     * @example 8080
     * @example 443
     */
    public const NET_PEER_PORT = 'net.peer.port';

    /**
     * The URI scheme identifying the used protocol.
     *
     * @example http
     * @example https
     */
    public const HTTP_SCHEME = 'http.scheme';

    /**
     * The matched route (path template in the format used by the respective server framework). See note below.
     *
     * MUST NOT be populated when this is not supported by the HTTP server framework as the route attribute should have low-cardinality and the URI path can NOT substitute it.
     * SHOULD include the application root if there is one.
     *
     * @example /users/:userID?
     * @example {controller}/{action}/{id?}
     */
    public const HTTP_ROUTE = 'http.route';

    /**
     * Name of the local HTTP server that received the request.
     *
     * Determined by using the first of the following that applies<ul>
     * <li>The primary server name of the matched virtual host. MUST only
     * include host identifier.</li>
     * <li>Host identifier of the request target
     * if it's sent in absolute-form.</li>
     * <li>Host identifier of the `Host` header</li>
     * </ul>
     * SHOULD NOT be set if only IP address is available and capturing name would require a reverse DNS lookup.
     *
     * @example localhost
     */
    public const NET_HOST_NAME = 'net.host.name';

    /**
     * Port of the local HTTP server that received the request.
     *
     * Determined by using the first of the following that applies<ul>
     * <li>Port identifier of the primary server host of the matched virtual host.</li>
     * <li>Port identifier of the request target
     * if it's sent in absolute-form.</li>
     * <li>Port identifier of the `Host` header</li>
     * </ul>
     *
     * @example 8080
     */
    public const NET_HOST_PORT = 'net.host.port';

    /**
     * The name identifies the event.
     *
     * @example click
     * @example exception
     */
    public const EVENT_NAME = 'event.name';

    /**
     * The domain identifies the business context for the events.
     *
     * Events across different domains may have same `event.name`, yet be
     * unrelated events.
     */
    public const EVENT_DOMAIN = 'event.domain';

    /**
     * The unique identifier of the feature flag.
     *
     * @example logo-color
     */
    public const FEATURE_FLAG_KEY = 'feature_flag.key';

    /**
     * The name of the service provider that performs the flag evaluation.
     *
     * @example Flag Manager
     */
    public const FEATURE_FLAG_PROVIDER_NAME = 'feature_flag.provider_name';

    /**
     * SHOULD be a semantic identifier for a value. If one is unavailable, a stringified version of the value can be used.
     *
     * A semantic identifier, commonly referred to as a variant, provides a means
     * for referring to a value without including the value itself. This can
     * provide additional context for understanding the meaning behind a value.
     * For example, the variant `red` maybe be used for the value `#c05543`.A stringified version of the value can be used in situations where a
     * semantic identifier is unavailable. String representation of the value
     * should be determined by the implementer.
     *
     * @example red
     * @example true
     * @example on
     */
    public const FEATURE_FLAG_VARIANT = 'feature_flag.variant';

    /**
     * The full invoked ARN as provided on the `Context` passed to the function (`Lambda-Runtime-Invoked-Function-Arn` header on the `/runtime/invocation/next` applicable).
     *
     * This may be different from `cloud.resource_id` if an alias is involved.
     *
     * @example arn:aws:lambda:us-east-1:123456:function:myfunction:myalias
     */
    public const AWS_LAMBDA_INVOKED_ARN = 'aws.lambda.invoked_arn';

    /**
     * The event_id uniquely identifies the event.
     *
     * @example 123e4567-e89b-12d3-a456-426614174000
     * @example 0001
     */
    public const CLOUDEVENTS_EVENT_ID = 'cloudevents.event_id';

    /**
     * The source identifies the context in which an event happened.
     *
     * @example https://github.com/cloudevents
     * @example /cloudevents/spec/pull/123
     * @example my-service
     */
    public const CLOUDEVENTS_EVENT_SOURCE = 'cloudevents.event_source';

    /**
     * The version of the CloudEvents specification which the event uses.
     *
     * @example 1.0
     */
    public const CLOUDEVENTS_EVENT_SPEC_VERSION = 'cloudevents.event_spec_version';

    /**
     * The event_type contains a value describing the type of event related to the originating occurrence.
     *
     * @example com.github.pull_request.opened
     * @example com.example.object.deleted.v2
     */
    public const CLOUDEVENTS_EVENT_TYPE = 'cloudevents.event_type';

    /**
     * The subject of the event in the context of the event producer (identified by source).
     *
     * @example mynewfile.jpg
     */
    public const CLOUDEVENTS_EVENT_SUBJECT = 'cloudevents.event_subject';

    /**
     * Parent-child Reference type.
     *
     * The causal relationship between a child Span and a parent Span.
     */
    public const OPENTRACING_REF_TYPE = 'opentracing.ref_type';

    /**
     * An identifier for the database management system (DBMS) product being used. See below for a list of well-known identifiers.
     */
    public const DB_SYSTEM = 'db.system';

    /**
     * The connection string used to connect to the database. It is recommended to remove embedded credentials.
     *
     * @example Server=(localdb)\v11.0;Integrated Security=true;
     */
    public const DB_CONNECTION_STRING = 'db.connection_string';

    /**
     * Username for accessing the database.
     *
     * @example readonly_user
     * @example reporting_user
     */
    public const DB_USER = 'db.user';

    /**
     * The fully-qualified class name of the Java Database Connectivity (JDBC) driver used to connect.
     *
     * @example org.postgresql.Driver
     * @example com.microsoft.sqlserver.jdbc.SQLServerDriver
     */
    public const DB_JDBC_DRIVER_CLASSNAME = 'db.jdbc.driver_classname';

    /**
     * This attribute is used to report the name of the database being accessed. For commands that switch the database, this should be set to the target database (even if the command fails).
     *
     * In some SQL databases, the database name to be used is called &quot;schema name&quot;. In case there are multiple layers that could be considered for database name (e.g. Oracle instance name and schema name), the database name to be used is the more specific layer (e.g. Oracle schema name).
     *
     * @example customers
     * @example main
     */
    public const DB_NAME = 'db.name';

    /**
     * The database statement being executed.
     *
     * The value may be sanitized to exclude sensitive information.
     *
     * @example SELECT * FROM wuser_table
     * @example SET mykey "WuValue"
     */
    public const DB_STATEMENT = 'db.statement';

    /**
     * The name of the operation being executed, e.g. the MongoDB command name such as `findAndModify`, or the SQL keyword.
     *
     * When setting this to an SQL keyword, it is not recommended to attempt any client-side parsing of `db.statement` just to get this property, but it should be set if the operation name is provided by the library being instrumented. If the SQL statement has an ambiguous operation, or performs more than one operation, this value may be omitted.
     *
     * @example findAndModify
     * @example HMSET
     * @example SELECT
     */
    public const DB_OPERATION = 'db.operation';

    /**
     * Remote socket peer address: IPv4 or IPv6 for internet protocols, path for local communication, etc.
     *
     * @example 127.0.0.1
     * @example /tmp/mysql.sock
     */
    public const NET_SOCK_PEER_ADDR = 'net.sock.peer.addr';

    /**
     * Remote socket peer port.
     *
     * @example 16456
     */
    public const NET_SOCK_PEER_PORT = 'net.sock.peer.port';

    /**
     * Protocol address family which is used for communication.
     *
     * @example inet6
     * @example bluetooth
     */
    public const NET_SOCK_FAMILY = 'net.sock.family';

    /**
     * Remote socket peer name.
     *
     * @example proxy.example.com
     */
    public const NET_SOCK_PEER_NAME = 'net.sock.peer.name';

    /**
     * Transport protocol used. See note below.
     */
    public const NET_TRANSPORT = 'net.transport';

    /**
     * The Microsoft SQL Server instance name connecting to. This name is used to determine the port of a named instance.
     *
     * If setting a `db.mssql.instance_name`, `net.peer.port` is no longer required (but still recommended if non-standard).
     *
     * @example MSSQLSERVER
     */
    public const DB_MSSQL_INSTANCE_NAME = 'db.mssql.instance_name';

    /**
     * The fetch size used for paging, i.e. how many rows will be returned at once.
     *
     * @example 5000
     */
    public const DB_CASSANDRA_PAGE_SIZE = 'db.cassandra.page_size';

    /**
     * The consistency level of the query. Based on consistency values from CQL.
     */
    public const DB_CASSANDRA_CONSISTENCY_LEVEL = 'db.cassandra.consistency_level';

    /**
     * The name of the primary table that the operation is acting upon, including the keyspace name (if applicable).
     *
     * This mirrors the db.sql.table attribute but references cassandra rather than sql. It is not recommended to attempt any client-side parsing of `db.statement` just to get this property, but it should be set if it is provided by the library being instrumented. If the operation is acting upon an anonymous table, or more than one table, this value MUST NOT be set.
     *
     * @example mytable
     */
    public const DB_CASSANDRA_TABLE = 'db.cassandra.table';

    /**
     * Whether or not the query is idempotent.
     */
    public const DB_CASSANDRA_IDEMPOTENCE = 'db.cassandra.idempotence';

    /**
     * The number of times a query was speculatively executed. Not set or `0` if the query was not executed speculatively.
     *
     * @example 2
     */
    public const DB_CASSANDRA_SPECULATIVE_EXECUTION_COUNT = 'db.cassandra.speculative_execution_count';

    /**
     * The ID of the coordinating node for a query.
     *
     * @example be13faa2-8574-4d71-926d-27f16cf8a7af
     */
    public const DB_CASSANDRA_COORDINATOR_ID = 'db.cassandra.coordinator.id';

    /**
     * The data center of the coordinating node for a query.
     *
     * @example us-west-2
     */
    public const DB_CASSANDRA_COORDINATOR_DC = 'db.cassandra.coordinator.dc';

    /**
     * The index of the database being accessed as used in the `SELECT` command, provided as an integer. To be used instead of the generic `db.name` attribute.
     *
     * @example 1
     * @example 15
     */
    public const DB_REDIS_DATABASE_INDEX = 'db.redis.database_index';

    /**
     * The collection being accessed within the database stated in `db.name`.
     *
     * @example customers
     * @example products
     */
    public const DB_MONGODB_COLLECTION = 'db.mongodb.collection';

    /**
     * The name of the primary table that the operation is acting upon, including the database name (if applicable).
     *
     * It is not recommended to attempt any client-side parsing of `db.statement` just to get this property, but it should be set if it is provided by the library being instrumented. If the operation is acting upon an anonymous table, or more than one table, this value MUST NOT be set.
     *
     * @example public.users
     * @example customers
     */
    public const DB_SQL_TABLE = 'db.sql.table';

    /**
     * Name of the code, either &quot;OK&quot; or &quot;ERROR&quot;. MUST NOT be set if the status code is UNSET.
     */
    public const OTEL_STATUS_CODE = 'otel.status_code';

    /**
     * Description of the Status if it has a value, otherwise not set.
     *
     * @example resource not found
     */
    public const OTEL_STATUS_DESCRIPTION = 'otel.status_description';

    /**
     * Type of the trigger which caused this function invocation.
     *
     * For the server/consumer span on the incoming side,
     * `faas.trigger` MUST be set.Clients invoking FaaS instances usually cannot set `faas.trigger`,
     * since they would typically need to look in the payload to determine
     * the event type. If clients set it, it should be the same as the
     * trigger that corresponding incoming would have (i.e., this has
     * nothing to do with the underlying transport used to make the API
     * call to invoke the lambda, which is often HTTP).
     */
    public const FAAS_TRIGGER = 'faas.trigger';

    /**
     * The invocation ID of the current function invocation.
     *
     * @example af9d5aa4-a685-4c5f-a22b-444f80b3cc28
     */
    public const FAAS_INVOCATION_ID = 'faas.invocation_id';

    /**
     * Cloud provider-specific native identifier of the monitored cloud resource (e.g. an ARN on AWS, a fully qualified resource ID on Azure, a full resource name on GCP).
     *
     * On some cloud providers, it may not be possible to determine the full ID at startup,
     * so it may be necessary to set `cloud.resource_id` as a span attribute instead.The exact value to use for `cloud.resource_id` depends on the cloud provider.
     * The following well-known definitions MUST be used if you set this attribute and they apply:<ul>
     * <li><strong>AWS Lambda:</strong> The function ARN.
     * Take care not to use the &quot;invoked ARN&quot; directly but replace any
     * alias suffix
     * with the resolved function version, as the same runtime instance may be invokable with
     * multiple different aliases.</li>
     * <li><strong>GCP:</strong> The URI of the resource</li>
     * <li><strong>Azure:</strong> The Fully Qualified Resource ID of the invoked function,
     * <em>not</em> the function app, having the form
     * `/subscriptions/<SUBSCIPTION_GUID>/resourceGroups/<RG>/providers/Microsoft.Web/sites/<FUNCAPP>/functions/<FUNC>`.
     * This means that a span attribute MUST be used, as an Azure function app can host multiple functions that would usually share
     * a TracerProvider.</li>
     * </ul>
     *
     * @example arn:aws:lambda:REGION:ACCOUNT_ID:function:my-function
     * @example //run.googleapis.com/projects/PROJECT_ID/locations/LOCATION_ID/services/SERVICE_ID
     * @example /subscriptions/<SUBSCIPTION_GUID>/resourceGroups/<RG>/providers/Microsoft.Web/sites/<FUNCAPP>/functions/<FUNC>
     */
    public const CLOUD_RESOURCE_ID = 'cloud.resource_id';

    /**
     * The name of the source on which the triggering operation was performed. For example, in Cloud Storage or S3 corresponds to the bucket name, and in Cosmos DB to the database name.
     *
     * @example myBucketName
     * @example myDbName
     */
    public const FAAS_DOCUMENT_COLLECTION = 'faas.document.collection';

    /**
     * Describes the type of the operation that was performed on the data.
     */
    public const FAAS_DOCUMENT_OPERATION = 'faas.document.operation';

    /**
     * A string containing the time when the data was accessed in the ISO 8601 format expressed in UTC.
     *
     * @example 2020-01-23T13:47:06Z
     */
    public const FAAS_DOCUMENT_TIME = 'faas.document.time';

    /**
     * The document name/table subjected to the operation. For example, in Cloud Storage or S3 is the name of the file, and in Cosmos DB the table name.
     *
     * @example myFile.txt
     * @example myTableName
     */
    public const FAAS_DOCUMENT_NAME = 'faas.document.name';

    /**
     * The full request target as passed in a HTTP request line or equivalent.
     *
     * @example /path/12314/?q=ddds
     */
    public const HTTP_TARGET = 'http.target';

    /**
     * The IP address of the original client behind all proxies, if known (e.g. from X-Forwarded-For).
     *
     * This is not necessarily the same as `net.sock.peer.addr`, which would
     * identify the network-level peer, which may be a proxy.This attribute should be set when a source of information different
     * from the one used for `net.sock.peer.addr`, is available even if that other
     * source just confirms the same value as `net.sock.peer.addr`.
     * Rationale: For `net.sock.peer.addr`, one typically does not know if it
     * comes from a proxy, reverse proxy, or the actual client. Setting
     * `http.client_ip` when it's the same as `net.sock.peer.addr` means that
     * one is at least somewhat confident that the address is not that of
     * the closest proxy.
     *
     * @example 83.164.160.102
     */
    public const HTTP_CLIENT_IP = 'http.client_ip';

    /**
     * Local socket address. Useful in case of a multi-IP host.
     *
     * @example 192.168.0.1
     */
    public const NET_SOCK_HOST_ADDR = 'net.sock.host.addr';

    /**
     * Local socket port number.
     *
     * @example 35555
     */
    public const NET_SOCK_HOST_PORT = 'net.sock.host.port';

    /**
     * A string identifying the messaging system.
     *
     * @example kafka
     * @example rabbitmq
     * @example rocketmq
     * @example activemq
     * @example AmazonSQS
     */
    public const MESSAGING_SYSTEM = 'messaging.system';

    /**
     * A string identifying the kind of messaging operation as defined in the Operation names section above.
     *
     * If a custom value is used, it MUST be of low cardinality.
     */
    public const MESSAGING_OPERATION = 'messaging.operation';

    /**
     * The number of messages sent, received, or processed in the scope of the batching operation.
     *
     * Instrumentations SHOULD NOT set `messaging.batch.message_count` on spans that operate with a single message. When a messaging client library supports both batch and single-message API for the same operation, instrumentations SHOULD use `messaging.batch.message_count` for batching APIs and SHOULD NOT use it for single-message APIs.
     *
     * @example 1
     * @example 2
     */
    public const MESSAGING_BATCH_MESSAGE_COUNT = 'messaging.batch.message_count';

    /**
     * A value used by the messaging system as an identifier for the message, represented as a string.
     *
     * @example 452a7c7c7c7048c2f887f61572b18fc2
     */
    public const MESSAGING_MESSAGE_ID = 'messaging.message.id';

    /**
     * The conversation ID identifying the conversation to which the message belongs, represented as a string. Sometimes called &quot;Correlation ID&quot;.
     *
     * @example MyConversationId
     */
    public const MESSAGING_MESSAGE_CONVERSATION_ID = 'messaging.message.conversation_id';

    /**
     * The (uncompressed) size of the message payload in bytes. Also use this attribute if it is unknown whether the compressed or uncompressed payload size is reported.
     *
     * @example 2738
     */
    public const MESSAGING_MESSAGE_PAYLOAD_SIZE_BYTES = 'messaging.message.payload_size_bytes';

    /**
     * The compressed size of the message payload in bytes.
     *
     * @example 2048
     */
    public const MESSAGING_MESSAGE_PAYLOAD_COMPRESSED_SIZE_BYTES = 'messaging.message.payload_compressed_size_bytes';

    /**
     * Application layer protocol used. The value SHOULD be normalized to lowercase.
     *
     * @example amqp
     * @example mqtt
     */
    public const NET_APP_PROTOCOL_NAME = 'net.app.protocol.name';

    /**
     * Version of the application layer protocol used. See note below.
     *
     * `net.app.protocol.version` refers to the version of the protocol used and might be different from the protocol client's version. If the HTTP client used has a version of `0.27.2`, but sends HTTP version `1.1`, this attribute should be set to `1.1`.
     *
     * @example 3.1.1
     */
    public const NET_APP_PROTOCOL_VERSION = 'net.app.protocol.version';

    /**
     * The internet connection type currently being used by the host.
     *
     * @example wifi
     */
    public const NET_HOST_CONNECTION_TYPE = 'net.host.connection.type';

    /**
     * This describes more details regarding the connection.type. It may be the type of cell technology connection, but it could be used for describing details about a wifi connection.
     *
     * @example LTE
     */
    public const NET_HOST_CONNECTION_SUBTYPE = 'net.host.connection.subtype';

    /**
     * The name of the mobile carrier.
     *
     * @example sprint
     */
    public const NET_HOST_CARRIER_NAME = 'net.host.carrier.name';

    /**
     * The mobile carrier country code.
     *
     * @example 310
     */
    public const NET_HOST_CARRIER_MCC = 'net.host.carrier.mcc';

    /**
     * The mobile carrier network code.
     *
     * @example 001
     */
    public const NET_HOST_CARRIER_MNC = 'net.host.carrier.mnc';

    /**
     * The ISO 3166-1 alpha-2 2-character country code associated with the mobile carrier network.
     *
     * @example DE
     */
    public const NET_HOST_CARRIER_ICC = 'net.host.carrier.icc';

    /**
     * A string containing the function invocation time in the ISO 8601 format expressed in UTC.
     *
     * @example 2020-01-23T13:47:06Z
     */
    public const FAAS_TIME = 'faas.time';

    /**
     * A string containing the schedule period as Cron Expression.
     *
     * @example 0/5 * * * ? *
     */
    public const FAAS_CRON = 'faas.cron';

    /**
     * A boolean that is true if the serverless function is executed for the first time (aka cold-start).
     */
    public const FAAS_COLDSTART = 'faas.coldstart';

    /**
     * The name of the invoked function.
     *
     * SHOULD be equal to the `faas.name` resource attribute of the invoked function.
     *
     * @example my-function
     */
    public const FAAS_INVOKED_NAME = 'faas.invoked_name';

    /**
     * The cloud provider of the invoked function.
     *
     * SHOULD be equal to the `cloud.provider` resource attribute of the invoked function.
     */
    public const FAAS_INVOKED_PROVIDER = 'faas.invoked_provider';

    /**
     * The cloud region of the invoked function.
     *
     * SHOULD be equal to the `cloud.region` resource attribute of the invoked function.
     *
     * @example eu-central-1
     */
    public const FAAS_INVOKED_REGION = 'faas.invoked_region';

    /**
     * The `service.name` of the remote service. SHOULD be equal to the actual `service.name` resource attribute of the remote service if any.
     *
     * @example AuthTokenCache
     */
    public const PEER_SERVICE = 'peer.service';

    /**
     * Username or client_id extracted from the access token or Authorization header in the inbound request from outside the system.
     *
     * @example username
     */
    public const ENDUSER_ID = 'enduser.id';

    /**
     * Actual/assumed role the client is making the request under extracted from token or application security context.
     *
     * @example admin
     */
    public const ENDUSER_ROLE = 'enduser.role';

    /**
     * Scopes or granted authorities the client currently possesses extracted from token or application security context. The value would come from the scope associated with an OAuth 2.0 Access Token or an attribute value in a SAML 2.0 Assertion.
     *
     * @example read:message, write:files
     */
    public const ENDUSER_SCOPE = 'enduser.scope';

    /**
     * Current &quot;managed&quot; thread ID (as opposed to OS thread ID).
     *
     * @example 42
     */
    public const THREAD_ID = 'thread.id';

    /**
     * Current thread name.
     *
     * @example main
     */
    public const THREAD_NAME = 'thread.name';

    /**
     * The method or function name, or equivalent (usually rightmost part of the code unit's name).
     *
     * @example serveRequest
     */
    public const CODE_FUNCTION = 'code.function';

    /**
     * The &quot;namespace&quot; within which `code.function` is defined. Usually the qualified class or module name, such that `code.namespace` + some separator + `code.function` form a unique identifier for the code unit.
     *
     * @example com.example.MyHttpService
     */
    public const CODE_NAMESPACE = 'code.namespace';

    /**
     * The source code file name that identifies the code unit as uniquely as possible (preferably an absolute file path).
     *
     * @example /usr/local/MyApplication/content_root/app/index.php
     */
    public const CODE_FILEPATH = 'code.filepath';

    /**
     * The line number in `code.filepath` best representing the operation. It SHOULD point within the code unit named in `code.function`.
     *
     * @example 42
     */
    public const CODE_LINENO = 'code.lineno';

    /**
     * The column number in `code.filepath` best representing the operation. It SHOULD point within the code unit named in `code.function`.
     *
     * @example 16
     */
    public const CODE_COLUMN = 'code.column';

    /**
     * The size of the request payload body in bytes. This is the number of bytes transferred excluding headers and is often, but not always, present as the Content-Length header. For requests using transport encoding, this should be the compressed size.
     *
     * @example 3495
     */
    public const HTTP_REQUEST_CONTENT_LENGTH = 'http.request_content_length';

    /**
     * The size of the response payload body in bytes. This is the number of bytes transferred excluding headers and is often, but not always, present as the Content-Length header. For requests using transport encoding, this should be the compressed size.
     *
     * @example 3495
     */
    public const HTTP_RESPONSE_CONTENT_LENGTH = 'http.response_content_length';

    /**
     * Value of the HTTP User-Agent header sent by the client.
     *
     * @example CERN-LineMode/2.15 libwww/2.17b3
     */
    public const USER_AGENT_ORIGINAL = 'user_agent.original';

    /**
     * Full HTTP request URL in the form `scheme://host[:port]/path?query[#fragment]`. Usually the fragment is not transmitted over HTTP, but if it is known, it should be included nevertheless.
     *
     * `http.url` MUST NOT contain credentials passed via URL in form of `https://username:password@www.example.com/`. In such case the attribute's value should be `https://www.example.com/`.
     *
     * @example https://www.foo.bar/search?q=OpenTelemetry#SemConv
     */
    public const HTTP_URL = 'http.url';

    /**
     * The ordinal number of request resending attempt (for any reason, including redirects).
     *
     * The resend count SHOULD be updated each time an HTTP request gets resent by the client, regardless of what was the cause of the resending (e.g. redirection, authorization failure, 503 Server Unavailable, network issues, or any other).
     *
     * @example 3
     */
    public const HTTP_RESEND_COUNT = 'http.resend_count';

    /**
     * The value `aws-api`.
     *
     * @example aws-api
     */
    public const RPC_SYSTEM = 'rpc.system';

    /**
     * The name of the service to which a request is made, as returned by the AWS SDK.
     *
     * This is the logical name of the service from the RPC interface perspective, which can be different from the name of any implementing class. The `code.namespace` attribute may be used to store the latter (despite the attribute name, it may include a class name; e.g., class with method actually executing the call on the server side, RPC client stub class on the client side).
     *
     * @example DynamoDB
     * @example S3
     */
    public const RPC_SERVICE = 'rpc.service';

    /**
     * The name of the operation corresponding to the request, as returned by the AWS SDK.
     *
     * This is the logical name of the method from the RPC interface perspective, which can be different from the name of any implementing method/function. The `code.function` attribute may be used to store the latter (e.g., method actually executing the call on the server side, RPC client stub method on the client side).
     *
     * @example GetItem
     * @example PutItem
     */
    public const RPC_METHOD = 'rpc.method';

    /**
     * The keys in the `RequestItems` object field.
     *
     * @example Users
     * @example Cats
     */
    public const AWS_DYNAMODB_TABLE_NAMES = 'aws.dynamodb.table_names';

    /**
     * The JSON-serialized value of each item in the `ConsumedCapacity` response field.
     *
     * @example { "CapacityUnits": number, "GlobalSecondaryIndexes": { "string" : { "CapacityUnits": number, "ReadCapacityUnits": number, "WriteCapacityUnits": number } }, "LocalSecondaryIndexes": { "string" : { "CapacityUnits": number, "ReadCapacityUnits": number, "WriteCapacityUnits": number } }, "ReadCapacityUnits": number, "Table": { "CapacityUnits": number, "ReadCapacityUnits": number, "WriteCapacityUnits": number }, "TableName": "string", "WriteCapacityUnits": number }
     */
    public const AWS_DYNAMODB_CONSUMED_CAPACITY = 'aws.dynamodb.consumed_capacity';

    /**
     * The JSON-serialized value of the `ItemCollectionMetrics` response field.
     *
     * @example { "string" : [ { "ItemCollectionKey": { "string" : { "B": blob, "BOOL": boolean, "BS": [ blob ], "L": [ "AttributeValue" ], "M": { "string" : "AttributeValue" }, "N": "string", "NS": [ "string" ], "NULL": boolean, "S": "string", "SS": [ "string" ] } }, "SizeEstimateRangeGB": [ number ] } ] }
     */
    public const AWS_DYNAMODB_ITEM_COLLECTION_METRICS = 'aws.dynamodb.item_collection_metrics';

    /**
     * The value of the `ProvisionedThroughput.ReadCapacityUnits` request parameter.
     *
     * @example 1.0
     * @example 2.0
     */
    public const AWS_DYNAMODB_PROVISIONED_READ_CAPACITY = 'aws.dynamodb.provisioned_read_capacity';

    /**
     * The value of the `ProvisionedThroughput.WriteCapacityUnits` request parameter.
     *
     * @example 1.0
     * @example 2.0
     */
    public const AWS_DYNAMODB_PROVISIONED_WRITE_CAPACITY = 'aws.dynamodb.provisioned_write_capacity';

    /**
     * The value of the `ConsistentRead` request parameter.
     */
    public const AWS_DYNAMODB_CONSISTENT_READ = 'aws.dynamodb.consistent_read';

    /**
     * The value of the `ProjectionExpression` request parameter.
     *
     * @example Title
     * @example Title, Price, Color
     * @example Title, Description, RelatedItems, ProductReviews
     */
    public const AWS_DYNAMODB_PROJECTION = 'aws.dynamodb.projection';

    /**
     * The value of the `Limit` request parameter.
     *
     * @example 10
     */
    public const AWS_DYNAMODB_LIMIT = 'aws.dynamodb.limit';

    /**
     * The value of the `AttributesToGet` request parameter.
     *
     * @example lives
     * @example id
     */
    public const AWS_DYNAMODB_ATTRIBUTES_TO_GET = 'aws.dynamodb.attributes_to_get';

    /**
     * The value of the `IndexName` request parameter.
     *
     * @example name_to_group
     */
    public const AWS_DYNAMODB_INDEX_NAME = 'aws.dynamodb.index_name';

    /**
     * The value of the `Select` request parameter.
     *
     * @example ALL_ATTRIBUTES
     * @example COUNT
     */
    public const AWS_DYNAMODB_SELECT = 'aws.dynamodb.select';

    /**
     * The JSON-serialized value of each item of the `GlobalSecondaryIndexes` request field.
     *
     * @example { "IndexName": "string", "KeySchema": [ { "AttributeName": "string", "KeyType": "string" } ], "Projection": { "NonKeyAttributes": [ "string" ], "ProjectionType": "string" }, "ProvisionedThroughput": { "ReadCapacityUnits": number, "WriteCapacityUnits": number } }
     */
    public const AWS_DYNAMODB_GLOBAL_SECONDARY_INDEXES = 'aws.dynamodb.global_secondary_indexes';

    /**
     * The JSON-serialized value of each item of the `LocalSecondaryIndexes` request field.
     *
     * @example { "IndexArn": "string", "IndexName": "string", "IndexSizeBytes": number, "ItemCount": number, "KeySchema": [ { "AttributeName": "string", "KeyType": "string" } ], "Projection": { "NonKeyAttributes": [ "string" ], "ProjectionType": "string" } }
     */
    public const AWS_DYNAMODB_LOCAL_SECONDARY_INDEXES = 'aws.dynamodb.local_secondary_indexes';

    /**
     * The value of the `ExclusiveStartTableName` request parameter.
     *
     * @example Users
     * @example CatsTable
     */
    public const AWS_DYNAMODB_EXCLUSIVE_START_TABLE = 'aws.dynamodb.exclusive_start_table';

    /**
     * The the number of items in the `TableNames` response parameter.
     *
     * @example 20
     */
    public const AWS_DYNAMODB_TABLE_COUNT = 'aws.dynamodb.table_count';

    /**
     * The value of the `ScanIndexForward` request parameter.
     */
    public const AWS_DYNAMODB_SCAN_FORWARD = 'aws.dynamodb.scan_forward';

    /**
     * The value of the `Segment` request parameter.
     *
     * @example 10
     */
    public const AWS_DYNAMODB_SEGMENT = 'aws.dynamodb.segment';

    /**
     * The value of the `TotalSegments` request parameter.
     *
     * @example 100
     */
    public const AWS_DYNAMODB_TOTAL_SEGMENTS = 'aws.dynamodb.total_segments';

    /**
     * The value of the `Count` response parameter.
     *
     * @example 10
     */
    public const AWS_DYNAMODB_COUNT = 'aws.dynamodb.count';

    /**
     * The value of the `ScannedCount` response parameter.
     *
     * @example 50
     */
    public const AWS_DYNAMODB_SCANNED_COUNT = 'aws.dynamodb.scanned_count';

    /**
     * The JSON-serialized value of each item in the `AttributeDefinitions` request field.
     *
     * @example { "AttributeName": "string", "AttributeType": "string" }
     */
    public const AWS_DYNAMODB_ATTRIBUTE_DEFINITIONS = 'aws.dynamodb.attribute_definitions';

    /**
     * The JSON-serialized value of each item in the the `GlobalSecondaryIndexUpdates` request field.
     *
     * @example { "Create": { "IndexName": "string", "KeySchema": [ { "AttributeName": "string", "KeyType": "string" } ], "Projection": { "NonKeyAttributes": [ "string" ], "ProjectionType": "string" }, "ProvisionedThroughput": { "ReadCapacityUnits": number, "WriteCapacityUnits": number } }
     */
    public const AWS_DYNAMODB_GLOBAL_SECONDARY_INDEX_UPDATES = 'aws.dynamodb.global_secondary_index_updates';

    /**
     * The name of the operation being executed.
     *
     * @example findBookById
     */
    public const GRAPHQL_OPERATION_NAME = 'graphql.operation.name';

    /**
     * The type of the operation being executed.
     *
     * @example query
     * @example mutation
     * @example subscription
     */
    public const GRAPHQL_OPERATION_TYPE = 'graphql.operation.type';

    /**
     * The GraphQL document being executed.
     *
     * The value may be sanitized to exclude sensitive information.
     *
     * @example query findBookById { bookById(id: ?) { name } }
     */
    public const GRAPHQL_DOCUMENT = 'graphql.document';

    /**
     * The message destination name.
     *
     * Destination name SHOULD uniquely identify a specific queue, topic or other entity within the broker. If
     * the broker does not have such notion, the destination name SHOULD uniquely identify the broker.
     *
     * @example MyQueue
     * @example MyTopic
     */
    public const MESSAGING_DESTINATION_NAME = 'messaging.destination.name';

    /**
     * The message source name.
     *
     * Source name SHOULD uniquely identify a specific queue, topic, or other entity within the broker. If
     * the broker does not have such notion, the source name SHOULD uniquely identify the broker.
     *
     * @example MyQueue
     * @example MyTopic
     */
    public const MESSAGING_SOURCE_NAME = 'messaging.source.name';

    /**
     * The kind of message destination.
     */
    public const MESSAGING_DESTINATION_KIND = 'messaging.destination.kind';

    /**
     * Low cardinality representation of the messaging destination name.
     *
     * Destination names could be constructed from templates. An example would be a destination name involving a user name or product id. Although the destination name in this case is of high cardinality, the underlying template is of low cardinality and can be effectively used for grouping and aggregation.
     *
     * @example /customers/{customerId}
     */
    public const MESSAGING_DESTINATION_TEMPLATE = 'messaging.destination.template';

    /**
     * A boolean that is true if the message destination is temporary and might not exist anymore after messages are processed.
     */
    public const MESSAGING_DESTINATION_TEMPORARY = 'messaging.destination.temporary';

    /**
     * A boolean that is true if the message destination is anonymous (could be unnamed or have auto-generated name).
     */
    public const MESSAGING_DESTINATION_ANONYMOUS = 'messaging.destination.anonymous';

    /**
     * The kind of message source.
     */
    public const MESSAGING_SOURCE_KIND = 'messaging.source.kind';

    /**
     * Low cardinality representation of the messaging source name.
     *
     * Source names could be constructed from templates. An example would be a source name involving a user name or product id. Although the source name in this case is of high cardinality, the underlying template is of low cardinality and can be effectively used for grouping and aggregation.
     *
     * @example /customers/{customerId}
     */
    public const MESSAGING_SOURCE_TEMPLATE = 'messaging.source.template';

    /**
     * A boolean that is true if the message source is temporary and might not exist anymore after messages are processed.
     */
    public const MESSAGING_SOURCE_TEMPORARY = 'messaging.source.temporary';

    /**
     * A boolean that is true if the message source is anonymous (could be unnamed or have auto-generated name).
     */
    public const MESSAGING_SOURCE_ANONYMOUS = 'messaging.source.anonymous';

    /**
     * The identifier for the consumer receiving a message. For Kafka, set it to `{messaging.kafka.consumer.group} - {messaging.kafka.client_id}`, if both are present, or only `messaging.kafka.consumer.group`. For brokers, such as RabbitMQ and Artemis, set it to the `client_id` of the client consuming the message.
     *
     * @example mygroup - client-6
     */
    public const MESSAGING_CONSUMER_ID = 'messaging.consumer.id';

    /**
     * RabbitMQ message routing key.
     *
     * @example myKey
     */
    public const MESSAGING_RABBITMQ_DESTINATION_ROUTING_KEY = 'messaging.rabbitmq.destination.routing_key';

    /**
     * Message keys in Kafka are used for grouping alike messages to ensure they're processed on the same partition. They differ from `messaging.message.id` in that they're not unique. If the key is `null`, the attribute MUST NOT be set.
     *
     * If the key type is not string, it's string representation has to be supplied for the attribute. If the key has no unambiguous, canonical string form, don't include its value.
     *
     * @example myKey
     */
    public const MESSAGING_KAFKA_MESSAGE_KEY = 'messaging.kafka.message.key';

    /**
     * Name of the Kafka Consumer Group that is handling the message. Only applies to consumers, not producers.
     *
     * @example my-group
     */
    public const MESSAGING_KAFKA_CONSUMER_GROUP = 'messaging.kafka.consumer.group';

    /**
     * Client Id for the Consumer or Producer that is handling the message.
     *
     * @example client-5
     */
    public const MESSAGING_KAFKA_CLIENT_ID = 'messaging.kafka.client_id';

    /**
     * Partition the message is sent to.
     *
     * @example 2
     */
    public const MESSAGING_KAFKA_DESTINATION_PARTITION = 'messaging.kafka.destination.partition';

    /**
     * Partition the message is received from.
     *
     * @example 2
     */
    public const MESSAGING_KAFKA_SOURCE_PARTITION = 'messaging.kafka.source.partition';

    /**
     * The offset of a record in the corresponding Kafka partition.
     *
     * @example 42
     */
    public const MESSAGING_KAFKA_MESSAGE_OFFSET = 'messaging.kafka.message.offset';

    /**
     * A boolean that is true if the message is a tombstone.
     */
    public const MESSAGING_KAFKA_MESSAGE_TOMBSTONE = 'messaging.kafka.message.tombstone';

    /**
     * Namespace of RocketMQ resources, resources in different namespaces are individual.
     *
     * @example myNamespace
     */
    public const MESSAGING_ROCKETMQ_NAMESPACE = 'messaging.rocketmq.namespace';

    /**
     * Name of the RocketMQ producer/consumer group that is handling the message. The client type is identified by the SpanKind.
     *
     * @example myConsumerGroup
     */
    public const MESSAGING_ROCKETMQ_CLIENT_GROUP = 'messaging.rocketmq.client_group';

    /**
     * The unique identifier for each client.
     *
     * @example myhost@8742@s8083jm
     */
    public const MESSAGING_ROCKETMQ_CLIENT_ID = 'messaging.rocketmq.client_id';

    /**
     * The timestamp in milliseconds that the delay message is expected to be delivered to consumer.
     *
     * @example 1665987217045
     */
    public const MESSAGING_ROCKETMQ_MESSAGE_DELIVERY_TIMESTAMP = 'messaging.rocketmq.message.delivery_timestamp';

    /**
     * The delay time level for delay message, which determines the message delay time.
     *
     * @example 3
     */
    public const MESSAGING_ROCKETMQ_MESSAGE_DELAY_TIME_LEVEL = 'messaging.rocketmq.message.delay_time_level';

    /**
     * It is essential for FIFO message. Messages that belong to the same message group are always processed one by one within the same consumer group.
     *
     * @example myMessageGroup
     */
    public const MESSAGING_ROCKETMQ_MESSAGE_GROUP = 'messaging.rocketmq.message.group';

    /**
     * Type of message.
     */
    public const MESSAGING_ROCKETMQ_MESSAGE_TYPE = 'messaging.rocketmq.message.type';

    /**
     * The secondary classifier of message besides topic.
     *
     * @example tagA
     */
    public const MESSAGING_ROCKETMQ_MESSAGE_TAG = 'messaging.rocketmq.message.tag';

    /**
     * Key(s) of message, another way to mark message besides message id.
     *
     * @example keyA
     * @example keyB
     */
    public const MESSAGING_ROCKETMQ_MESSAGE_KEYS = 'messaging.rocketmq.message.keys';

    /**
     * Model of message consumption. This only applies to consumer spans.
     */
    public const MESSAGING_ROCKETMQ_CONSUMPTION_MODEL = 'messaging.rocketmq.consumption_model';

    /**
     * The numeric status code of the gRPC request.
     */
    public const RPC_GRPC_STATUS_CODE = 'rpc.grpc.status_code';

    /**
     * Protocol version as in `jsonrpc` property of request/response. Since JSON-RPC 1.0 does not specify this, the value can be omitted.
     *
     * @example 2.0
     * @example 1.0
     */
    public const RPC_JSONRPC_VERSION = 'rpc.jsonrpc.version';

    /**
     * `id` property of request or response. Since protocol allows id to be int, string, `null` or missing (for notifications), value is expected to be cast to string for simplicity. Use empty string in case of `null` value. Omit entirely if this is a notification.
     *
     * @example 10
     * @example request-7
     */
    public const RPC_JSONRPC_REQUEST_ID = 'rpc.jsonrpc.request_id';

    /**
     * `error.code` property of response if it is an error response.
     *
     * @example -32700
     * @example 100
     */
    public const RPC_JSONRPC_ERROR_CODE = 'rpc.jsonrpc.error_code';

    /**
     * `error.message` property of response if it is an error response.
     *
     * @example Parse error
     * @example User already exists
     */
    public const RPC_JSONRPC_ERROR_MESSAGE = 'rpc.jsonrpc.error_message';

    /**
     * Whether this is a received or sent message.
     */
    public const MESSAGE_TYPE = 'message.type';

    /**
     * MUST be calculated as two different counters starting from `1` one for sent messages and one for received message.
     *
     * This way we guarantee that the values will be consistent between different implementations.
     */
    public const MESSAGE_ID = 'message.id';

    /**
     * Compressed size of the message in bytes.
     */
    public const MESSAGE_COMPRESSED_SIZE = 'message.compressed_size';

    /**
     * Uncompressed size of the message in bytes.
     */
    public const MESSAGE_UNCOMPRESSED_SIZE = 'message.uncompressed_size';

    /**
     * The error codes of the Connect request. Error codes are always string values.
     */
    public const RPC_CONNECT_RPC_ERROR_CODE = 'rpc.connect_rpc.error_code';

    /**
     * SHOULD be set to true if the exception event is recorded at a point where it is known that the exception is escaping the scope of the span.
     *
     * An exception is considered to have escaped (or left) the scope of a span,
     * if that span is ended while the exception is still logically &quot;in flight&quot;.
     * This may be actually &quot;in flight&quot; in some languages (e.g. if the exception
     * is passed to a Context manager's `__exit__` method in Python) but will
     * usually be caught at the point of recording the exception in most languages.It is usually not possible to determine at the point where an exception is thrown
     * whether it will escape the scope of a span.
     * However, it is trivial to know that an exception
     * will escape, if one checks for an active exception just before ending the span,
     * as done in the example above.It follows that an exception may still escape the scope of the span
     * even if the `exception.escaped` attribute was not set or set to false,
     * since the event might have been recorded at a time where it was not
     * clear whether the exception will escape.
     */
    public const EXCEPTION_ESCAPED = 'exception.escaped';
}
