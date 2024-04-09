<?php

// DO NOT EDIT, this is an Auto-generated file from script/semantic-convention/templates/Attributes.php.j2

declare(strict_types=1);

namespace OpenTelemetry\SemConv;

interface TraceAttributes
{
    /**
     * The URL of the OpenTelemetry schema for these keys and values.
     */
    public const SCHEMA_URL = 'https://opentelemetry.io/schemas/1.25.0';

    /**
     * Uniquely identifies the framework API revision offered by a version (`os.version`) of the android operating system. More information can be found here.
     *
     * @example 33
     * @example 32
     */
    public const ANDROID_OS_API_LEVEL = 'android.os.api_level';

    /**
     * This attribute represents the state the application has transitioned into at the occurrence of the event.
     *
     * The Android lifecycle states are defined in Activity lifecycle callbacks, and from which the `OS identifiers` are derived.
     */
    public const ANDROID_STATE = 'android.state';

    /**
     * Full type name of the `IExceptionHandler` implementation that handled the exception.
     *
     * @example Contoso.MyHandler
     */
    public const ASPNETCORE_DIAGNOSTICS_HANDLER_TYPE = 'aspnetcore.diagnostics.handler.type';

    /**
     * Rate limiting policy name.
     *
     * @example fixed
     * @example sliding
     * @example token
     */
    public const ASPNETCORE_RATE_LIMITING_POLICY = 'aspnetcore.rate_limiting.policy';

    /**
     * Rate-limiting result, shows whether the lease was acquired or contains a rejection reason.
     *
     * @example acquired
     * @example request_canceled
     */
    public const ASPNETCORE_RATE_LIMITING_RESULT = 'aspnetcore.rate_limiting.result';

    /**
     * Flag indicating if request was handled by the application pipeline.
     *
     * @example True
     */
    public const ASPNETCORE_REQUEST_IS_UNHANDLED = 'aspnetcore.request.is_unhandled';

    /**
     * A value that indicates whether the matched route is a fallback route.
     *
     * @example True
     */
    public const ASPNETCORE_ROUTING_IS_FALLBACK = 'aspnetcore.routing.is_fallback';

    /**
     * The JSON-serialized value of each item in the `AttributeDefinitions` request field.
     *
     * @example { "AttributeName": "string", "AttributeType": "string" }
     */
    public const AWS_DYNAMODB_ATTRIBUTE_DEFINITIONS = 'aws.dynamodb.attribute_definitions';

    /**
     * The value of the `AttributesToGet` request parameter.
     *
     * @example lives
     * @example id
     */
    public const AWS_DYNAMODB_ATTRIBUTES_TO_GET = 'aws.dynamodb.attributes_to_get';

    /**
     * The value of the `ConsistentRead` request parameter.
     */
    public const AWS_DYNAMODB_CONSISTENT_READ = 'aws.dynamodb.consistent_read';

    /**
     * The JSON-serialized value of each item in the `ConsumedCapacity` response field.
     *
     * @example { "CapacityUnits": number, "GlobalSecondaryIndexes": { "string" : { "CapacityUnits": number, "ReadCapacityUnits": number, "WriteCapacityUnits": number } }, "LocalSecondaryIndexes": { "string" : { "CapacityUnits": number, "ReadCapacityUnits": number, "WriteCapacityUnits": number } }, "ReadCapacityUnits": number, "Table": { "CapacityUnits": number, "ReadCapacityUnits": number, "WriteCapacityUnits": number }, "TableName": "string", "WriteCapacityUnits": number }
     */
    public const AWS_DYNAMODB_CONSUMED_CAPACITY = 'aws.dynamodb.consumed_capacity';

    /**
     * The value of the `Count` response parameter.
     *
     * @example 10
     */
    public const AWS_DYNAMODB_COUNT = 'aws.dynamodb.count';

    /**
     * The value of the `ExclusiveStartTableName` request parameter.
     *
     * @example Users
     * @example CatsTable
     */
    public const AWS_DYNAMODB_EXCLUSIVE_START_TABLE = 'aws.dynamodb.exclusive_start_table';

    /**
     * The JSON-serialized value of each item in the `GlobalSecondaryIndexUpdates` request field.
     *
     * @example { "Create": { "IndexName": "string", "KeySchema": [ { "AttributeName": "string", "KeyType": "string" } ], "Projection": { "NonKeyAttributes": [ "string" ], "ProjectionType": "string" }, "ProvisionedThroughput": { "ReadCapacityUnits": number, "WriteCapacityUnits": number } }
     */
    public const AWS_DYNAMODB_GLOBAL_SECONDARY_INDEX_UPDATES = 'aws.dynamodb.global_secondary_index_updates';

    /**
     * The JSON-serialized value of each item of the `GlobalSecondaryIndexes` request field.
     *
     * @example { "IndexName": "string", "KeySchema": [ { "AttributeName": "string", "KeyType": "string" } ], "Projection": { "NonKeyAttributes": [ "string" ], "ProjectionType": "string" }, "ProvisionedThroughput": { "ReadCapacityUnits": number, "WriteCapacityUnits": number } }
     */
    public const AWS_DYNAMODB_GLOBAL_SECONDARY_INDEXES = 'aws.dynamodb.global_secondary_indexes';

    /**
     * The value of the `IndexName` request parameter.
     *
     * @example name_to_group
     */
    public const AWS_DYNAMODB_INDEX_NAME = 'aws.dynamodb.index_name';

    /**
     * The JSON-serialized value of the `ItemCollectionMetrics` response field.
     *
     * @example { "string" : [ { "ItemCollectionKey": { "string" : { "B": blob, "BOOL": boolean, "BS": [ blob ], "L": [ "AttributeValue" ], "M": { "string" : "AttributeValue" }, "N": "string", "NS": [ "string" ], "NULL": boolean, "S": "string", "SS": [ "string" ] } }, "SizeEstimateRangeGB": [ number ] } ] }
     */
    public const AWS_DYNAMODB_ITEM_COLLECTION_METRICS = 'aws.dynamodb.item_collection_metrics';

    /**
     * The value of the `Limit` request parameter.
     *
     * @example 10
     */
    public const AWS_DYNAMODB_LIMIT = 'aws.dynamodb.limit';

    /**
     * The JSON-serialized value of each item of the `LocalSecondaryIndexes` request field.
     *
     * @example { "IndexArn": "string", "IndexName": "string", "IndexSizeBytes": number, "ItemCount": number, "KeySchema": [ { "AttributeName": "string", "KeyType": "string" } ], "Projection": { "NonKeyAttributes": [ "string" ], "ProjectionType": "string" } }
     */
    public const AWS_DYNAMODB_LOCAL_SECONDARY_INDEXES = 'aws.dynamodb.local_secondary_indexes';

    /**
     * The value of the `ProjectionExpression` request parameter.
     *
     * @example Title
     * @example Title, Price, Color
     * @example Title, Description, RelatedItems, ProductReviews
     */
    public const AWS_DYNAMODB_PROJECTION = 'aws.dynamodb.projection';

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
     * The value of the `ScanIndexForward` request parameter.
     */
    public const AWS_DYNAMODB_SCAN_FORWARD = 'aws.dynamodb.scan_forward';

    /**
     * The value of the `ScannedCount` response parameter.
     *
     * @example 50
     */
    public const AWS_DYNAMODB_SCANNED_COUNT = 'aws.dynamodb.scanned_count';

    /**
     * The value of the `Segment` request parameter.
     *
     * @example 10
     */
    public const AWS_DYNAMODB_SEGMENT = 'aws.dynamodb.segment';

    /**
     * The value of the `Select` request parameter.
     *
     * @example ALL_ATTRIBUTES
     * @example COUNT
     */
    public const AWS_DYNAMODB_SELECT = 'aws.dynamodb.select';

    /**
     * The number of items in the `TableNames` response parameter.
     *
     * @example 20
     */
    public const AWS_DYNAMODB_TABLE_COUNT = 'aws.dynamodb.table_count';

    /**
     * The keys in the `RequestItems` object field.
     *
     * @example Users
     * @example Cats
     */
    public const AWS_DYNAMODB_TABLE_NAMES = 'aws.dynamodb.table_names';

    /**
     * The value of the `TotalSegments` request parameter.
     *
     * @example 100
     */
    public const AWS_DYNAMODB_TOTAL_SEGMENTS = 'aws.dynamodb.total_segments';

    /**
     * The full invoked ARN as provided on the `Context` passed to the function (`Lambda-Runtime-Invoked-Function-Arn` header on the `/runtime/invocation/next` applicable).
     *
     * This may be different from `cloud.resource_id` if an alias is involved.
     *
     * @example arn:aws:lambda:us-east-1:123456:function:myfunction:myalias
     */
    public const AWS_LAMBDA_INVOKED_ARN = 'aws.lambda.invoked_arn';

    /**
     * The AWS request ID as returned in the response headers `x-amz-request-id` or `x-amz-requestid`.
     *
     * @example 79b9da39-b7ae-508a-a6bc-864b2829c622
     * @example C9ER4AJX75574TDJ
     */
    public const AWS_REQUEST_ID = 'aws.request_id';

    /**
     * The S3 bucket name the request refers to. Corresponds to the `--bucket` parameter of the S3 API operations.
     *
     * The `bucket` attribute is applicable to all S3 operations that reference a bucket, i.e. that require the bucket name as a mandatory parameter.
     * This applies to almost all S3 operations except `list-buckets`.
     *
     * @example some-bucket-name
     */
    public const AWS_S3_BUCKET = 'aws.s3.bucket';

    /**
     * The source object (in the form `bucket`/`key`) for the copy operation.
     *
     * The `copy_source` attribute applies to S3 copy operations and corresponds to the `--copy-source` parameter
     * of the copy-object operation within the S3 API.
     * This applies in particular to the following operations:<ul>
     * <li>copy-object</li>
     * <li>upload-part-copy</li>
     * </ul>
     *
     * @example someFile.yml
     */
    public const AWS_S3_COPY_SOURCE = 'aws.s3.copy_source';

    /**
     * The delete request container that specifies the objects to be deleted.
     *
     * The `delete` attribute is only applicable to the delete-object operation.
     * The `delete` attribute corresponds to the `--delete` parameter of the
     * delete-objects operation within the S3 API.
     *
     * @example Objects=[{Key=string,VersionId=string},{Key=string,VersionId=string}],Quiet=boolean
     */
    public const AWS_S3_DELETE = 'aws.s3.delete';

    /**
     * The S3 object key the request refers to. Corresponds to the `--key` parameter of the S3 API operations.
     *
     * The `key` attribute is applicable to all object-related S3 operations, i.e. that require the object key as a mandatory parameter.
     * This applies in particular to the following operations:<ul>
     * <li>copy-object</li>
     * <li>delete-object</li>
     * <li>get-object</li>
     * <li>head-object</li>
     * <li>put-object</li>
     * <li>restore-object</li>
     * <li>select-object-content</li>
     * <li>abort-multipart-upload</li>
     * <li>complete-multipart-upload</li>
     * <li>create-multipart-upload</li>
     * <li>list-parts</li>
     * <li>upload-part</li>
     * <li>upload-part-copy</li>
     * </ul>
     *
     * @example someFile.yml
     */
    public const AWS_S3_KEY = 'aws.s3.key';

    /**
     * The part number of the part being uploaded in a multipart-upload operation. This is a positive integer between 1 and 10,000.
     *
     * The `part_number` attribute is only applicable to the upload-part
     * and upload-part-copy operations.
     * The `part_number` attribute corresponds to the `--part-number` parameter of the
     * upload-part operation within the S3 API.
     *
     * @example 3456
     */
    public const AWS_S3_PART_NUMBER = 'aws.s3.part_number';

    /**
     * Upload ID that identifies the multipart upload.
     *
     * The `upload_id` attribute applies to S3 multipart-upload operations and corresponds to the `--upload-id` parameter
     * of the S3 API multipart operations.
     * This applies in particular to the following operations:<ul>
     * <li>abort-multipart-upload</li>
     * <li>complete-multipart-upload</li>
     * <li>list-parts</li>
     * <li>upload-part</li>
     * <li>upload-part-copy</li>
     * </ul>
     *
     * @example dfRtDYWFbkRONycy.Yxwh66Yjlx.cph0gtNBtJ
     */
    public const AWS_S3_UPLOAD_ID = 'aws.s3.upload_id';

    /**
     * Array of brand name and version separated by a space.
     *
     * This value is intended to be taken from the UA client hints API (`navigator.userAgentData.brands`).
     *
     * @example  Not A;Brand 99
     * @example Chromium 99
     * @example Chrome 99
     */
    public const BROWSER_BRANDS = 'browser.brands';

    /**
     * Preferred language of the user using the browser.
     *
     * This value is intended to be taken from the Navigator API `navigator.language`.
     *
     * @example en
     * @example en-US
     * @example fr
     * @example fr-FR
     */
    public const BROWSER_LANGUAGE = 'browser.language';

    /**
     * A boolean that is true if the browser is running on a mobile device.
     *
     * This value is intended to be taken from the UA client hints API (`navigator.userAgentData.mobile`). If unavailable, this attribute SHOULD be left unset.
     */
    public const BROWSER_MOBILE = 'browser.mobile';

    /**
     * The platform on which the browser is running.
     *
     * This value is intended to be taken from the UA client hints API (`navigator.userAgentData.platform`). If unavailable, the legacy `navigator.platform` API SHOULD NOT be used instead and this attribute SHOULD be left unset in order for the values to be consistent.
     * The list of possible values is defined in the W3C User-Agent Client Hints specification. Note that some (but not all) of these values can overlap with values in the `os.type` and `os.name` attributes. However, for consistency, the values in the `browser.platform` attribute should capture the exact value that the user agent provides.
     *
     * @example Windows
     * @example macOS
     * @example Android
     */
    public const BROWSER_PLATFORM = 'browser.platform';

    /**
     * Client address - domain name if available without reverse DNS lookup; otherwise, IP address or Unix domain socket name.
     *
     * When observed from the server side, and when communicating through an intermediary, `client.address` SHOULD represent the client address behind any intermediaries,  for example proxies, if it's available.
     *
     * @example client.example.com
     * @example 10.1.2.80
     * @example /tmp/my.sock
     */
    public const CLIENT_ADDRESS = 'client.address';

    /**
     * Client port number.
     *
     * When observed from the server side, and when communicating through an intermediary, `client.port` SHOULD represent the client port behind any intermediaries,  for example proxies, if it's available.
     *
     * @example 65123
     */
    public const CLIENT_PORT = 'client.port';

    /**
     * The cloud account ID the resource is assigned to.
     *
     * @example 111111111111
     * @example opentelemetry
     */
    public const CLOUD_ACCOUNT_ID = 'cloud.account.id';

    /**
     * Cloud regions often have multiple, isolated locations known as zones to increase availability. Availability zone represents the zone where the resource is running.
     *
     * Availability zones are called &quot;zones&quot; on Alibaba Cloud and Google Cloud.
     *
     * @example us-east-1c
     */
    public const CLOUD_AVAILABILITY_ZONE = 'cloud.availability_zone';

    /**
     * The cloud platform in use.
     *
     * The prefix of the service SHOULD match the one specified in `cloud.provider`.
     */
    public const CLOUD_PLATFORM = 'cloud.platform';

    /**
     * Name of the cloud provider.
     */
    public const CLOUD_PROVIDER = 'cloud.provider';

    /**
     * The geographical region the resource is running.
     *
     * Refer to your provider's docs to see the available regions, for example Alibaba Cloud regions, AWS regions, Azure regions, Google Cloud regions, or Tencent Cloud regions.
     *
     * @example us-central1
     * @example us-east-1
     */
    public const CLOUD_REGION = 'cloud.region';

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
     * The subject of the event in the context of the event producer (identified by source).
     *
     * @example mynewfile.jpg
     */
    public const CLOUDEVENTS_EVENT_SUBJECT = 'cloudevents.event_subject';

    /**
     * The event_type contains a value describing the type of event related to the originating occurrence.
     *
     * @example com.github.pull_request.opened
     * @example com.example.object.deleted.v2
     */
    public const CLOUDEVENTS_EVENT_TYPE = 'cloudevents.event_type';

    /**
     * The column number in `code.filepath` best representing the operation. It SHOULD point within the code unit named in `code.function`.
     *
     * @example 16
     */
    public const CODE_COLUMN = 'code.column';

    /**
     * The source code file name that identifies the code unit as uniquely as possible (preferably an absolute file path).
     *
     * @example /usr/local/MyApplication/content_root/app/index.php
     */
    public const CODE_FILEPATH = 'code.filepath';

    /**
     * The method or function name, or equivalent (usually rightmost part of the code unit's name).
     *
     * @example serveRequest
     */
    public const CODE_FUNCTION = 'code.function';

    /**
     * The line number in `code.filepath` best representing the operation. It SHOULD point within the code unit named in `code.function`.
     *
     * @example 42
     */
    public const CODE_LINENO = 'code.lineno';

    /**
     * The &quot;namespace&quot; within which `code.function` is defined. Usually the qualified class or module name, such that `code.namespace` + some separator + `code.function` form a unique identifier for the code unit.
     *
     * @example com.example.MyHttpService
     */
    public const CODE_NAMESPACE = 'code.namespace';

    /**
     * A stacktrace as a string in the natural representation for the language runtime. The representation is to be determined and documented by each language SIG.
     *
     * @example at com.example.GenerateTrace.methodB(GenerateTrace.java:13)\n at com.example.GenerateTrace.methodA(GenerateTrace.java:9)\n at com.example.GenerateTrace.main(GenerateTrace.java:5)
     */
    public const CODE_STACKTRACE = 'code.stacktrace';

    /**
     * The command used to run the container (i.e. the command name).
     *
     * If using embedded credentials or sensitive data, it is recommended to remove them to prevent potential leakage.
     *
     * @example otelcontribcol
     */
    public const CONTAINER_COMMAND = 'container.command';

    /**
     * All the command arguments (including the command/executable itself) run by the container. [2].
     *
     * @example otelcontribcol, --config, config.yaml
     */
    public const CONTAINER_COMMAND_ARGS = 'container.command_args';

    /**
     * The full command run by the container as a single string representing the full command. [2].
     *
     * @example otelcontribcol --config config.yaml
     */
    public const CONTAINER_COMMAND_LINE = 'container.command_line';

    /**
     * The CPU state for this data point.
     *
     * @example user
     * @example kernel
     */
    public const CONTAINER_CPU_STATE = 'container.cpu.state';

    /**
     * Container ID. Usually a UUID, as for example used to identify Docker containers. The UUID might be abbreviated.
     *
     * @example a3bf90e006b2
     */
    public const CONTAINER_ID = 'container.id';

    /**
     * Runtime specific image identifier. Usually a hash algorithm followed by a UUID.
     *
     * Docker defines a sha256 of the image id; `container.image.id` corresponds to the `Image` field from the Docker container inspect API endpoint.
     * K8s defines a link to the container registry repository with digest `"imageID": "registry.azurecr.io /namespace/service/dockerfile@sha256:bdeabd40c3a8a492eaf9e8e44d0ebbb84bac7ee25ac0cf8a7159d25f62555625"`.
     * The ID is assinged by the container runtime and can vary in different environments. Consider using `oci.manifest.digest` if it is important to identify the same image in different environments/runtimes.
     *
     * @example sha256:19c92d0a00d1b66d897bceaa7319bee0dd38a10a851c60bcec9474aa3f01e50f
     */
    public const CONTAINER_IMAGE_ID = 'container.image.id';

    /**
     * Name of the image the container was built on.
     *
     * @example gcr.io/opentelemetry/operator
     */
    public const CONTAINER_IMAGE_NAME = 'container.image.name';

    /**
     * Repo digests of the container image as provided by the container runtime.
     *
     * Docker and CRI report those under the `RepoDigests` field.
     *
     * @example example@sha256:afcc7f1ac1b49db317a7196c902e61c6c3c4607d63599ee1a82d702d249a0ccb
     * @example internal.registry.example.com:5000/example@sha256:b69959407d21e8a062e0416bf13405bb2b71ed7a84dde4158ebafacfa06f5578
     */
    public const CONTAINER_IMAGE_REPO_DIGESTS = 'container.image.repo_digests';

    /**
     * Container image tags. An example can be found in Docker Image Inspect. Should be only the `<tag>` section of the full name for example from `registry.example.com/my-org/my-image:<tag>`.
     *
     * @example v1.27.1
     * @example 3.5.7-0
     */
    public const CONTAINER_IMAGE_TAGS = 'container.image.tags';

    /**
     * Container name used by container runtime.
     *
     * @example opentelemetry-autoconf
     */
    public const CONTAINER_NAME = 'container.name';

    /**
     * The container runtime managing this container.
     *
     * @example docker
     * @example containerd
     * @example rkt
     */
    public const CONTAINER_RUNTIME = 'container.runtime';

    /**
     * The consistency level of the query. Based on consistency values from CQL.
     */
    public const DB_CASSANDRA_CONSISTENCY_LEVEL = 'db.cassandra.consistency_level';

    /**
     * The data center of the coordinating node for a query.
     *
     * @example us-west-2
     */
    public const DB_CASSANDRA_COORDINATOR_DC = 'db.cassandra.coordinator.dc';

    /**
     * The ID of the coordinating node for a query.
     *
     * @example be13faa2-8574-4d71-926d-27f16cf8a7af
     */
    public const DB_CASSANDRA_COORDINATOR_ID = 'db.cassandra.coordinator.id';

    /**
     * Whether or not the query is idempotent.
     */
    public const DB_CASSANDRA_IDEMPOTENCE = 'db.cassandra.idempotence';

    /**
     * The fetch size used for paging, i.e. how many rows will be returned at once.
     *
     * @example 5000
     */
    public const DB_CASSANDRA_PAGE_SIZE = 'db.cassandra.page_size';

    /**
     * The number of times a query was speculatively executed. Not set or `0` if the query was not executed speculatively.
     *
     * @example 2
     */
    public const DB_CASSANDRA_SPECULATIVE_EXECUTION_COUNT = 'db.cassandra.speculative_execution_count';

    /**
     * The name of the primary Cassandra table that the operation is acting upon, including the keyspace name (if applicable).
     *
     * This mirrors the db.sql.table attribute but references cassandra rather than sql. It is not recommended to attempt any client-side parsing of `db.statement` just to get this property, but it should be set if it is provided by the library being instrumented. If the operation is acting upon an anonymous table, or more than one table, this value MUST NOT be set.
     *
     * @example mytable
     */
    public const DB_CASSANDRA_TABLE = 'db.cassandra.table';

    /**
     * Deprecated, use `server.address`, `server.port` attributes instead.
     *
     * @deprecated "Replaced by `server.address` and `server.port`.".
     *
     * @example Server=(localdb)\v11.0;Integrated Security=true;
     */
    public const DB_CONNECTION_STRING = 'db.connection_string';

    /**
     * Unique Cosmos client instance id.
     *
     * @example 3ba4827d-4422-483f-b59f-85b74211c11d
     */
    public const DB_COSMOSDB_CLIENT_ID = 'db.cosmosdb.client_id';

    /**
     * Cosmos client connection mode.
     */
    public const DB_COSMOSDB_CONNECTION_MODE = 'db.cosmosdb.connection_mode';

    /**
     * Cosmos DB container name.
     *
     * @example anystring
     */
    public const DB_COSMOSDB_CONTAINER = 'db.cosmosdb.container';

    /**
     * CosmosDB Operation Type.
     */
    public const DB_COSMOSDB_OPERATION_TYPE = 'db.cosmosdb.operation_type';

    /**
     * RU consumed for that operation.
     *
     * @example 46.18
     * @example 1.0
     */
    public const DB_COSMOSDB_REQUEST_CHARGE = 'db.cosmosdb.request_charge';

    /**
     * Request payload size in bytes.
     */
    public const DB_COSMOSDB_REQUEST_CONTENT_LENGTH = 'db.cosmosdb.request_content_length';

    /**
     * Cosmos DB status code.
     *
     * @example 200
     * @example 201
     */
    public const DB_COSMOSDB_STATUS_CODE = 'db.cosmosdb.status_code';

    /**
     * Cosmos DB sub status code.
     *
     * @example 1000
     * @example 1002
     */
    public const DB_COSMOSDB_SUB_STATUS_CODE = 'db.cosmosdb.sub_status_code';

    /**
     * Represents the identifier of an Elasticsearch cluster.
     *
     * @example e9106fc68e3044f0b1475b04bf4ffd5f
     */
    public const DB_ELASTICSEARCH_CLUSTER_NAME = 'db.elasticsearch.cluster.name';

    /**
     * Deprecated, use `db.instance.id` instead.
     *
     * @deprecated Replaced by `db.instance.id`.
     *
     * @example instance-0000000001
     */
    public const DB_ELASTICSEARCH_NODE_NAME = 'db.elasticsearch.node.name';

    /**
     * An identifier (address, unique name, or any other identifier) of the database instance that is executing queries or mutations on the current connection. This is useful in cases where the database is running in a clustered environment and the instrumentation is able to record the node executing the query. The client may obtain this value in databases like MySQL using queries like `select @@hostname`.
     *
     * @example mysql-e26b99z.example.com
     */
    public const DB_INSTANCE_ID = 'db.instance.id';

    /**
     * Removed, no replacement at this time.
     *
     * @deprecated Removed as not used.
     *
     * @example org.postgresql.Driver
     * @example com.microsoft.sqlserver.jdbc.SQLServerDriver
     */
    public const DB_JDBC_DRIVER_CLASSNAME = 'db.jdbc.driver_classname';

    /**
     * The MongoDB collection being accessed within the database stated in `db.name`.
     *
     * @example customers
     * @example products
     */
    public const DB_MONGODB_COLLECTION = 'db.mongodb.collection';

    /**
     * The Microsoft SQL Server instance name connecting to. This name is used to determine the port of a named instance.
     *
     * If setting a `db.mssql.instance_name`, `server.port` is no longer required (but still recommended if non-standard).
     *
     * @example MSSQLSERVER
     */
    public const DB_MSSQL_INSTANCE_NAME = 'db.mssql.instance_name';

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
     * The index of the database being accessed as used in the `SELECT` command, provided as an integer. To be used instead of the generic `db.name` attribute.
     *
     * @example 1
     * @example 15
     */
    public const DB_REDIS_DATABASE_INDEX = 'db.redis.database_index';

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
     * The database statement being executed.
     *
     * @example SELECT * FROM wuser_table
     * @example SET mykey "WuValue"
     */
    public const DB_STATEMENT = 'db.statement';

    /**
     * An identifier for the database management system (DBMS) product being used. See below for a list of well-known identifiers.
     */
    public const DB_SYSTEM = 'db.system';

    /**
     * Username for accessing the database.
     *
     * @example readonly_user
     * @example reporting_user
     */
    public const DB_USER = 'db.user';

    /**
     * Name of the deployment environment (aka deployment tier).
     *
     * `deployment.environment` does not affect the uniqueness constraints defined through
     * the `service.namespace`, `service.name` and `service.instance.id` resource attributes.
     * This implies that resources carrying the following attribute combinations MUST be
     * considered to be identifying the same service:<ul>
     * <li>`service.name=frontend`, `deployment.environment=production`</li>
     * <li>`service.name=frontend`, `deployment.environment=staging`.</li>
     * </ul>
     *
     * @example staging
     * @example production
     */
    public const DEPLOYMENT_ENVIRONMENT = 'deployment.environment';

    /**
     * Destination address - domain name if available without reverse DNS lookup; otherwise, IP address or Unix domain socket name.
     *
     * When observed from the source side, and when communicating through an intermediary, `destination.address` SHOULD represent the destination address behind any intermediaries, for example proxies, if it's available.
     *
     * @example destination.example.com
     * @example 10.1.2.80
     * @example /tmp/my.sock
     */
    public const DESTINATION_ADDRESS = 'destination.address';

    /**
     * Destination port number.
     *
     * @example 3389
     * @example 2888
     */
    public const DESTINATION_PORT = 'destination.port';

    /**
     * A unique identifier representing the device.
     *
     * The device identifier MUST only be defined using the values outlined below. This value is not an advertising identifier and MUST NOT be used as such. On iOS (Swift or Objective-C), this value MUST be equal to the vendor identifier. On Android (Java or Kotlin), this value MUST be equal to the Firebase Installation ID or a globally unique UUID which is persisted across sessions in your application. More information can be found here on best practices and exact implementation details. Caution should be taken when storing personal data or anything which can identify a user. GDPR and data protection laws may apply, ensure you do your own due diligence.
     *
     * @example 2ab2916d-a51f-4ac8-80ee-45ac31a28092
     */
    public const DEVICE_ID = 'device.id';

    /**
     * The name of the device manufacturer.
     *
     * The Android OS provides this field via Build. iOS apps SHOULD hardcode the value `Apple`.
     *
     * @example Apple
     * @example Samsung
     */
    public const DEVICE_MANUFACTURER = 'device.manufacturer';

    /**
     * The model identifier for the device.
     *
     * It's recommended this value represents a machine-readable version of the model identifier rather than the market or consumer-friendly name of the device.
     *
     * @example iPhone3,4
     * @example SM-G920F
     */
    public const DEVICE_MODEL_IDENTIFIER = 'device.model.identifier';

    /**
     * The marketing name for the device model.
     *
     * It's recommended this value represents a human-readable version of the device model rather than a machine-readable alternative.
     *
     * @example iPhone 6s Plus
     * @example Samsung Galaxy S6
     */
    public const DEVICE_MODEL_NAME = 'device.model.name';

    /**
     * The disk IO operation direction.
     *
     * @example read
     */
    public const DISK_IO_DIRECTION = 'disk.io.direction';

    /**
     * The name being queried.
     *
     * If the name field contains non-printable characters (below 32 or above 126), those characters should be represented as escaped base 10 integers (\DDD). Back slashes and quotes should be escaped. Tabs, carriage returns, and line feeds should be converted to \t, \r, and \n respectively.
     *
     * @example www.example.com
     * @example opentelemetry.io
     */
    public const DNS_QUESTION_NAME = 'dns.question.name';

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
     * Describes a class of error the operation ended with.
     *
     * If the request fails with an error before response status code was sent or received,
     * `error.type` SHOULD be set to exception type (its fully-qualified class name, if applicable)
     * or a component-specific low cardinality error identifier.If response status code was sent or received and status indicates an error according to HTTP span status definition,
     * `error.type` SHOULD be set to the status code number (represented as a string), an exception type (if thrown) or a component-specific error identifier.The `error.type` value SHOULD be predictable and SHOULD have low cardinality.
     * Instrumentations SHOULD document the list of errors they report.The cardinality of `error.type` within one instrumentation library SHOULD be low, but
     * telemetry consumers that aggregate data from multiple instrumentation libraries and applications
     * should be prepared for `error.type` to have high cardinality at query time, when no
     * additional filters are applied.If the request has completed successfully, instrumentations SHOULD NOT set `error.type`.
     *
     * @example timeout
     * @example java.net.UnknownHostException
     * @example server_certificate_invalid
     * @example 500
     */
    public const ERROR_TYPE = 'error.type';

    /**
     * Identifies the class / type of event.
     *
     * Event names are subject to the same rules as attribute names. Notably, event names are namespaced to avoid collisions and provide a clean separation of semantics for events in separate domains like browser, mobile, and kubernetes.
     *
     * @example browser.mouse.click
     * @example device.app.lifecycle
     */
    public const EVENT_NAME = 'event.name';

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
     * as done in the example for recording span exceptions.It follows that an exception may still escape the scope of the span
     * even if the `exception.escaped` attribute was not set or set to false,
     * since the event might have been recorded at a time where it was not
     * clear whether the exception will escape.
     */
    public const EXCEPTION_ESCAPED = 'exception.escaped';

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
     * The type of the exception (its fully-qualified class name, if applicable). The dynamic type of the exception should be preferred over the static type in languages that support it.
     *
     * @example java.net.ConnectException
     * @example OSError
     */
    public const EXCEPTION_TYPE = 'exception.type';

    /**
     * A boolean that is true if the serverless function is executed for the first time (aka cold-start).
     */
    public const FAAS_COLDSTART = 'faas.coldstart';

    /**
     * A string containing the schedule period as Cron Expression.
     *
     * @example 0/5 * * * ? *
     */
    public const FAAS_CRON = 'faas.cron';

    /**
     * The name of the source on which the triggering operation was performed. For example, in Cloud Storage or S3 corresponds to the bucket name, and in Cosmos DB to the database name.
     *
     * @example myBucketName
     * @example myDbName
     */
    public const FAAS_DOCUMENT_COLLECTION = 'faas.document.collection';

    /**
     * The document name/table subjected to the operation. For example, in Cloud Storage or S3 is the name of the file, and in Cosmos DB the table name.
     *
     * @example myFile.txt
     * @example myTableName
     */
    public const FAAS_DOCUMENT_NAME = 'faas.document.name';

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
     * The execution environment ID as a string, that will be potentially reused for other invocations to the same function/function version.
     *
     * <ul>
     * <li><strong>AWS Lambda:</strong> Use the (full) log stream name.</li>
     * </ul>
     *
     * @example 2021/06/28/[$LATEST]2f399eb14537447da05ab2a2e39309de
     */
    public const FAAS_INSTANCE = 'faas.instance';

    /**
     * The invocation ID of the current function invocation.
     *
     * @example af9d5aa4-a685-4c5f-a22b-444f80b3cc28
     */
    public const FAAS_INVOCATION_ID = 'faas.invocation_id';

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
     * The amount of memory available to the serverless function converted to Bytes.
     *
     * It's recommended to set this attribute since e.g. too little memory can easily stop a Java AWS Lambda function from working correctly. On AWS Lambda, the environment variable `AWS_LAMBDA_FUNCTION_MEMORY_SIZE` provides this information (which must be multiplied by 1,048,576).
     *
     * @example 134217728
     */
    public const FAAS_MAX_MEMORY = 'faas.max_memory';

    /**
     * The name of the single function that this runtime instance executes.
     *
     * This is the name of the function as configured/deployed on the FaaS
     * platform and is usually different from the name of the callback
     * function (which may be stored in the
     * `code.namespace`/`code.function`
     * span attributes).For some cloud providers, the above definition is ambiguous. The following
     * definition of function name MUST be used for this attribute
     * (and consequently the span name) for the listed cloud providers/products:<ul>
     * <li><strong>Azure:</strong>  The full name `<FUNCAPP>/<FUNC>`, i.e., function app name
     * followed by a forward slash followed by the function name (this form
     * can also be seen in the resource JSON for the function).
     * This means that a span attribute MUST be used, as an Azure function
     * app can host multiple functions that would usually share
     * a TracerProvider (see also the `cloud.resource_id` attribute).</li>
     * </ul>
     *
     * @example my-function
     * @example myazurefunctionapp/some-function-name
     */
    public const FAAS_NAME = 'faas.name';

    /**
     * A string containing the function invocation time in the ISO 8601 format expressed in UTC.
     *
     * @example 2020-01-23T13:47:06Z
     */
    public const FAAS_TIME = 'faas.time';

    /**
     * Type of the trigger which caused this function invocation.
     */
    public const FAAS_TRIGGER = 'faas.trigger';

    /**
     * The immutable version of the function being executed.
     *
     * Depending on the cloud provider and platform, use:<ul>
     * <li><strong>AWS Lambda:</strong> The function version
     * (an integer represented as a decimal string).</li>
     * <li><strong>Google Cloud Run (Services):</strong> The revision
     * (i.e., the function name plus the revision suffix).</li>
     * <li><strong>Google Cloud Functions:</strong> The value of the
     * `K_REVISION` environment variable.</li>
     * <li><strong>Azure Functions:</strong> Not applicable. Do not set this attribute.</li>
     * </ul>
     *
     * @example 26
     * @example pinkfroid-00002
     */
    public const FAAS_VERSION = 'faas.version';

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
     * Directory where the file is located. It should include the drive letter, when appropriate.
     *
     * @example /home/user
     * @example C:\Program Files\MyApp
     */
    public const FILE_DIRECTORY = 'file.directory';

    /**
     * File extension, excluding the leading dot.
     *
     * When the file name has multiple extensions (example.tar.gz), only the last one should be captured (&quot;gz&quot;, not &quot;tar.gz&quot;).
     *
     * @example png
     * @example gz
     */
    public const FILE_EXTENSION = 'file.extension';

    /**
     * Name of the file including the extension, without the directory.
     *
     * @example example.png
     */
    public const FILE_NAME = 'file.name';

    /**
     * Full path to the file, including the file name. It should include the drive letter, when appropriate.
     *
     * @example /home/alice/example.png
     * @example C:\Program Files\MyApp\myapp.exe
     */
    public const FILE_PATH = 'file.path';

    /**
     * File size in bytes.
     */
    public const FILE_SIZE = 'file.size';

    /**
     * The name of the Cloud Run execution being run for the Job, as set by the `CLOUD_RUN_EXECUTION` environment variable.
     *
     * @example job-name-xxxx
     * @example sample-job-mdw84
     */
    public const GCP_CLOUD_RUN_JOB_EXECUTION = 'gcp.cloud_run.job.execution';

    /**
     * The index for a task within an execution as provided by the `CLOUD_RUN_TASK_INDEX` environment variable.
     *
     * @example 1
     */
    public const GCP_CLOUD_RUN_JOB_TASK_INDEX = 'gcp.cloud_run.job.task_index';

    /**
     * The hostname of a GCE instance. This is the full value of the default or custom hostname.
     *
     * @example my-host1234.example.com
     * @example sample-vm.us-west1-b.c.my-project.internal
     */
    public const GCP_GCE_INSTANCE_HOSTNAME = 'gcp.gce.instance.hostname';

    /**
     * The instance name of a GCE instance. This is the value provided by `host.name`, the visible name of the instance in the Cloud Console UI, and the prefix for the default hostname of the instance as defined by the default internal DNS name.
     *
     * @example instance-1
     * @example my-vm-name
     */
    public const GCP_GCE_INSTANCE_NAME = 'gcp.gce.instance.name';

    /**
     * The GraphQL document being executed.
     *
     * The value may be sanitized to exclude sensitive information.
     *
     * @example query findBookById { bookById(id: ?) { name } }
     */
    public const GRAPHQL_DOCUMENT = 'graphql.document';

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
     * The CPU architecture the host system is running on.
     */
    public const HOST_ARCH = 'host.arch';

    /**
     * The amount of level 2 memory cache available to the processor (in Bytes).
     *
     * @example 12288000
     */
    public const HOST_CPU_CACHE_L2_SIZE = 'host.cpu.cache.l2.size';

    /**
     * Family or generation of the CPU.
     *
     * @example 6
     * @example PA-RISC 1.1e
     */
    public const HOST_CPU_FAMILY = 'host.cpu.family';

    /**
     * Model identifier. It provides more granular information about the CPU, distinguishing it from other CPUs within the same family.
     *
     * @example 6
     * @example 9000/778/B180L
     */
    public const HOST_CPU_MODEL_ID = 'host.cpu.model.id';

    /**
     * Model designation of the processor.
     *
     * @example 11th Gen Intel(R) Core(TM) i7-1185G7 @ 3.00GHz
     */
    public const HOST_CPU_MODEL_NAME = 'host.cpu.model.name';

    /**
     * Stepping or core revisions.
     *
     * @example 1
     * @example r1p1
     */
    public const HOST_CPU_STEPPING = 'host.cpu.stepping';

    /**
     * Processor manufacturer identifier. A maximum 12-character string.
     *
     * CPUID command returns the vendor ID string in EBX, EDX and ECX registers. Writing these to memory in this order results in a 12-character string.
     *
     * @example GenuineIntel
     */
    public const HOST_CPU_VENDOR_ID = 'host.cpu.vendor.id';

    /**
     * Unique host ID. For Cloud, this must be the instance_id assigned by the cloud provider. For non-containerized systems, this should be the `machine-id`. See the table below for the sources to use to determine the `machine-id` based on operating system.
     *
     * @example fdbf79e8af94cb7f9e8df36789187052
     */
    public const HOST_ID = 'host.id';

    /**
     * VM image ID or host OS image ID. For Cloud, this value is from the provider.
     *
     * @example ami-07b06b442921831e5
     */
    public const HOST_IMAGE_ID = 'host.image.id';

    /**
     * Name of the VM image or OS install the host was instantiated from.
     *
     * @example infra-ami-eks-worker-node-7d4ec78312
     * @example CentOS-8-x86_64-1905
     */
    public const HOST_IMAGE_NAME = 'host.image.name';

    /**
     * The version string of the VM image or host OS as defined in Version Attributes.
     *
     * @example 0.1
     */
    public const HOST_IMAGE_VERSION = 'host.image.version';

    /**
     * Available IP addresses of the host, excluding loopback interfaces.
     *
     * IPv4 Addresses MUST be specified in dotted-quad notation. IPv6 addresses MUST be specified in the RFC 5952 format.
     *
     * @example 192.168.1.140
     * @example fe80::abc2:4a28:737a:609e
     */
    public const HOST_IP = 'host.ip';

    /**
     * Available MAC addresses of the host, excluding loopback interfaces.
     *
     * MAC Addresses MUST be represented in IEEE RA hexadecimal form: as hyphen-separated octets in uppercase hexadecimal form from most to least significant.
     *
     * @example AC-DE-48-23-45-67
     * @example AC-DE-48-23-45-67-01-9F
     */
    public const HOST_MAC = 'host.mac';

    /**
     * Name of the host. On Unix systems, it may contain what the hostname command returns, or the fully qualified hostname, or another name specified by the user.
     *
     * @example opentelemetry-test
     */
    public const HOST_NAME = 'host.name';

    /**
     * Type of host. For Cloud, this must be the machine type.
     *
     * @example n1-standard-1
     */
    public const HOST_TYPE = 'host.type';

    /**
     * State of the HTTP connection in the HTTP connection pool.
     *
     * @example active
     * @example idle
     */
    public const HTTP_CONNECTION_STATE = 'http.connection.state';

    /**
     * Deprecated, use `network.protocol.name` instead.
     *
     * @deprecated Replaced by `network.protocol.name`.
     */
    public const HTTP_FLAVOR = 'http.flavor';

    /**
     * Deprecated, use `http.request.method` instead.
     *
     * @deprecated Replaced by `http.request.method`.
     *
     * @example GET
     * @example POST
     * @example HEAD
     */
    public const HTTP_METHOD = 'http.method';

    /**
     * The size of the request payload body in bytes. This is the number of bytes transferred excluding headers and is often, but not always, present as the Content-Length header. For requests using transport encoding, this should be the compressed size.
     *
     * @example 3495
     */
    public const HTTP_REQUEST_BODY_SIZE = 'http.request.body.size';

    /**
     * HTTP request method.
     *
     * HTTP request method value SHOULD be &quot;known&quot; to the instrumentation.
     * By default, this convention defines &quot;known&quot; methods as the ones listed in RFC9110
     * and the PATCH method defined in RFC5789.If the HTTP request method is not known to instrumentation, it MUST set the `http.request.method` attribute to `_OTHER`.If the HTTP instrumentation could end up converting valid HTTP request methods to `_OTHER`, then it MUST provide a way to override
     * the list of known HTTP methods. If this override is done via environment variable, then the environment variable MUST be named
     * OTEL_INSTRUMENTATION_HTTP_KNOWN_METHODS and support a comma-separated list of case-sensitive known HTTP methods
     * (this list MUST be a full override of the default known method, it is not a list of known methods in addition to the defaults).HTTP method names are case-sensitive and `http.request.method` attribute value MUST match a known HTTP method name exactly.
     * Instrumentations for specific web frameworks that consider HTTP methods to be case insensitive, SHOULD populate a canonical equivalent.
     * Tracing instrumentations that do so, MUST also set `http.request.method_original` to the original value.
     *
     * @example GET
     * @example POST
     * @example HEAD
     */
    public const HTTP_REQUEST_METHOD = 'http.request.method';

    /**
     * Original HTTP method sent by the client in the request line.
     *
     * @example GeT
     * @example ACL
     * @example foo
     */
    public const HTTP_REQUEST_METHOD_ORIGINAL = 'http.request.method_original';

    /**
     * The ordinal number of request resending attempt (for any reason, including redirects).
     *
     * The resend count SHOULD be updated each time an HTTP request gets resent by the client, regardless of what was the cause of the resending (e.g. redirection, authorization failure, 503 Server Unavailable, network issues, or any other).
     *
     * @example 3
     */
    public const HTTP_REQUEST_RESEND_COUNT = 'http.request.resend_count';

    /**
     * The total size of the request in bytes. This should be the total number of bytes sent over the wire, including the request line (HTTP/1.1), framing (HTTP/2 and HTTP/3), headers, and request body if any.
     *
     * @example 1437
     */
    public const HTTP_REQUEST_SIZE = 'http.request.size';

    /**
     * Deprecated, use `http.request.header.content-length` instead.
     *
     * @deprecated Replaced by `http.request.header.content-length`.
     *
     * @example 3495
     */
    public const HTTP_REQUEST_CONTENT_LENGTH = 'http.request_content_length';

    /**
     * The size of the response payload body in bytes. This is the number of bytes transferred excluding headers and is often, but not always, present as the Content-Length header. For requests using transport encoding, this should be the compressed size.
     *
     * @example 3495
     */
    public const HTTP_RESPONSE_BODY_SIZE = 'http.response.body.size';

    /**
     * The total size of the response in bytes. This should be the total number of bytes sent over the wire, including the status line (HTTP/1.1), framing (HTTP/2 and HTTP/3), headers, and response body and trailers if any.
     *
     * @example 1437
     */
    public const HTTP_RESPONSE_SIZE = 'http.response.size';

    /**
     * HTTP response status code.
     *
     * @example 200
     */
    public const HTTP_RESPONSE_STATUS_CODE = 'http.response.status_code';

    /**
     * Deprecated, use `http.response.header.content-length` instead.
     *
     * @deprecated Replaced by `http.response.header.content-length`.
     *
     * @example 3495
     */
    public const HTTP_RESPONSE_CONTENT_LENGTH = 'http.response_content_length';

    /**
     * The matched route, that is, the path template in the format used by the respective server framework.
     *
     * MUST NOT be populated when this is not supported by the HTTP server framework as the route attribute should have low-cardinality and the URI path can NOT substitute it.
     * SHOULD include the application root if there is one.
     *
     * @example /users/:userID?
     * @example {controller}/{action}/{id?}
     */
    public const HTTP_ROUTE = 'http.route';

    /**
     * Deprecated, use `url.scheme` instead.
     *
     * @deprecated Replaced by `url.scheme` instead.
     *
     * @example http
     * @example https
     */
    public const HTTP_SCHEME = 'http.scheme';

    /**
     * Deprecated, use `http.response.status_code` instead.
     *
     * @deprecated Replaced by `http.response.status_code`.
     *
     * @example 200
     */
    public const HTTP_STATUS_CODE = 'http.status_code';

    /**
     * Deprecated, use `url.path` and `url.query` instead.
     *
     * @deprecated Split to `url.path` and `url.query.
     *
     * @example /search?q=OpenTelemetry#SemConv
     */
    public const HTTP_TARGET = 'http.target';

    /**
     * Deprecated, use `url.full` instead.
     *
     * @deprecated Replaced by `url.full`.
     *
     * @example https://www.foo.bar/search?q=OpenTelemetry#SemConv
     */
    public const HTTP_URL = 'http.url';

    /**
     * Deprecated, use `user_agent.original` instead.
     *
     * @deprecated Replaced by `user_agent.original`.
     *
     * @example CERN-LineMode/2.15 libwww/2.17b3
     * @example Mozilla/5.0 (iPhone; CPU iPhone OS 14_7_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.1.2 Mobile/15E148 Safari/604.1
     */
    public const HTTP_USER_AGENT = 'http.user_agent';

    /**
     * This attribute represents the state the application has transitioned into at the occurrence of the event.
     *
     * The iOS lifecycle states are defined in the UIApplicationDelegate documentation, and from which the `OS terminology` column values are derived.
     */
    public const IOS_STATE = 'ios.state';

    /**
     * Name of the buffer pool.
     *
     * Pool names are generally obtained via BufferPoolMXBean#getName().
     *
     * @example mapped
     * @example direct
     */
    public const JVM_BUFFER_POOL_NAME = 'jvm.buffer.pool.name';

    /**
     * Name of the memory pool.
     *
     * Pool names are generally obtained via MemoryPoolMXBean#getName().
     *
     * @example G1 Old Gen
     * @example G1 Eden space
     * @example G1 Survivor Space
     */
    public const JVM_MEMORY_POOL_NAME = 'jvm.memory.pool.name';

    /**
     * The type of memory.
     *
     * @example heap
     * @example non_heap
     */
    public const JVM_MEMORY_TYPE = 'jvm.memory.type';

    /**
     * The name of the cluster.
     *
     * @example opentelemetry-cluster
     */
    public const K8S_CLUSTER_NAME = 'k8s.cluster.name';

    /**
     * A pseudo-ID for the cluster, set to the UID of the `kube-system` namespace.
     *
     * K8s doesn't have support for obtaining a cluster ID. If this is ever
     * added, we will recommend collecting the `k8s.cluster.uid` through the
     * official APIs. In the meantime, we are able to use the `uid` of the
     * `kube-system` namespace as a proxy for cluster ID. Read on for the
     * rationale.Every object created in a K8s cluster is assigned a distinct UID. The
     * `kube-system` namespace is used by Kubernetes itself and will exist
     * for the lifetime of the cluster. Using the `uid` of the `kube-system`
     * namespace is a reasonable proxy for the K8s ClusterID as it will only
     * change if the cluster is rebuilt. Furthermore, Kubernetes UIDs are
     * UUIDs as standardized by
     * ISO/IEC 9834-8 and ITU-T X.667.
     * Which states:<blockquote>
     * If generated according to one of the mechanisms defined in Rec.</blockquote>
     * ITU-T X.667 | ISO/IEC 9834-8, a UUID is either guaranteed to be
     *   different from all other UUIDs generated before 3603 A.D., or is
     *   extremely likely to be different (depending on the mechanism chosen).Therefore, UIDs between clusters should be extremely unlikely to
     * conflict.
     *
     * @example 218fc5a9-a5f1-4b54-aa05-46717d0ab26d
     */
    public const K8S_CLUSTER_UID = 'k8s.cluster.uid';

    /**
     * The name of the Container from Pod specification, must be unique within a Pod. Container runtime usually uses different globally unique name (`container.name`).
     *
     * @example redis
     */
    public const K8S_CONTAINER_NAME = 'k8s.container.name';

    /**
     * Number of times the container was restarted. This attribute can be used to identify a particular container (running or stopped) within a container spec.
     *
     * @example 2
     */
    public const K8S_CONTAINER_RESTART_COUNT = 'k8s.container.restart_count';

    /**
     * The name of the CronJob.
     *
     * @example opentelemetry
     */
    public const K8S_CRONJOB_NAME = 'k8s.cronjob.name';

    /**
     * The UID of the CronJob.
     *
     * @example 275ecb36-5aa8-4c2a-9c47-d8bb681b9aff
     */
    public const K8S_CRONJOB_UID = 'k8s.cronjob.uid';

    /**
     * The name of the DaemonSet.
     *
     * @example opentelemetry
     */
    public const K8S_DAEMONSET_NAME = 'k8s.daemonset.name';

    /**
     * The UID of the DaemonSet.
     *
     * @example 275ecb36-5aa8-4c2a-9c47-d8bb681b9aff
     */
    public const K8S_DAEMONSET_UID = 'k8s.daemonset.uid';

    /**
     * The name of the Deployment.
     *
     * @example opentelemetry
     */
    public const K8S_DEPLOYMENT_NAME = 'k8s.deployment.name';

    /**
     * The UID of the Deployment.
     *
     * @example 275ecb36-5aa8-4c2a-9c47-d8bb681b9aff
     */
    public const K8S_DEPLOYMENT_UID = 'k8s.deployment.uid';

    /**
     * The name of the Job.
     *
     * @example opentelemetry
     */
    public const K8S_JOB_NAME = 'k8s.job.name';

    /**
     * The UID of the Job.
     *
     * @example 275ecb36-5aa8-4c2a-9c47-d8bb681b9aff
     */
    public const K8S_JOB_UID = 'k8s.job.uid';

    /**
     * The name of the namespace that the pod is running in.
     *
     * @example default
     */
    public const K8S_NAMESPACE_NAME = 'k8s.namespace.name';

    /**
     * The name of the Node.
     *
     * @example node-1
     */
    public const K8S_NODE_NAME = 'k8s.node.name';

    /**
     * The UID of the Node.
     *
     * @example 1eb3a0c6-0477-4080-a9cb-0cb7db65c6a2
     */
    public const K8S_NODE_UID = 'k8s.node.uid';

    /**
     * The name of the Pod.
     *
     * @example opentelemetry-pod-autoconf
     */
    public const K8S_POD_NAME = 'k8s.pod.name';

    /**
     * The UID of the Pod.
     *
     * @example 275ecb36-5aa8-4c2a-9c47-d8bb681b9aff
     */
    public const K8S_POD_UID = 'k8s.pod.uid';

    /**
     * The name of the ReplicaSet.
     *
     * @example opentelemetry
     */
    public const K8S_REPLICASET_NAME = 'k8s.replicaset.name';

    /**
     * The UID of the ReplicaSet.
     *
     * @example 275ecb36-5aa8-4c2a-9c47-d8bb681b9aff
     */
    public const K8S_REPLICASET_UID = 'k8s.replicaset.uid';

    /**
     * The name of the StatefulSet.
     *
     * @example opentelemetry
     */
    public const K8S_STATEFULSET_NAME = 'k8s.statefulset.name';

    /**
     * The UID of the StatefulSet.
     *
     * @example 275ecb36-5aa8-4c2a-9c47-d8bb681b9aff
     */
    public const K8S_STATEFULSET_UID = 'k8s.statefulset.uid';

    /**
     * The basename of the file.
     *
     * @example audit.log
     */
    public const LOG_FILE_NAME = 'log.file.name';

    /**
     * The basename of the file, with symlinks resolved.
     *
     * @example uuid.log
     */
    public const LOG_FILE_NAME_RESOLVED = 'log.file.name_resolved';

    /**
     * The full path to the file.
     *
     * @example /var/log/mysql/audit.log
     */
    public const LOG_FILE_PATH = 'log.file.path';

    /**
     * The full path to the file, with symlinks resolved.
     *
     * @example /var/lib/docker/uuid.log
     */
    public const LOG_FILE_PATH_RESOLVED = 'log.file.path_resolved';

    /**
     * The stream associated with the log. See below for a list of well-known values.
     */
    public const LOG_IOSTREAM = 'log.iostream';

    /**
     * A unique identifier for the Log Record.
     *
     * If an id is provided, other log records with the same id will be considered duplicates and can be removed safely. This means, that two distinguishable log records MUST have different values.
     * The id MAY be an Universally Unique Lexicographically Sortable Identifier (ULID), but other identifiers (e.g. UUID) may be used as needed.
     *
     * @example 01ARZ3NDEKTSV4RRFFQ69G5FAV
     */
    public const LOG_RECORD_UID = 'log.record.uid';

    /**
     * Compressed size of the message in bytes.
     */
    public const MESSAGE_COMPRESSED_SIZE = 'message.compressed_size';

    /**
     * MUST be calculated as two different counters starting from `1` one for sent messages and one for received message.
     *
     * This way we guarantee that the values will be consistent between different implementations.
     */
    public const MESSAGE_ID = 'message.id';

    /**
     * Whether this is a received or sent message.
     */
    public const MESSAGE_TYPE = 'message.type';

    /**
     * Uncompressed size of the message in bytes.
     */
    public const MESSAGE_UNCOMPRESSED_SIZE = 'message.uncompressed_size';

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
     * A unique identifier for the client that consumes or produces a message.
     *
     * @example client-5
     * @example myhost@8742@s8083jm
     */
    public const MESSAGING_CLIENT_ID = 'messaging.client_id';

    /**
     * A boolean that is true if the message destination is anonymous (could be unnamed or have auto-generated name).
     */
    public const MESSAGING_DESTINATION_ANONYMOUS = 'messaging.destination.anonymous';

    /**
     * The message destination name.
     *
     * Destination name SHOULD uniquely identify a specific queue, topic or other entity within the broker. If
     * the broker doesn't have such notion, the destination name SHOULD uniquely identify the broker.
     *
     * @example MyQueue
     * @example MyTopic
     */
    public const MESSAGING_DESTINATION_NAME = 'messaging.destination.name';

    /**
     * The identifier of the partition messages are sent to or received from, unique within the `messaging.destination.name`.
     *
     * @example 1
     */
    public const MESSAGING_DESTINATION_PARTITION_ID = 'messaging.destination.partition.id';

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
     * A boolean that is true if the publish message destination is anonymous (could be unnamed or have auto-generated name).
     */
    public const MESSAGING_DESTINATION_PUBLISH_ANONYMOUS = 'messaging.destination_publish.anonymous';

    /**
     * The name of the original destination the message was published to.
     *
     * The name SHOULD uniquely identify a specific queue, topic, or other entity within the broker. If
     * the broker doesn't have such notion, the original destination name SHOULD uniquely identify the broker.
     *
     * @example MyQueue
     * @example MyTopic
     */
    public const MESSAGING_DESTINATION_PUBLISH_NAME = 'messaging.destination_publish.name';

    /**
     * The name of the consumer group the event consumer is associated with.
     *
     * @example indexer
     */
    public const MESSAGING_EVENTHUBS_CONSUMER_GROUP = 'messaging.eventhubs.consumer.group';

    /**
     * The UTC epoch seconds at which the message has been accepted and stored in the entity.
     *
     * @example 1701393730
     */
    public const MESSAGING_EVENTHUBS_MESSAGE_ENQUEUED_TIME = 'messaging.eventhubs.message.enqueued_time';

    /**
     * The ordering key for a given message. If the attribute is not present, the message does not have an ordering key.
     *
     * @example ordering_key
     */
    public const MESSAGING_GCP_PUBSUB_MESSAGE_ORDERING_KEY = 'messaging.gcp_pubsub.message.ordering_key';

    /**
     * Name of the Kafka Consumer Group that is handling the message. Only applies to consumers, not producers.
     *
     * @example my-group
     */
    public const MESSAGING_KAFKA_CONSUMER_GROUP = 'messaging.kafka.consumer.group';

    /**
     * &quot;Deprecated, use `messaging.destination.partition.id` instead.&quot;.
     *
     * @deprecated Replaced by `messaging.destination.partition.id`.
     *
     * @example 2
     */
    public const MESSAGING_KAFKA_DESTINATION_PARTITION = 'messaging.kafka.destination.partition';

    /**
     * Message keys in Kafka are used for grouping alike messages to ensure they're processed on the same partition. They differ from `messaging.message.id` in that they're not unique. If the key is `null`, the attribute MUST NOT be set.
     *
     * If the key type is not string, it's string representation has to be supplied for the attribute. If the key has no unambiguous, canonical string form, don't include its value.
     *
     * @example myKey
     */
    public const MESSAGING_KAFKA_MESSAGE_KEY = 'messaging.kafka.message.key';

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
     * The size of the message body in bytes.
     *
     * This can refer to both the compressed or uncompressed body size. If both sizes are known, the uncompressed
     * body size should be used.
     *
     * @example 1439
     */
    public const MESSAGING_MESSAGE_BODY_SIZE = 'messaging.message.body.size';

    /**
     * The conversation ID identifying the conversation to which the message belongs, represented as a string. Sometimes called &quot;Correlation ID&quot;.
     *
     * @example MyConversationId
     */
    public const MESSAGING_MESSAGE_CONVERSATION_ID = 'messaging.message.conversation_id';

    /**
     * The size of the message body and metadata in bytes.
     *
     * This can refer to both the compressed or uncompressed size. If both sizes are known, the uncompressed
     * size should be used.
     *
     * @example 2738
     */
    public const MESSAGING_MESSAGE_ENVELOPE_SIZE = 'messaging.message.envelope.size';

    /**
     * A value used by the messaging system as an identifier for the message, represented as a string.
     *
     * @example 452a7c7c7c7048c2f887f61572b18fc2
     */
    public const MESSAGING_MESSAGE_ID = 'messaging.message.id';

    /**
     * A string identifying the kind of messaging operation.
     *
     * If a custom value is used, it MUST be of low cardinality.
     */
    public const MESSAGING_OPERATION = 'messaging.operation';

    /**
     * RabbitMQ message routing key.
     *
     * @example myKey
     */
    public const MESSAGING_RABBITMQ_DESTINATION_ROUTING_KEY = 'messaging.rabbitmq.destination.routing_key';

    /**
     * RabbitMQ message delivery tag.
     *
     * @example 123
     */
    public const MESSAGING_RABBITMQ_MESSAGE_DELIVERY_TAG = 'messaging.rabbitmq.message.delivery_tag';

    /**
     * Name of the RocketMQ producer/consumer group that is handling the message. The client type is identified by the SpanKind.
     *
     * @example myConsumerGroup
     */
    public const MESSAGING_ROCKETMQ_CLIENT_GROUP = 'messaging.rocketmq.client_group';

    /**
     * Model of message consumption. This only applies to consumer spans.
     */
    public const MESSAGING_ROCKETMQ_CONSUMPTION_MODEL = 'messaging.rocketmq.consumption_model';

    /**
     * The delay time level for delay message, which determines the message delay time.
     *
     * @example 3
     */
    public const MESSAGING_ROCKETMQ_MESSAGE_DELAY_TIME_LEVEL = 'messaging.rocketmq.message.delay_time_level';

    /**
     * The timestamp in milliseconds that the delay message is expected to be delivered to consumer.
     *
     * @example 1665987217045
     */
    public const MESSAGING_ROCKETMQ_MESSAGE_DELIVERY_TIMESTAMP = 'messaging.rocketmq.message.delivery_timestamp';

    /**
     * It is essential for FIFO message. Messages that belong to the same message group are always processed one by one within the same consumer group.
     *
     * @example myMessageGroup
     */
    public const MESSAGING_ROCKETMQ_MESSAGE_GROUP = 'messaging.rocketmq.message.group';

    /**
     * Key(s) of message, another way to mark message besides message id.
     *
     * @example keyA
     * @example keyB
     */
    public const MESSAGING_ROCKETMQ_MESSAGE_KEYS = 'messaging.rocketmq.message.keys';

    /**
     * The secondary classifier of message besides topic.
     *
     * @example tagA
     */
    public const MESSAGING_ROCKETMQ_MESSAGE_TAG = 'messaging.rocketmq.message.tag';

    /**
     * Type of message.
     */
    public const MESSAGING_ROCKETMQ_MESSAGE_TYPE = 'messaging.rocketmq.message.type';

    /**
     * Namespace of RocketMQ resources, resources in different namespaces are individual.
     *
     * @example myNamespace
     */
    public const MESSAGING_ROCKETMQ_NAMESPACE = 'messaging.rocketmq.namespace';

    /**
     * The name of the subscription in the topic messages are received from.
     *
     * @example mySubscription
     */
    public const MESSAGING_SERVICEBUS_DESTINATION_SUBSCRIPTION_NAME = 'messaging.servicebus.destination.subscription_name';

    /**
     * Describes the settlement type.
     */
    public const MESSAGING_SERVICEBUS_DISPOSITION_STATUS = 'messaging.servicebus.disposition_status';

    /**
     * Number of deliveries that have been attempted for this message.
     *
     * @example 2
     */
    public const MESSAGING_SERVICEBUS_MESSAGE_DELIVERY_COUNT = 'messaging.servicebus.message.delivery_count';

    /**
     * The UTC epoch seconds at which the message has been accepted and stored in the entity.
     *
     * @example 1701393730
     */
    public const MESSAGING_SERVICEBUS_MESSAGE_ENQUEUED_TIME = 'messaging.servicebus.message.enqueued_time';

    /**
     * An identifier for the messaging system being used. See below for a list of well-known identifiers.
     */
    public const MESSAGING_SYSTEM = 'messaging.system';

    /**
     * Deprecated, use `server.address`.
     *
     * @deprecated Replaced by `server.address`.
     *
     * @example example.com
     */
    public const NET_HOST_NAME = 'net.host.name';

    /**
     * Deprecated, use `server.port`.
     *
     * @deprecated Replaced by `server.port`.
     *
     * @example 8080
     */
    public const NET_HOST_PORT = 'net.host.port';

    /**
     * Deprecated, use `server.address` on client spans and `client.address` on server spans.
     *
     * @deprecated Replaced by `server.address` on client spans and `client.address` on server spans.
     *
     * @example example.com
     */
    public const NET_PEER_NAME = 'net.peer.name';

    /**
     * Deprecated, use `server.port` on client spans and `client.port` on server spans.
     *
     * @deprecated Replaced by `server.port` on client spans and `client.port` on server spans.
     *
     * @example 8080
     */
    public const NET_PEER_PORT = 'net.peer.port';

    /**
     * Deprecated, use `network.protocol.name`.
     *
     * @deprecated Replaced by `network.protocol.name`.
     *
     * @example amqp
     * @example http
     * @example mqtt
     */
    public const NET_PROTOCOL_NAME = 'net.protocol.name';

    /**
     * Deprecated, use `network.protocol.version`.
     *
     * @deprecated Replaced by `network.protocol.version`.
     *
     * @example 3.1.1
     */
    public const NET_PROTOCOL_VERSION = 'net.protocol.version';

    /**
     * Deprecated, use `network.transport` and `network.type`.
     *
     * @deprecated Split to `network.transport` and `network.type`.
     */
    public const NET_SOCK_FAMILY = 'net.sock.family';

    /**
     * Deprecated, use `network.local.address`.
     *
     * @deprecated Replaced by `network.local.address`.
     *
     * @example /var/my.sock
     */
    public const NET_SOCK_HOST_ADDR = 'net.sock.host.addr';

    /**
     * Deprecated, use `network.local.port`.
     *
     * @deprecated Replaced by `network.local.port`.
     *
     * @example 8080
     */
    public const NET_SOCK_HOST_PORT = 'net.sock.host.port';

    /**
     * Deprecated, use `network.peer.address`.
     *
     * @deprecated Replaced by `network.peer.address`.
     *
     * @example 192.168.0.1
     */
    public const NET_SOCK_PEER_ADDR = 'net.sock.peer.addr';

    /**
     * Deprecated, no replacement at this time.
     *
     * @deprecated Removed.
     *
     * @example /var/my.sock
     */
    public const NET_SOCK_PEER_NAME = 'net.sock.peer.name';

    /**
     * Deprecated, use `network.peer.port`.
     *
     * @deprecated Replaced by `network.peer.port`.
     *
     * @example 65531
     */
    public const NET_SOCK_PEER_PORT = 'net.sock.peer.port';

    /**
     * Deprecated, use `network.transport`.
     *
     * @deprecated Replaced by `network.transport`.
     */
    public const NET_TRANSPORT = 'net.transport';

    /**
     * The ISO 3166-1 alpha-2 2-character country code associated with the mobile carrier network.
     *
     * @example DE
     */
    public const NETWORK_CARRIER_ICC = 'network.carrier.icc';

    /**
     * The mobile carrier country code.
     *
     * @example 310
     */
    public const NETWORK_CARRIER_MCC = 'network.carrier.mcc';

    /**
     * The mobile carrier network code.
     *
     * @example 001
     */
    public const NETWORK_CARRIER_MNC = 'network.carrier.mnc';

    /**
     * The name of the mobile carrier.
     *
     * @example sprint
     */
    public const NETWORK_CARRIER_NAME = 'network.carrier.name';

    /**
     * This describes more details regarding the connection.type. It may be the type of cell technology connection, but it could be used for describing details about a wifi connection.
     *
     * @example LTE
     */
    public const NETWORK_CONNECTION_SUBTYPE = 'network.connection.subtype';

    /**
     * The internet connection type.
     *
     * @example wifi
     */
    public const NETWORK_CONNECTION_TYPE = 'network.connection.type';

    /**
     * The network IO operation direction.
     *
     * @example transmit
     */
    public const NETWORK_IO_DIRECTION = 'network.io.direction';

    /**
     * Local address of the network connection - IP address or Unix domain socket name.
     *
     * @example 10.1.2.80
     * @example /tmp/my.sock
     */
    public const NETWORK_LOCAL_ADDRESS = 'network.local.address';

    /**
     * Local port number of the network connection.
     *
     * @example 65123
     */
    public const NETWORK_LOCAL_PORT = 'network.local.port';

    /**
     * Peer address of the network connection - IP address or Unix domain socket name.
     *
     * @example 10.1.2.80
     * @example /tmp/my.sock
     */
    public const NETWORK_PEER_ADDRESS = 'network.peer.address';

    /**
     * Peer port number of the network connection.
     *
     * @example 65123
     */
    public const NETWORK_PEER_PORT = 'network.peer.port';

    /**
     * OSI application layer or non-OSI equivalent.
     *
     * The value SHOULD be normalized to lowercase.
     *
     * @example http
     * @example spdy
     */
    public const NETWORK_PROTOCOL_NAME = 'network.protocol.name';

    /**
     * The actual version of the protocol used for network communication.
     *
     * If protocol version is subject to negotiation (for example using ALPN), this attribute SHOULD be set to the negotiated version. If the actual protocol version is not known, this attribute SHOULD NOT be set.
     *
     * @example 1.0
     * @example 1.1
     * @example 2
     * @example 3
     */
    public const NETWORK_PROTOCOL_VERSION = 'network.protocol.version';

    /**
     * OSI transport layer or inter-process communication method.
     *
     * The value SHOULD be normalized to lowercase.Consider always setting the transport when setting a port number, since
     * a port number is ambiguous without knowing the transport. For example
     * different processes could be listening on TCP port 12345 and UDP port 12345.
     *
     * @example tcp
     * @example unix
     */
    public const NETWORK_TRANSPORT = 'network.transport';

    /**
     * OSI network layer or non-OSI equivalent.
     *
     * The value SHOULD be normalized to lowercase.
     *
     * @example ipv4
     * @example ipv6
     */
    public const NETWORK_TYPE = 'network.type';

    /**
     * The digest of the OCI image manifest. For container images specifically is the digest by which the container image is known.
     *
     * Follows OCI Image Manifest Specification, and specifically the Digest property.
     * An example can be found in Example Image Manifest.
     *
     * @example sha256:e4ca62c0d62f3e886e684806dfe9d4e0cda60d54986898173c1083856cfda0f4
     */
    public const OCI_MANIFEST_DIGEST = 'oci.manifest.digest';

    /**
     * Parent-child Reference type.
     *
     * The causal relationship between a child Span and a parent Span.
     */
    public const OPENTRACING_REF_TYPE = 'opentracing.ref_type';

    /**
     * Unique identifier for a particular build or compilation of the operating system.
     *
     * @example TQ3C.230805.001.B2
     * @example 20E247
     * @example 22621
     */
    public const OS_BUILD_ID = 'os.build_id';

    /**
     * Human readable (not intended to be parsed) OS version information, like e.g. reported by `ver` or `lsb_release -a` commands.
     *
     * @example Microsoft Windows [Version 10.0.18363.778]
     * @example Ubuntu 18.04.1 LTS
     */
    public const OS_DESCRIPTION = 'os.description';

    /**
     * Human readable operating system name.
     *
     * @example iOS
     * @example Android
     * @example Ubuntu
     */
    public const OS_NAME = 'os.name';

    /**
     * The operating system type.
     */
    public const OS_TYPE = 'os.type';

    /**
     * The version string of the operating system as defined in Version Attributes.
     *
     * @example 14.2.1
     * @example 18.04.1
     */
    public const OS_VERSION = 'os.version';

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
     * The `service.name` of the remote service. SHOULD be equal to the actual `service.name` resource attribute of the remote service if any.
     *
     * @example AuthTokenCache
     */
    public const PEER_SERVICE = 'peer.service';

    /**
     * The name of the connection pool; unique within the instrumented application. In case the connection pool implementation doesn't provide a name, instrumentation should use a combination of `server.address` and `server.port` attributes formatted as `server.address:server.port`.
     *
     * @example myDataSource
     */
    public const POOL_NAME = 'pool.name';

    /**
     * The command used to launch the process (i.e. the command name). On Linux based systems, can be set to the zeroth string in `proc/[pid]/cmdline`. On Windows, can be set to the first parameter extracted from `GetCommandLineW`.
     *
     * @example cmd/otelcol
     */
    public const PROCESS_COMMAND = 'process.command';

    /**
     * All the command arguments (including the command/executable itself) as received by the process. On Linux-based systems (and some other Unixoid systems supporting procfs), can be set according to the list of null-delimited strings extracted from `proc/[pid]/cmdline`. For libc-based executables, this would be the full argv vector passed to `main`.
     *
     * @example cmd/otecol
     * @example --config=config.yaml
     */
    public const PROCESS_COMMAND_ARGS = 'process.command_args';

    /**
     * The full command used to launch the process as a single string representing the full command. On Windows, can be set to the result of `GetCommandLineW`. Do not set this if you have to assemble it just for monitoring; use `process.command_args` instead.
     *
     * @example C:\cmd\otecol --config="my directory\config.yaml"
     */
    public const PROCESS_COMMAND_LINE = 'process.command_line';

    /**
     * The CPU state for this data point. A process SHOULD be characterized <em>either</em> by data points with no `state` labels, <em>or only</em> data points with `state` labels.
     */
    public const PROCESS_CPU_STATE = 'process.cpu.state';

    /**
     * The name of the process executable. On Linux based systems, can be set to the `Name` in `proc/[pid]/status`. On Windows, can be set to the base name of `GetProcessImageFileNameW`.
     *
     * @example otelcol
     */
    public const PROCESS_EXECUTABLE_NAME = 'process.executable.name';

    /**
     * The full path to the process executable. On Linux based systems, can be set to the target of `proc/[pid]/exe`. On Windows, can be set to the result of `GetProcessImageFileNameW`.
     *
     * @example /usr/bin/cmd/otelcol
     */
    public const PROCESS_EXECUTABLE_PATH = 'process.executable.path';

    /**
     * The username of the user that owns the process.
     *
     * @example root
     */
    public const PROCESS_OWNER = 'process.owner';

    /**
     * Parent Process identifier (PPID).
     *
     * @example 111
     */
    public const PROCESS_PARENT_PID = 'process.parent_pid';

    /**
     * Process identifier (PID).
     *
     * @example 1234
     */
    public const PROCESS_PID = 'process.pid';

    /**
     * An additional description about the runtime of the process, for example a specific vendor customization of the runtime environment.
     *
     * @example Eclipse OpenJ9 Eclipse OpenJ9 VM openj9-0.21.0
     */
    public const PROCESS_RUNTIME_DESCRIPTION = 'process.runtime.description';

    /**
     * The name of the runtime of this process. For compiled native binaries, this SHOULD be the name of the compiler.
     *
     * @example OpenJDK Runtime Environment
     */
    public const PROCESS_RUNTIME_NAME = 'process.runtime.name';

    /**
     * The version of the runtime of this process, as returned by the runtime without modification.
     *
     * @example 14.0.2
     */
    public const PROCESS_RUNTIME_VERSION = 'process.runtime.version';

    /**
     * The error codes of the Connect request. Error codes are always string values.
     */
    public const RPC_CONNECT_RPC_ERROR_CODE = 'rpc.connect_rpc.error_code';

    /**
     * The numeric status code of the gRPC request.
     */
    public const RPC_GRPC_STATUS_CODE = 'rpc.grpc.status_code';

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
     * `id` property of request or response. Since protocol allows id to be int, string, `null` or missing (for notifications), value is expected to be cast to string for simplicity. Use empty string in case of `null` value. Omit entirely if this is a notification.
     *
     * @example 10
     * @example request-7
     */
    public const RPC_JSONRPC_REQUEST_ID = 'rpc.jsonrpc.request_id';

    /**
     * Protocol version as in `jsonrpc` property of request/response. Since JSON-RPC 1.0 doesn't specify this, the value can be omitted.
     *
     * @example 2.0
     * @example 1.0
     */
    public const RPC_JSONRPC_VERSION = 'rpc.jsonrpc.version';

    /**
     * The name of the (logical) method being called, must be equal to the $method part in the span name.
     *
     * This is the logical name of the method from the RPC interface perspective, which can be different from the name of any implementing method/function. The `code.function` attribute may be used to store the latter (e.g., method actually executing the call on the server side, RPC client stub method on the client side).
     *
     * @example exampleMethod
     */
    public const RPC_METHOD = 'rpc.method';

    /**
     * The full (logical) name of the service being called, including its package name, if applicable.
     *
     * This is the logical name of the service from the RPC interface perspective, which can be different from the name of any implementing class. The `code.namespace` attribute may be used to store the latter (despite the attribute name, it may include a class name; e.g., class with method actually executing the call on the server side, RPC client stub class on the client side).
     *
     * @example myservice.EchoService
     */
    public const RPC_SERVICE = 'rpc.service';

    /**
     * A string identifying the remoting system. See below for a list of well-known identifiers.
     */
    public const RPC_SYSTEM = 'rpc.system';

    /**
     * Server domain name if available without reverse DNS lookup; otherwise, IP address or Unix domain socket name.
     *
     * When observed from the client side, and when communicating through an intermediary, `server.address` SHOULD represent the server address behind any intermediaries, for example proxies, if it's available.
     *
     * @example example.com
     * @example 10.1.2.80
     * @example /tmp/my.sock
     */
    public const SERVER_ADDRESS = 'server.address';

    /**
     * Server port number.
     *
     * When observed from the client side, and when communicating through an intermediary, `server.port` SHOULD represent the server port behind any intermediaries, for example proxies, if it's available.
     *
     * @example 80
     * @example 8080
     * @example 443
     */
    public const SERVER_PORT = 'server.port';

    /**
     * The string ID of the service instance.
     *
     * MUST be unique for each instance of the same `service.namespace,service.name` pair (in other words
     * `service.namespace,service.name,service.instance.id` triplet MUST be globally unique). The ID helps to
     * distinguish instances of the same service that exist at the same time (e.g. instances of a horizontally scaled
     * service).Implementations, such as SDKs, are recommended to generate a random Version 1 or Version 4 RFC
     * 4122 UUID, but are free to use an inherent unique ID as the source of
     * this value if stability is desirable. In that case, the ID SHOULD be used as source of a UUID Version 5 and
     * SHOULD use the following UUID as the namespace: `4d63009a-8d0f-11ee-aad7-4c796ed8e320`.UUIDs are typically recommended, as only an opaque value for the purposes of identifying a service instance is
     * needed. Similar to what can be seen in the man page for the
     * `/etc/machine-id` file, the underlying
     * data, such as pod name and namespace should be treated as confidential, being the user's choice to expose it
     * or not via another resource attribute.For applications running behind an application server (like unicorn), we do not recommend using one identifier
     * for all processes participating in the application. Instead, it's recommended each division (e.g. a worker
     * thread in unicorn) to have its own instance.id.It's not recommended for a Collector to set `service.instance.id` if it can't unambiguously determine the
     * service instance that is generating that telemetry. For instance, creating an UUID based on `pod.name` will
     * likely be wrong, as the Collector might not know from which container within that pod the telemetry originated.
     * However, Collectors can set the `service.instance.id` if they can unambiguously determine the service instance
     * for that telemetry. This is typically the case for scraping receivers, as they know the target address and
     * port.
     *
     * @example 627cc493-f310-47de-96bd-71410b7dec09
     */
    public const SERVICE_INSTANCE_ID = 'service.instance.id';

    /**
     * Logical name of the service.
     *
     * MUST be the same for all instances of horizontally scaled services. If the value was not specified, SDKs MUST fallback to `unknown_service:` concatenated with `process.executable.name`, e.g. `unknown_service:bash`. If `process.executable.name` is not available, the value MUST be set to `unknown_service`.
     *
     * @example shoppingcart
     */
    public const SERVICE_NAME = 'service.name';

    /**
     * A namespace for `service.name`.
     *
     * A string value having a meaning that helps to distinguish a group of services, for example the team name that owns a group of services. `service.name` is expected to be unique within the same namespace. If `service.namespace` is not specified in the Resource then `service.name` is expected to be unique for all services that have no explicit namespace defined (so the empty/unspecified namespace is simply one more valid namespace). Zero-length namespace string is assumed equal to unspecified namespace.
     *
     * @example Shop
     */
    public const SERVICE_NAMESPACE = 'service.namespace';

    /**
     * The version string of the service API or implementation. The format is not defined by these conventions.
     *
     * @example 2.0.0
     * @example a01dbef8a
     */
    public const SERVICE_VERSION = 'service.version';

    /**
     * A unique id to identify a session.
     *
     * @example 00112233-4455-6677-8899-aabbccddeeff
     */
    public const SESSION_ID = 'session.id';

    /**
     * The previous `session.id` for this user, when known.
     *
     * @example 00112233-4455-6677-8899-aabbccddeeff
     */
    public const SESSION_PREVIOUS_ID = 'session.previous_id';

    /**
     * SignalR HTTP connection closure status.
     *
     * @example app_shutdown
     * @example timeout
     */
    public const SIGNALR_CONNECTION_STATUS = 'signalr.connection.status';

    /**
     * SignalR transport type.
     *
     * @example web_sockets
     * @example long_polling
     */
    public const SIGNALR_TRANSPORT = 'signalr.transport';

    /**
     * Source address - domain name if available without reverse DNS lookup; otherwise, IP address or Unix domain socket name.
     *
     * When observed from the destination side, and when communicating through an intermediary, `source.address` SHOULD represent the source address behind any intermediaries, for example proxies, if it's available.
     *
     * @example source.example.com
     * @example 10.1.2.80
     * @example /tmp/my.sock
     */
    public const SOURCE_ADDRESS = 'source.address';

    /**
     * Source port number.
     *
     * @example 3389
     * @example 2888
     */
    public const SOURCE_PORT = 'source.port';

    /**
     * The state of a connection in the pool.
     *
     * @example idle
     */
    public const STATE = 'state';

    /**
     * The logical CPU number [0..n-1].
     *
     * @example 1
     */
    public const SYSTEM_CPU_LOGICAL_NUMBER = 'system.cpu.logical_number';

    /**
     * The CPU state for this data point. A system's CPU SHOULD be characterized <em>either</em> by data points with no `state` labels, <em>or only</em> data points with `state` labels.
     *
     * @example idle
     * @example interrupt
     */
    public const SYSTEM_CPU_STATE = 'system.cpu.state';

    /**
     * The device identifier.
     *
     * @example (identifier)
     */
    public const SYSTEM_DEVICE = 'system.device';

    /**
     * The filesystem mode.
     *
     * @example rw, ro
     */
    public const SYSTEM_FILESYSTEM_MODE = 'system.filesystem.mode';

    /**
     * The filesystem mount path.
     *
     * @example /mnt/data
     */
    public const SYSTEM_FILESYSTEM_MOUNTPOINT = 'system.filesystem.mountpoint';

    /**
     * The filesystem state.
     *
     * @example used
     */
    public const SYSTEM_FILESYSTEM_STATE = 'system.filesystem.state';

    /**
     * The filesystem type.
     *
     * @example ext4
     */
    public const SYSTEM_FILESYSTEM_TYPE = 'system.filesystem.type';

    /**
     * The memory state.
     *
     * @example free
     * @example cached
     */
    public const SYSTEM_MEMORY_STATE = 'system.memory.state';

    /**
     * A stateless protocol MUST NOT set this attribute.
     *
     * @example close_wait
     */
    public const SYSTEM_NETWORK_STATE = 'system.network.state';

    /**
     * The paging access direction.
     *
     * @example in
     */
    public const SYSTEM_PAGING_DIRECTION = 'system.paging.direction';

    /**
     * The memory paging state.
     *
     * @example free
     */
    public const SYSTEM_PAGING_STATE = 'system.paging.state';

    /**
     * The memory paging type.
     *
     * @example minor
     */
    public const SYSTEM_PAGING_TYPE = 'system.paging.type';

    /**
     * The process state, e.g., Linux Process State Codes.
     *
     * @example running
     */
    public const SYSTEM_PROCESS_STATUS = 'system.process.status';

    /**
     * Deprecated, use `system.process.status` instead.
     *
     * @deprecated Replaced by `system.process.status`.
     *
     * @example running
     */
    public const SYSTEM_PROCESSES_STATUS = 'system.processes.status';

    /**
     * The name of the auto instrumentation agent or distribution, if used.
     *
     * Official auto instrumentation agents and distributions SHOULD set the `telemetry.distro.name` attribute to
     * a string starting with `opentelemetry-`, e.g. `opentelemetry-java-instrumentation`.
     *
     * @example parts-unlimited-java
     */
    public const TELEMETRY_DISTRO_NAME = 'telemetry.distro.name';

    /**
     * The version string of the auto instrumentation agent or distribution, if used.
     *
     * @example 1.2.3
     */
    public const TELEMETRY_DISTRO_VERSION = 'telemetry.distro.version';

    /**
     * The language of the telemetry SDK.
     */
    public const TELEMETRY_SDK_LANGUAGE = 'telemetry.sdk.language';

    /**
     * The name of the telemetry SDK as defined above.
     *
     * The OpenTelemetry SDK MUST set the `telemetry.sdk.name` attribute to `opentelemetry`.
     * If another SDK, like a fork or a vendor-provided implementation, is used, this SDK MUST set the
     * `telemetry.sdk.name` attribute to the fully-qualified class or module name of this SDK's main entry point
     * or another suitable identifier depending on the language.
     * The identifier `opentelemetry` is reserved and MUST NOT be used in this case.
     * All custom identifiers SHOULD be stable across different versions of an implementation.
     *
     * @example opentelemetry
     */
    public const TELEMETRY_SDK_NAME = 'telemetry.sdk.name';

    /**
     * The version string of the telemetry SDK.
     *
     * @example 1.2.3
     */
    public const TELEMETRY_SDK_VERSION = 'telemetry.sdk.version';

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
     * String indicating the cipher used during the current connection.
     *
     * The values allowed for `tls.cipher` MUST be one of the `Descriptions` of the registered TLS Cipher Suits.
     *
     * @example TLS_RSA_WITH_3DES_EDE_CBC_SHA
     * @example TLS_ECDHE_RSA_WITH_AES_128_CBC_SHA256
     */
    public const TLS_CIPHER = 'tls.cipher';

    /**
     * PEM-encoded stand-alone certificate offered by the client. This is usually mutually-exclusive of `client.certificate_chain` since this value also exists in that list.
     *
     * @example MII...
     */
    public const TLS_CLIENT_CERTIFICATE = 'tls.client.certificate';

    /**
     * Array of PEM-encoded certificates that make up the certificate chain offered by the client. This is usually mutually-exclusive of `client.certificate` since that value should be the first certificate in the chain.
     *
     * @example MII...
     * @example MI...
     */
    public const TLS_CLIENT_CERTIFICATE_CHAIN = 'tls.client.certificate_chain';

    /**
     * Certificate fingerprint using the MD5 digest of DER-encoded version of certificate offered by the client. For consistency with other hash values, this value should be formatted as an uppercase hash.
     *
     * @example 0F76C7F2C55BFD7D8E8B8F4BFBF0C9EC
     */
    public const TLS_CLIENT_HASH_MD5 = 'tls.client.hash.md5';

    /**
     * Certificate fingerprint using the SHA1 digest of DER-encoded version of certificate offered by the client. For consistency with other hash values, this value should be formatted as an uppercase hash.
     *
     * @example 9E393D93138888D288266C2D915214D1D1CCEB2A
     */
    public const TLS_CLIENT_HASH_SHA1 = 'tls.client.hash.sha1';

    /**
     * Certificate fingerprint using the SHA256 digest of DER-encoded version of certificate offered by the client. For consistency with other hash values, this value should be formatted as an uppercase hash.
     *
     * @example 0687F666A054EF17A08E2F2162EAB4CBC0D265E1D7875BE74BF3C712CA92DAF0
     */
    public const TLS_CLIENT_HASH_SHA256 = 'tls.client.hash.sha256';

    /**
     * Distinguished name of subject of the issuer of the x.509 certificate presented by the client.
     *
     * @example CN=Example Root CA, OU=Infrastructure Team, DC=example, DC=com
     */
    public const TLS_CLIENT_ISSUER = 'tls.client.issuer';

    /**
     * A hash that identifies clients based on how they perform an SSL/TLS handshake.
     *
     * @example d4e5b18d6b55c71272893221c96ba240
     */
    public const TLS_CLIENT_JA3 = 'tls.client.ja3';

    /**
     * Date/Time indicating when client certificate is no longer considered valid.
     *
     * @example 2021-01-01T00:00:00.000Z
     */
    public const TLS_CLIENT_NOT_AFTER = 'tls.client.not_after';

    /**
     * Date/Time indicating when client certificate is first considered valid.
     *
     * @example 1970-01-01T00:00:00.000Z
     */
    public const TLS_CLIENT_NOT_BEFORE = 'tls.client.not_before';

    /**
     * Also called an SNI, this tells the server which hostname to which the client is attempting to connect to.
     *
     * @example opentelemetry.io
     */
    public const TLS_CLIENT_SERVER_NAME = 'tls.client.server_name';

    /**
     * Distinguished name of subject of the x.509 certificate presented by the client.
     *
     * @example CN=myclient, OU=Documentation Team, DC=example, DC=com
     */
    public const TLS_CLIENT_SUBJECT = 'tls.client.subject';

    /**
     * Array of ciphers offered by the client during the client hello.
     *
     * @example "TLS_ECDHE_RSA_WITH_AES_256_GCM_SHA384", "TLS_ECDHE_ECDSA_WITH_AES_256_GCM_SHA384", "..."
     */
    public const TLS_CLIENT_SUPPORTED_CIPHERS = 'tls.client.supported_ciphers';

    /**
     * String indicating the curve used for the given cipher, when applicable.
     *
     * @example secp256r1
     */
    public const TLS_CURVE = 'tls.curve';

    /**
     * Boolean flag indicating if the TLS negotiation was successful and transitioned to an encrypted tunnel.
     *
     * @example True
     */
    public const TLS_ESTABLISHED = 'tls.established';

    /**
     * String indicating the protocol being tunneled. Per the values in the IANA registry, this string should be lower case.
     *
     * @example http/1.1
     */
    public const TLS_NEXT_PROTOCOL = 'tls.next_protocol';

    /**
     * Normalized lowercase protocol name parsed from original string of the negotiated SSL/TLS protocol version.
     */
    public const TLS_PROTOCOL_NAME = 'tls.protocol.name';

    /**
     * Numeric part of the version parsed from the original string of the negotiated SSL/TLS protocol version.
     *
     * @example 1.2
     * @example 3
     */
    public const TLS_PROTOCOL_VERSION = 'tls.protocol.version';

    /**
     * Boolean flag indicating if this TLS connection was resumed from an existing TLS negotiation.
     *
     * @example True
     */
    public const TLS_RESUMED = 'tls.resumed';

    /**
     * PEM-encoded stand-alone certificate offered by the server. This is usually mutually-exclusive of `server.certificate_chain` since this value also exists in that list.
     *
     * @example MII...
     */
    public const TLS_SERVER_CERTIFICATE = 'tls.server.certificate';

    /**
     * Array of PEM-encoded certificates that make up the certificate chain offered by the server. This is usually mutually-exclusive of `server.certificate` since that value should be the first certificate in the chain.
     *
     * @example MII...
     * @example MI...
     */
    public const TLS_SERVER_CERTIFICATE_CHAIN = 'tls.server.certificate_chain';

    /**
     * Certificate fingerprint using the MD5 digest of DER-encoded version of certificate offered by the server. For consistency with other hash values, this value should be formatted as an uppercase hash.
     *
     * @example 0F76C7F2C55BFD7D8E8B8F4BFBF0C9EC
     */
    public const TLS_SERVER_HASH_MD5 = 'tls.server.hash.md5';

    /**
     * Certificate fingerprint using the SHA1 digest of DER-encoded version of certificate offered by the server. For consistency with other hash values, this value should be formatted as an uppercase hash.
     *
     * @example 9E393D93138888D288266C2D915214D1D1CCEB2A
     */
    public const TLS_SERVER_HASH_SHA1 = 'tls.server.hash.sha1';

    /**
     * Certificate fingerprint using the SHA256 digest of DER-encoded version of certificate offered by the server. For consistency with other hash values, this value should be formatted as an uppercase hash.
     *
     * @example 0687F666A054EF17A08E2F2162EAB4CBC0D265E1D7875BE74BF3C712CA92DAF0
     */
    public const TLS_SERVER_HASH_SHA256 = 'tls.server.hash.sha256';

    /**
     * Distinguished name of subject of the issuer of the x.509 certificate presented by the client.
     *
     * @example CN=Example Root CA, OU=Infrastructure Team, DC=example, DC=com
     */
    public const TLS_SERVER_ISSUER = 'tls.server.issuer';

    /**
     * A hash that identifies servers based on how they perform an SSL/TLS handshake.
     *
     * @example d4e5b18d6b55c71272893221c96ba240
     */
    public const TLS_SERVER_JA3S = 'tls.server.ja3s';

    /**
     * Date/Time indicating when server certificate is no longer considered valid.
     *
     * @example 2021-01-01T00:00:00.000Z
     */
    public const TLS_SERVER_NOT_AFTER = 'tls.server.not_after';

    /**
     * Date/Time indicating when server certificate is first considered valid.
     *
     * @example 1970-01-01T00:00:00.000Z
     */
    public const TLS_SERVER_NOT_BEFORE = 'tls.server.not_before';

    /**
     * Distinguished name of subject of the x.509 certificate presented by the server.
     *
     * @example CN=myserver, OU=Documentation Team, DC=example, DC=com
     */
    public const TLS_SERVER_SUBJECT = 'tls.server.subject';

    /**
     * Domain extracted from the `url.full`, such as &quot;opentelemetry.io&quot;.
     *
     * In some cases a URL may refer to an IP and/or port directly, without a domain name. In this case, the IP address would go to the domain field. If the URL contains a literal IPv6 address enclosed by `[` and `]`, the `[` and `]` characters should also be captured in the domain field.
     *
     * @example www.foo.bar
     * @example opentelemetry.io
     * @example 3.12.167.2
     * @example [1080:0:0:0:8:800:200C:417A]
     */
    public const URL_DOMAIN = 'url.domain';

    /**
     * The file extension extracted from the `url.full`, excluding the leading dot.
     *
     * The file extension is only set if it exists, as not every url has a file extension. When the file name has multiple extensions `example.tar.gz`, only the last one should be captured `gz`, not `tar.gz`.
     *
     * @example png
     * @example gz
     */
    public const URL_EXTENSION = 'url.extension';

    /**
     * The URI fragment component.
     *
     * @example SemConv
     */
    public const URL_FRAGMENT = 'url.fragment';

    /**
     * Absolute URL describing a network resource according to RFC3986.
     *
     * For network calls, URL usually has `scheme://host[:port][path][?query][#fragment]` format, where the fragment is not transmitted over HTTP, but if it is known, it SHOULD be included nevertheless.
     * `url.full` MUST NOT contain credentials passed via URL in form of `https://username:password@www.example.com/`. In such case username and password SHOULD be redacted and attribute's value SHOULD be `https://REDACTED:REDACTED@www.example.com/`.
     * `url.full` SHOULD capture the absolute URL when it is available (or can be reconstructed). Sensitive content provided in `url.full` SHOULD be scrubbed when instrumentations can identify it.
     *
     * @example https://www.foo.bar/search?q=OpenTelemetry#SemConv
     * @example //localhost
     */
    public const URL_FULL = 'url.full';

    /**
     * Unmodified original URL as seen in the event source.
     *
     * In network monitoring, the observed URL may be a full URL, whereas in access logs, the URL is often just represented as a path. This field is meant to represent the URL as it was observed, complete or not.
     * `url.original` might contain credentials passed via URL in form of `https://username:password@www.example.com/`. In such case password and username SHOULD NOT be redacted and attribute's value SHOULD remain the same.
     *
     * @example https://www.foo.bar/search?q=OpenTelemetry#SemConv
     * @example search?q=OpenTelemetry
     */
    public const URL_ORIGINAL = 'url.original';

    /**
     * The URI path component.
     *
     * Sensitive content provided in `url.path` SHOULD be scrubbed when instrumentations can identify it.
     *
     * @example /search
     */
    public const URL_PATH = 'url.path';

    /**
     * Port extracted from the `url.full`.
     *
     * @example 443
     */
    public const URL_PORT = 'url.port';

    /**
     * The URI query component.
     *
     * Sensitive content provided in `url.query` SHOULD be scrubbed when instrumentations can identify it.
     *
     * @example q=OpenTelemetry
     */
    public const URL_QUERY = 'url.query';

    /**
     * The highest registered url domain, stripped of the subdomain.
     *
     * This value can be determined precisely with the public suffix list. For example, the registered domain for `foo.example.com` is `example.com`. Trying to approximate this by simply taking the last two labels will not work well for TLDs such as `co.uk`.
     *
     * @example example.com
     * @example foo.co.uk
     */
    public const URL_REGISTERED_DOMAIN = 'url.registered_domain';

    /**
     * The URI scheme component identifying the used protocol.
     *
     * @example http
     * @example https
     */
    public const URL_SCHEME = 'url.scheme';

    /**
     * The subdomain portion of a fully qualified domain name includes all of the names except the host name under the registered_domain. In a partially qualified domain, or if the qualification level of the full name cannot be determined, subdomain contains all of the names below the registered domain.
     *
     * The subdomain portion of `www.east.mydomain.co.uk` is `east`. If the domain has multiple levels of subdomain, such as `sub2.sub1.example.com`, the subdomain field should contain `sub2.sub1`, with no trailing period.
     *
     * @example east
     * @example sub2.sub1
     */
    public const URL_SUBDOMAIN = 'url.subdomain';

    /**
     * The effective top level domain (eTLD), also known as the domain suffix, is the last part of the domain name. For example, the top level domain for example.com is `com`.
     *
     * This value can be determined precisely with the public suffix list.
     *
     * @example com
     * @example co.uk
     */
    public const URL_TOP_LEVEL_DOMAIN = 'url.top_level_domain';

    /**
     * Name of the user-agent extracted from original. Usually refers to the browser's name.
     *
     * Example of extracting browser's name from original string. In the case of using a user-agent for non-browser products, such as microservices with multiple names/versions inside the `user_agent.original`, the most significant name SHOULD be selected. In such a scenario it should align with `user_agent.version`
     *
     * @example Safari
     * @example YourApp
     */
    public const USER_AGENT_NAME = 'user_agent.name';

    /**
     * Value of the HTTP User-Agent header sent by the client.
     *
     * @example CERN-LineMode/2.15 libwww/2.17b3
     * @example Mozilla/5.0 (iPhone; CPU iPhone OS 14_7_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.1.2 Mobile/15E148 Safari/604.1
     * @example YourApp/1.0.0 grpc-java-okhttp/1.27.2
     */
    public const USER_AGENT_ORIGINAL = 'user_agent.original';

    /**
     * Version of the user-agent extracted from original. Usually refers to the browser's version.
     *
     * Example of extracting browser's version from original string. In the case of using a user-agent for non-browser products, such as microservices with multiple names/versions inside the `user_agent.original`, the most significant version SHOULD be selected. In such a scenario it should align with `user_agent.name`
     *
     * @example 14.1.2
     * @example 1.0.0
     */
    public const USER_AGENT_VERSION = 'user_agent.version';

    /**
     * @deprecated
     */
    public const FAAS_EXECUTION = 'faas.execution';

    /**
     * @deprecated
     */
    public const HTTP_HOST = 'http.host';

    /**
     * @deprecated
     */
    public const HTTP_REQUEST_CONTENT_LENGTH_UNCOMPRESSED = 'http.request_content_length_uncompressed';

    /**
     * @deprecated
     */
    public const HTTP_RESPONSE_CONTENT_LENGTH_UNCOMPRESSED = 'http.response_content_length_uncompressed';

    /**
     * @deprecated
     */
    public const HTTP_RETRY_COUNT = 'http.retry_count';

    /**
     * @deprecated
     */
    public const HTTP_SERVER_NAME = 'http.server_name';

    /**
     * @deprecated
     */
    public const MESSAGING_CONVERSATION_ID = 'messaging.conversation_id';

    /**
     * @deprecated
     */
    public const MESSAGING_DESTINATION = 'messaging.destination';

    /**
     * @deprecated
     */
    public const MESSAGING_KAFKA_PARTITION = 'messaging.kafka.partition';

    /**
     * @deprecated
     */
    public const MESSAGING_KAFKA_TOMBSTONE = 'messaging.kafka.tombstone';

    /**
     * @deprecated
     */
    public const MESSAGING_PROTOCOL = 'messaging.protocol';

    /**
     * @deprecated
     */
    public const MESSAGING_PROTOCOL_VERSION = 'messaging.protocol_version';

    /**
     * @deprecated
     */
    public const MESSAGING_RABBITMQ_ROUTING_KEY = 'messaging.rabbitmq.routing_key';

    /**
     * @deprecated
     */
    public const MESSAGING_TEMP_DESTINATION = 'messaging.temp_destination';

    /**
     * @deprecated
     */
    public const MESSAGING_URL = 'messaging.url';

    /**
     * @deprecated
     */
    public const NET_HOST_IP = 'net.host.ip';

    /**
     * @deprecated
     */
    public const NET_PEER_IP = 'net.peer.ip';

    /**
     * @deprecated
     */
    public const HTTP_CLIENT_IP = 'http.client_ip';

    /**
     * @deprecated
     */
    public const MESSAGING_CONSUMER_ID = 'messaging.consumer.id';

    /**
     * @deprecated
     */
    public const MESSAGING_DESTINATION_KIND = 'messaging.destination.kind';

    /**
     * @deprecated
     */
    public const MESSAGING_KAFKA_CLIENT_ID = 'messaging.kafka.client_id';

    /**
     * @deprecated
     */
    public const MESSAGING_KAFKA_SOURCE_PARTITION = 'messaging.kafka.source.partition';

    /**
     * @deprecated
     */
    public const MESSAGING_MESSAGE_PAYLOAD_COMPRESSED_SIZE_BYTES = 'messaging.message.payload_compressed_size_bytes';

    /**
     * @deprecated
     */
    public const MESSAGING_MESSAGE_PAYLOAD_SIZE_BYTES = 'messaging.message.payload_size_bytes';

    /**
     * @deprecated
     */
    public const MESSAGING_ROCKETMQ_CLIENT_ID = 'messaging.rocketmq.client_id';

    /**
     * @deprecated
     */
    public const MESSAGING_SOURCE_ANONYMOUS = 'messaging.source.anonymous';

    /**
     * @deprecated
     */
    public const MESSAGING_SOURCE_KIND = 'messaging.source.kind';

    /**
     * @deprecated
     */
    public const MESSAGING_SOURCE_NAME = 'messaging.source.name';

    /**
     * @deprecated
     */
    public const MESSAGING_SOURCE_TEMPLATE = 'messaging.source.template';

    /**
     * @deprecated
     */
    public const MESSAGING_SOURCE_TEMPORARY = 'messaging.source.temporary';

    /**
     * @deprecated
     */
    public const NET_APP_PROTOCOL_NAME = 'net.app.protocol.name';

    /**
     * @deprecated
     */
    public const NET_APP_PROTOCOL_VERSION = 'net.app.protocol.version';

    /**
     * @deprecated
     */
    public const NET_HOST_CARRIER_ICC = 'net.host.carrier.icc';

    /**
     * @deprecated
     */
    public const NET_HOST_CARRIER_MCC = 'net.host.carrier.mcc';

    /**
     * @deprecated
     */
    public const NET_HOST_CARRIER_MNC = 'net.host.carrier.mnc';

    /**
     * @deprecated
     */
    public const NET_HOST_CARRIER_NAME = 'net.host.carrier.name';

    /**
     * @deprecated
     */
    public const NET_HOST_CONNECTION_SUBTYPE = 'net.host.connection.subtype';

    /**
     * @deprecated
     */
    public const NET_HOST_CONNECTION_TYPE = 'net.host.connection.type';

    /**
     * @deprecated
     */
    public const HTTP_RESEND_COUNT = 'http.resend_count';

    /**
     * @deprecated
     */
    public const THREAD_DAEMON = 'thread.daemon';

    /**
     * @deprecated
     */
    public const EVENT_DOMAIN = 'event.domain';

    /**
     * @deprecated
     */
    public const SYSTEM_DISK_DIRECTION = 'system.disk.direction';

    /**
     * @deprecated
     */
    public const SYSTEM_NETWORK_DIRECTION = 'system.network.direction';
}
