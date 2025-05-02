<?php

// DO NOT EDIT, this is an Auto-generated file from script/semantic-conventions

declare(strict_types=1);

namespace OpenTelemetry\SemConv;

interface TraceAttributes
{
    /**
     * The URL of the OpenTelemetry schema for these keys and values.
     */
    public const SCHEMA_URL = 'https://opentelemetry.io/schemas/1.32.0';

    /**
     * A unique identifier representing the installation of an application on a specific device
     *
     * Its value SHOULD persist across launches of the same application installation, including through application upgrades.
     * It SHOULD change if the application is uninstalled or if all applications of the vendor are uninstalled.
     * Additionally, users might be able to reset this value (e.g. by clearing application data).
     * If an app is installed multiple times on the same device (e.g. in different accounts on Android), each `app.installation.id` SHOULD have a different value.
     * If multiple OpenTelemetry SDKs are used within the same application, they SHOULD use the same value for `app.installation.id`.
     * Hardware IDs (e.g. serial number, IMEI, MAC address) MUST NOT be used as the `app.installation.id`.
     *
     * For iOS, this value SHOULD be equal to the [vendor identifier](https://developer.apple.com/documentation/uikit/uidevice/identifierforvendor).
     *
     * For Android, examples of `app.installation.id` implementations include:
     *
     * - [Firebase Installation ID](https://firebase.google.com/docs/projects/manage-installations).
     * - A globally unique UUID which is persisted across sessions in your application.
     * - [App set ID](https://developer.android.com/identity/app-set-id).
     * - [`Settings.getString(Settings.Secure.ANDROID_ID)`](https://developer.android.com/reference/android/provider/Settings.Secure#ANDROID_ID).
     *
     * More information about Android identifier best practices can be found [here](https://developer.android.com/training/articles/user-data-ids).
     */
    public const APP_INSTALLATION_ID = 'app.installation.id';

    /**
     * The x (horizontal) coordinate of a screen coordinate, in screen pixels.
     */
    public const APP_SCREEN_COORDINATE_X = 'app.screen.coordinate.x';

    /**
     * The y (vertical) component of a screen coordinate, in screen pixels.
     */
    public const APP_SCREEN_COORDINATE_Y = 'app.screen.coordinate.y';

    /**
     * An identifier that uniquely differentiates this widget from other widgets in the same application.
     *
     * A widget is an application component, typically an on-screen visual GUI element.
     */
    public const APP_WIDGET_ID = 'app.widget.id';

    /**
     * The name of an application widget.
     * A widget is an application component, typically an on-screen visual GUI element.
     */
    public const APP_WIDGET_NAME = 'app.widget.name';

    /**
     * The provenance filename of the built attestation which directly relates to the build artifact filename. This filename SHOULD accompany the artifact at publish time. See the [SLSA Relationship](https://slsa.dev/spec/v1.0/distributing-provenance#relationship-between-artifacts-and-attestations) specification for more information.
     */
    public const ARTIFACT_ATTESTATION_FILENAME = 'artifact.attestation.filename';

    /**
     * The full [hash value (see glossary)](https://nvlpubs.nist.gov/nistpubs/FIPS/NIST.FIPS.186-5.pdf), of the built attestation. Some envelopes in the [software attestation space](https://github.com/in-toto/attestation/tree/main/spec) also refer to this as the **digest**.
     */
    public const ARTIFACT_ATTESTATION_HASH = 'artifact.attestation.hash';

    /**
     * The id of the build [software attestation](https://slsa.dev/attestation-model).
     */
    public const ARTIFACT_ATTESTATION_ID = 'artifact.attestation.id';

    /**
     * The human readable file name of the artifact, typically generated during build and release processes. Often includes the package name and version in the file name.
     *
     * This file name can also act as the [Package Name](https://slsa.dev/spec/v1.0/terminology#package-model)
     * in cases where the package ecosystem maps accordingly.
     * Additionally, the artifact [can be published](https://slsa.dev/spec/v1.0/terminology#software-supply-chain)
     * for others, but that is not a guarantee.
     */
    public const ARTIFACT_FILENAME = 'artifact.filename';

    /**
     * The full [hash value (see glossary)](https://nvlpubs.nist.gov/nistpubs/FIPS/NIST.FIPS.186-5.pdf), often found in checksum.txt on a release of the artifact and used to verify package integrity.
     *
     * The specific algorithm used to create the cryptographic hash value is
     * not defined. In situations where an artifact has multiple
     * cryptographic hashes, it is up to the implementer to choose which
     * hash value to set here; this should be the most secure hash algorithm
     * that is suitable for the situation and consistent with the
     * corresponding attestation. The implementer can then provide the other
     * hash values through an additional set of attribute extensions as they
     * deem necessary.
     */
    public const ARTIFACT_HASH = 'artifact.hash';

    /**
     * The [Package URL](https://github.com/package-url/purl-spec) of the [package artifact](https://slsa.dev/spec/v1.0/terminology#package-model) provides a standard way to identify and locate the packaged artifact.
     */
    public const ARTIFACT_PURL = 'artifact.purl';

    /**
     * The version of the artifact.
     */
    public const ARTIFACT_VERSION = 'artifact.version';

    /**
     * ASP.NET Core exception middleware handling result
     */
    public const ASPNETCORE_DIAGNOSTICS_EXCEPTION_RESULT = 'aspnetcore.diagnostics.exception.result';

    /**
     * Full type name of the [`IExceptionHandler`](https://learn.microsoft.com/dotnet/api/microsoft.aspnetcore.diagnostics.iexceptionhandler) implementation that handled the exception.
     */
    public const ASPNETCORE_DIAGNOSTICS_HANDLER_TYPE = 'aspnetcore.diagnostics.handler.type';

    /**
     * Rate limiting policy name.
     */
    public const ASPNETCORE_RATE_LIMITING_POLICY = 'aspnetcore.rate_limiting.policy';

    /**
     * Rate-limiting result, shows whether the lease was acquired or contains a rejection reason
     */
    public const ASPNETCORE_RATE_LIMITING_RESULT = 'aspnetcore.rate_limiting.result';

    /**
     * Flag indicating if request was handled by the application pipeline.
     */
    public const ASPNETCORE_REQUEST_IS_UNHANDLED = 'aspnetcore.request.is_unhandled';

    /**
     * A value that indicates whether the matched route is a fallback route.
     */
    public const ASPNETCORE_ROUTING_IS_FALLBACK = 'aspnetcore.routing.is_fallback';

    /**
     * Match result - success or failure
     */
    public const ASPNETCORE_ROUTING_MATCH_STATUS = 'aspnetcore.routing.match_status';

    /**
     * The JSON-serialized value of each item in the `AttributeDefinitions` request field.
     */
    public const AWS_DYNAMODB_ATTRIBUTE_DEFINITIONS = 'aws.dynamodb.attribute_definitions';

    /**
     * The value of the `AttributesToGet` request parameter.
     */
    public const AWS_DYNAMODB_ATTRIBUTES_TO_GET = 'aws.dynamodb.attributes_to_get';

    /**
     * The value of the `ConsistentRead` request parameter.
     */
    public const AWS_DYNAMODB_CONSISTENT_READ = 'aws.dynamodb.consistent_read';

    /**
     * The JSON-serialized value of each item in the `ConsumedCapacity` response field.
     */
    public const AWS_DYNAMODB_CONSUMED_CAPACITY = 'aws.dynamodb.consumed_capacity';

    /**
     * The value of the `Count` response parameter.
     */
    public const AWS_DYNAMODB_COUNT = 'aws.dynamodb.count';

    /**
     * The value of the `ExclusiveStartTableName` request parameter.
     */
    public const AWS_DYNAMODB_EXCLUSIVE_START_TABLE = 'aws.dynamodb.exclusive_start_table';

    /**
     * The JSON-serialized value of each item in the `GlobalSecondaryIndexUpdates` request field.
     */
    public const AWS_DYNAMODB_GLOBAL_SECONDARY_INDEX_UPDATES = 'aws.dynamodb.global_secondary_index_updates';

    /**
     * The JSON-serialized value of each item of the `GlobalSecondaryIndexes` request field
     */
    public const AWS_DYNAMODB_GLOBAL_SECONDARY_INDEXES = 'aws.dynamodb.global_secondary_indexes';

    /**
     * The value of the `IndexName` request parameter.
     */
    public const AWS_DYNAMODB_INDEX_NAME = 'aws.dynamodb.index_name';

    /**
     * The JSON-serialized value of the `ItemCollectionMetrics` response field.
     */
    public const AWS_DYNAMODB_ITEM_COLLECTION_METRICS = 'aws.dynamodb.item_collection_metrics';

    /**
     * The value of the `Limit` request parameter.
     */
    public const AWS_DYNAMODB_LIMIT = 'aws.dynamodb.limit';

    /**
     * The JSON-serialized value of each item of the `LocalSecondaryIndexes` request field.
     */
    public const AWS_DYNAMODB_LOCAL_SECONDARY_INDEXES = 'aws.dynamodb.local_secondary_indexes';

    /**
     * The value of the `ProjectionExpression` request parameter.
     */
    public const AWS_DYNAMODB_PROJECTION = 'aws.dynamodb.projection';

    /**
     * The value of the `ProvisionedThroughput.ReadCapacityUnits` request parameter.
     */
    public const AWS_DYNAMODB_PROVISIONED_READ_CAPACITY = 'aws.dynamodb.provisioned_read_capacity';

    /**
     * The value of the `ProvisionedThroughput.WriteCapacityUnits` request parameter.
     */
    public const AWS_DYNAMODB_PROVISIONED_WRITE_CAPACITY = 'aws.dynamodb.provisioned_write_capacity';

    /**
     * The value of the `ScanIndexForward` request parameter.
     */
    public const AWS_DYNAMODB_SCAN_FORWARD = 'aws.dynamodb.scan_forward';

    /**
     * The value of the `ScannedCount` response parameter.
     */
    public const AWS_DYNAMODB_SCANNED_COUNT = 'aws.dynamodb.scanned_count';

    /**
     * The value of the `Segment` request parameter.
     */
    public const AWS_DYNAMODB_SEGMENT = 'aws.dynamodb.segment';

    /**
     * The value of the `Select` request parameter.
     */
    public const AWS_DYNAMODB_SELECT = 'aws.dynamodb.select';

    /**
     * The number of items in the `TableNames` response parameter.
     */
    public const AWS_DYNAMODB_TABLE_COUNT = 'aws.dynamodb.table_count';

    /**
     * The keys in the `RequestItems` object field.
     */
    public const AWS_DYNAMODB_TABLE_NAMES = 'aws.dynamodb.table_names';

    /**
     * The value of the `TotalSegments` request parameter.
     */
    public const AWS_DYNAMODB_TOTAL_SEGMENTS = 'aws.dynamodb.total_segments';

    /**
     * The ARN of an [ECS cluster](https://docs.aws.amazon.com/AmazonECS/latest/developerguide/clusters.html).
     */
    public const AWS_ECS_CLUSTER_ARN = 'aws.ecs.cluster.arn';

    /**
     * The Amazon Resource Name (ARN) of an [ECS container instance](https://docs.aws.amazon.com/AmazonECS/latest/developerguide/ECS_instances.html).
     */
    public const AWS_ECS_CONTAINER_ARN = 'aws.ecs.container.arn';

    /**
     * The [launch type](https://docs.aws.amazon.com/AmazonECS/latest/developerguide/launch_types.html) for an ECS task.
     */
    public const AWS_ECS_LAUNCHTYPE = 'aws.ecs.launchtype';

    /**
     * The ARN of a running [ECS task](https://docs.aws.amazon.com/AmazonECS/latest/developerguide/ecs-account-settings.html#ecs-resource-ids).
     */
    public const AWS_ECS_TASK_ARN = 'aws.ecs.task.arn';

    /**
     * The family name of the [ECS task definition](https://docs.aws.amazon.com/AmazonECS/latest/developerguide/task_definitions.html) used to create the ECS task.
     */
    public const AWS_ECS_TASK_FAMILY = 'aws.ecs.task.family';

    /**
     * The ID of a running ECS task. The ID MUST be extracted from `task.arn`.
     */
    public const AWS_ECS_TASK_ID = 'aws.ecs.task.id';

    /**
     * The revision for the task definition used to create the ECS task.
     */
    public const AWS_ECS_TASK_REVISION = 'aws.ecs.task.revision';

    /**
     * The ARN of an EKS cluster.
     */
    public const AWS_EKS_CLUSTER_ARN = 'aws.eks.cluster.arn';

    /**
     * The AWS extended request ID as returned in the response header `x-amz-id-2`.
     */
    public const AWS_EXTENDED_REQUEST_ID = 'aws.extended_request_id';

    /**
     * The full invoked ARN as provided on the `Context` passed to the function (`Lambda-Runtime-Invoked-Function-Arn` header on the `/runtime/invocation/next` applicable).
     *
     * This may be different from `cloud.resource_id` if an alias is involved.
     */
    public const AWS_LAMBDA_INVOKED_ARN = 'aws.lambda.invoked_arn';

    /**
     * The Amazon Resource Name(s) (ARN) of the AWS log group(s).
     *
     * See the [log group ARN format documentation](https://docs.aws.amazon.com/AmazonCloudWatch/latest/logs/iam-access-control-overview-cwl.html#CWL_ARN_Format).
     */
    public const AWS_LOG_GROUP_ARNS = 'aws.log.group.arns';

    /**
     * The name(s) of the AWS log group(s) an application is writing to.
     *
     * Multiple log groups must be supported for cases like multi-container applications, where a single application has sidecar containers, and each write to their own log group.
     */
    public const AWS_LOG_GROUP_NAMES = 'aws.log.group.names';

    /**
     * The ARN(s) of the AWS log stream(s).
     *
     * See the [log stream ARN format documentation](https://docs.aws.amazon.com/AmazonCloudWatch/latest/logs/iam-access-control-overview-cwl.html#CWL_ARN_Format). One log group can contain several log streams, so these ARNs necessarily identify both a log group and a log stream.
     */
    public const AWS_LOG_STREAM_ARNS = 'aws.log.stream.arns';

    /**
     * The name(s) of the AWS log stream(s) an application is writing to.
     */
    public const AWS_LOG_STREAM_NAMES = 'aws.log.stream.names';

    /**
     * The AWS request ID as returned in the response headers `x-amzn-requestid`, `x-amzn-request-id` or `x-amz-request-id`.
     */
    public const AWS_REQUEST_ID = 'aws.request_id';

    /**
     * The S3 bucket name the request refers to. Corresponds to the `--bucket` parameter of the [S3 API](https://docs.aws.amazon.com/cli/latest/reference/s3api/index.html) operations.
     * The `bucket` attribute is applicable to all S3 operations that reference a bucket, i.e. that require the bucket name as a mandatory parameter.
     * This applies to almost all S3 operations except `list-buckets`.
     */
    public const AWS_S3_BUCKET = 'aws.s3.bucket';

    /**
     * The source object (in the form `bucket`/`key`) for the copy operation.
     * The `copy_source` attribute applies to S3 copy operations and corresponds to the `--copy-source` parameter
     * of the [copy-object operation within the S3 API](https://docs.aws.amazon.com/cli/latest/reference/s3api/copy-object.html).
     * This applies in particular to the following operations:
     *
     * - [copy-object](https://docs.aws.amazon.com/cli/latest/reference/s3api/copy-object.html)
     * - [upload-part-copy](https://docs.aws.amazon.com/cli/latest/reference/s3api/upload-part-copy.html)
     */
    public const AWS_S3_COPY_SOURCE = 'aws.s3.copy_source';

    /**
     * The delete request container that specifies the objects to be deleted.
     * The `delete` attribute is only applicable to the [delete-object](https://docs.aws.amazon.com/cli/latest/reference/s3api/delete-object.html) operation.
     * The `delete` attribute corresponds to the `--delete` parameter of the
     * [delete-objects operation within the S3 API](https://docs.aws.amazon.com/cli/latest/reference/s3api/delete-objects.html).
     */
    public const AWS_S3_DELETE = 'aws.s3.delete';

    /**
     * The S3 object key the request refers to. Corresponds to the `--key` parameter of the [S3 API](https://docs.aws.amazon.com/cli/latest/reference/s3api/index.html) operations.
     * The `key` attribute is applicable to all object-related S3 operations, i.e. that require the object key as a mandatory parameter.
     * This applies in particular to the following operations:
     *
     * - [copy-object](https://docs.aws.amazon.com/cli/latest/reference/s3api/copy-object.html)
     * - [delete-object](https://docs.aws.amazon.com/cli/latest/reference/s3api/delete-object.html)
     * - [get-object](https://docs.aws.amazon.com/cli/latest/reference/s3api/get-object.html)
     * - [head-object](https://docs.aws.amazon.com/cli/latest/reference/s3api/head-object.html)
     * - [put-object](https://docs.aws.amazon.com/cli/latest/reference/s3api/put-object.html)
     * - [restore-object](https://docs.aws.amazon.com/cli/latest/reference/s3api/restore-object.html)
     * - [select-object-content](https://docs.aws.amazon.com/cli/latest/reference/s3api/select-object-content.html)
     * - [abort-multipart-upload](https://docs.aws.amazon.com/cli/latest/reference/s3api/abort-multipart-upload.html)
     * - [complete-multipart-upload](https://docs.aws.amazon.com/cli/latest/reference/s3api/complete-multipart-upload.html)
     * - [create-multipart-upload](https://docs.aws.amazon.com/cli/latest/reference/s3api/create-multipart-upload.html)
     * - [list-parts](https://docs.aws.amazon.com/cli/latest/reference/s3api/list-parts.html)
     * - [upload-part](https://docs.aws.amazon.com/cli/latest/reference/s3api/upload-part.html)
     * - [upload-part-copy](https://docs.aws.amazon.com/cli/latest/reference/s3api/upload-part-copy.html)
     */
    public const AWS_S3_KEY = 'aws.s3.key';

    /**
     * The part number of the part being uploaded in a multipart-upload operation. This is a positive integer between 1 and 10,000.
     * The `part_number` attribute is only applicable to the [upload-part](https://docs.aws.amazon.com/cli/latest/reference/s3api/upload-part.html)
     * and [upload-part-copy](https://docs.aws.amazon.com/cli/latest/reference/s3api/upload-part-copy.html) operations.
     * The `part_number` attribute corresponds to the `--part-number` parameter of the
     * [upload-part operation within the S3 API](https://docs.aws.amazon.com/cli/latest/reference/s3api/upload-part.html).
     */
    public const AWS_S3_PART_NUMBER = 'aws.s3.part_number';

    /**
     * Upload ID that identifies the multipart upload.
     * The `upload_id` attribute applies to S3 multipart-upload operations and corresponds to the `--upload-id` parameter
     * of the [S3 API](https://docs.aws.amazon.com/cli/latest/reference/s3api/index.html) multipart operations.
     * This applies in particular to the following operations:
     *
     * - [abort-multipart-upload](https://docs.aws.amazon.com/cli/latest/reference/s3api/abort-multipart-upload.html)
     * - [complete-multipart-upload](https://docs.aws.amazon.com/cli/latest/reference/s3api/complete-multipart-upload.html)
     * - [list-parts](https://docs.aws.amazon.com/cli/latest/reference/s3api/list-parts.html)
     * - [upload-part](https://docs.aws.amazon.com/cli/latest/reference/s3api/upload-part.html)
     * - [upload-part-copy](https://docs.aws.amazon.com/cli/latest/reference/s3api/upload-part-copy.html)
     */
    public const AWS_S3_UPLOAD_ID = 'aws.s3.upload_id';

    /**
     * [Azure Resource Provider Namespace](https://learn.microsoft.com/azure/azure-resource-manager/management/azure-services-resource-providers) as recognized by the client.
     */
    public const AZ_NAMESPACE = 'az.namespace';

    /**
     * The unique identifier of the service request. It's generated by the Azure service and returned with the response.
     */
    public const AZ_SERVICE_REQUEST_ID = 'az.service_request_id';

    /**
     * The unique identifier of the client instance.
     */
    public const AZURE_CLIENT_ID = 'azure.client.id';

    /**
     * Cosmos client connection mode.
     */
    public const AZURE_COSMOSDB_CONNECTION_MODE = 'azure.cosmosdb.connection.mode';

    /**
     * Account or request [consistency level](https://learn.microsoft.com/azure/cosmos-db/consistency-levels).
     */
    public const AZURE_COSMOSDB_CONSISTENCY_LEVEL = 'azure.cosmosdb.consistency.level';

    /**
     * List of regions contacted during operation in the order that they were contacted. If there is more than one region listed, it indicates that the operation was performed on multiple regions i.e. cross-regional call.
     *
     * Region name matches the format of `displayName` in [Azure Location API](https://learn.microsoft.com/rest/api/subscription/subscriptions/list-locations?view=rest-subscription-2021-10-01&tabs=HTTP#location)
     */
    public const AZURE_COSMOSDB_OPERATION_CONTACTED_REGIONS = 'azure.cosmosdb.operation.contacted_regions';

    /**
     * The number of request units consumed by the operation.
     */
    public const AZURE_COSMOSDB_OPERATION_REQUEST_CHARGE = 'azure.cosmosdb.operation.request_charge';

    /**
     * Request payload size in bytes.
     */
    public const AZURE_COSMOSDB_REQUEST_BODY_SIZE = 'azure.cosmosdb.request.body.size';

    /**
     * Cosmos DB sub status code.
     */
    public const AZURE_COSMOSDB_RESPONSE_SUB_STATUS_CODE = 'azure.cosmosdb.response.sub_status_code';

    /**
     * Array of brand name and version separated by a space
     * This value is intended to be taken from the [UA client hints API](https://wicg.github.io/ua-client-hints/#interface) (`navigator.userAgentData.brands`).
     */
    public const BROWSER_BRANDS = 'browser.brands';

    /**
     * Preferred language of the user using the browser
     * This value is intended to be taken from the Navigator API `navigator.language`.
     */
    public const BROWSER_LANGUAGE = 'browser.language';

    /**
     * A boolean that is true if the browser is running on a mobile device
     * This value is intended to be taken from the [UA client hints API](https://wicg.github.io/ua-client-hints/#interface) (`navigator.userAgentData.mobile`). If unavailable, this attribute SHOULD be left unset.
     */
    public const BROWSER_MOBILE = 'browser.mobile';

    /**
     * The platform on which the browser is running
     * This value is intended to be taken from the [UA client hints API](https://wicg.github.io/ua-client-hints/#interface) (`navigator.userAgentData.platform`). If unavailable, the legacy `navigator.platform` API SHOULD NOT be used instead and this attribute SHOULD be left unset in order for the values to be consistent.
     * The list of possible values is defined in the [W3C User-Agent Client Hints specification](https://wicg.github.io/ua-client-hints/#sec-ch-ua-platform). Note that some (but not all) of these values can overlap with values in the [`os.type` and `os.name` attributes](./os.md). However, for consistency, the values in the `browser.platform` attribute should capture the exact value that the user agent provides.
     */
    public const BROWSER_PLATFORM = 'browser.platform';

    /**
     * The consistency level of the query. Based on consistency values from [CQL](https://docs.datastax.com/en/cassandra-oss/3.0/cassandra/dml/dmlConfigConsistency.html).
     */
    public const CASSANDRA_CONSISTENCY_LEVEL = 'cassandra.consistency.level';

    /**
     * The data center of the coordinating node for a query.
     */
    public const CASSANDRA_COORDINATOR_DC = 'cassandra.coordinator.dc';

    /**
     * The ID of the coordinating node for a query.
     */
    public const CASSANDRA_COORDINATOR_ID = 'cassandra.coordinator.id';

    /**
     * The fetch size used for paging, i.e. how many rows will be returned at once.
     */
    public const CASSANDRA_PAGE_SIZE = 'cassandra.page.size';

    /**
     * Whether or not the query is idempotent.
     */
    public const CASSANDRA_QUERY_IDEMPOTENT = 'cassandra.query.idempotent';

    /**
     * The number of times a query was speculatively executed. Not set or `0` if the query was not executed speculatively.
     */
    public const CASSANDRA_SPECULATIVE_EXECUTION_COUNT = 'cassandra.speculative_execution.count';

    /**
     * The human readable name of the pipeline within a CI/CD system.
     */
    public const CICD_PIPELINE_NAME = 'cicd.pipeline.name';

    /**
     * The result of a pipeline run.
     */
    public const CICD_PIPELINE_RESULT = 'cicd.pipeline.result';

    /**
     * The unique identifier of a pipeline run within a CI/CD system.
     */
    public const CICD_PIPELINE_RUN_ID = 'cicd.pipeline.run.id';

    /**
     * The pipeline run goes through these states during its lifecycle.
     */
    public const CICD_PIPELINE_RUN_STATE = 'cicd.pipeline.run.state';

    /**
     * The [URL](https://wikipedia.org/wiki/URL) of the pipeline run, providing the complete address in order to locate and identify the pipeline run.
     */
    public const CICD_PIPELINE_RUN_URL_FULL = 'cicd.pipeline.run.url.full';

    /**
     * The human readable name of a task within a pipeline. Task here most closely aligns with a [computing process](https://wikipedia.org/wiki/Pipeline_(computing)) in a pipeline. Other terms for tasks include commands, steps, and procedures.
     */
    public const CICD_PIPELINE_TASK_NAME = 'cicd.pipeline.task.name';

    /**
     * The unique identifier of a task run within a pipeline.
     */
    public const CICD_PIPELINE_TASK_RUN_ID = 'cicd.pipeline.task.run.id';

    /**
     * The [URL](https://wikipedia.org/wiki/URL) of the pipeline task run, providing the complete address in order to locate and identify the pipeline task run.
     */
    public const CICD_PIPELINE_TASK_RUN_URL_FULL = 'cicd.pipeline.task.run.url.full';

    /**
     * The type of the task within a pipeline.
     */
    public const CICD_PIPELINE_TASK_TYPE = 'cicd.pipeline.task.type';

    /**
     * The name of a component of the CICD system.
     */
    public const CICD_SYSTEM_COMPONENT = 'cicd.system.component';

    /**
     * The state of a CICD worker / agent.
     */
    public const CICD_WORKER_STATE = 'cicd.worker.state';

    /**
     * Client address - domain name if available without reverse DNS lookup; otherwise, IP address or Unix domain socket name.
     * When observed from the server side, and when communicating through an intermediary, `client.address` SHOULD represent the client address behind any intermediaries,  for example proxies, if it's available.
     */
    public const CLIENT_ADDRESS = 'client.address';

    /**
     * Client port number.
     * When observed from the server side, and when communicating through an intermediary, `client.port` SHOULD represent the client port behind any intermediaries,  for example proxies, if it's available.
     */
    public const CLIENT_PORT = 'client.port';

    /**
     * The cloud account ID the resource is assigned to.
     */
    public const CLOUD_ACCOUNT_ID = 'cloud.account.id';

    /**
     * Cloud regions often have multiple, isolated locations known as zones to increase availability. Availability zone represents the zone where the resource is running.
     *
     * Availability zones are called "zones" on Alibaba Cloud and Google Cloud.
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
     * Refer to your provider's docs to see the available regions, for example [Alibaba Cloud regions](https://www.alibabacloud.com/help/doc-detail/40654.htm), [AWS regions](https://aws.amazon.com/about-aws/global-infrastructure/regions_az/), [Azure regions](https://azure.microsoft.com/global-infrastructure/geographies/), [Google Cloud regions](https://cloud.google.com/about/locations), or [Tencent Cloud regions](https://www.tencentcloud.com/document/product/213/6091).
     */
    public const CLOUD_REGION = 'cloud.region';

    /**
     * Cloud provider-specific native identifier of the monitored cloud resource (e.g. an [ARN](https://docs.aws.amazon.com/general/latest/gr/aws-arns-and-namespaces.html) on AWS, a [fully qualified resource ID](https://learn.microsoft.com/rest/api/resources/resources/get-by-id) on Azure, a [full resource name](https://google.aip.dev/122#full-resource-names) on GCP)
     *
     * On some cloud providers, it may not be possible to determine the full ID at startup,
     * so it may be necessary to set `cloud.resource_id` as a span attribute instead.
     *
     * The exact value to use for `cloud.resource_id` depends on the cloud provider.
     * The following well-known definitions MUST be used if you set this attribute and they apply:
     *
     * - **AWS Lambda:** The function [ARN](https://docs.aws.amazon.com/general/latest/gr/aws-arns-and-namespaces.html).
     *   Take care not to use the "invoked ARN" directly but replace any
     *   [alias suffix](https://docs.aws.amazon.com/lambda/latest/dg/configuration-aliases.html)
     *   with the resolved function version, as the same runtime instance may be invocable with
     *   multiple different aliases.
     * - **GCP:** The [URI of the resource](https://cloud.google.com/iam/docs/full-resource-names)
     * - **Azure:** The [Fully Qualified Resource ID](https://docs.microsoft.com/rest/api/resources/resources/get-by-id) of the invoked function,
     *   *not* the function app, having the form
     *   `/subscriptions/<SUBSCRIPTION_GUID>/resourceGroups/<RG>/providers/Microsoft.Web/sites/<FUNCAPP>/functions/<FUNC>`.
     *   This means that a span attribute MUST be used, as an Azure function app can host multiple functions that would usually share
     *   a TracerProvider.
     */
    public const CLOUD_RESOURCE_ID = 'cloud.resource_id';

    /**
     * The [event_id](https://github.com/cloudevents/spec/blob/v1.0.2/cloudevents/spec.md#id) uniquely identifies the event.
     */
    public const CLOUDEVENTS_EVENT_ID = 'cloudevents.event_id';

    /**
     * The [source](https://github.com/cloudevents/spec/blob/v1.0.2/cloudevents/spec.md#source-1) identifies the context in which an event happened.
     */
    public const CLOUDEVENTS_EVENT_SOURCE = 'cloudevents.event_source';

    /**
     * The [version of the CloudEvents specification](https://github.com/cloudevents/spec/blob/v1.0.2/cloudevents/spec.md#specversion) which the event uses.
     */
    public const CLOUDEVENTS_EVENT_SPEC_VERSION = 'cloudevents.event_spec_version';

    /**
     * The [subject](https://github.com/cloudevents/spec/blob/v1.0.2/cloudevents/spec.md#subject) of the event in the context of the event producer (identified by source).
     */
    public const CLOUDEVENTS_EVENT_SUBJECT = 'cloudevents.event_subject';

    /**
     * The [event_type](https://github.com/cloudevents/spec/blob/v1.0.2/cloudevents/spec.md#type) contains a value describing the type of event related to the originating occurrence.
     */
    public const CLOUDEVENTS_EVENT_TYPE = 'cloudevents.event_type';

    /**
     * Deprecated, use `code.column.number`
     *
     * @deprecated {"note": "Replaced by `code.column.number`", "reason": "uncategorized"}
     */
    public const CODE_COLUMN = 'code.column';

    /**
     * The column number in `code.file.path` best representing the operation. It SHOULD point within the code unit named in `code.function.name`. This attribute MUST NOT be used on the Profile signal since the data is already captured in 'message Line'. This constraint is imposed to prevent redundancy and maintain data integrity.
     */
    public const CODE_COLUMN_NUMBER = 'code.column.number';

    /**
     * The source code file name that identifies the code unit as uniquely as possible (preferably an absolute file path). This attribute MUST NOT be used on the Profile signal since the data is already captured in 'message Function'. This constraint is imposed to prevent redundancy and maintain data integrity.
     */
    public const CODE_FILE_PATH = 'code.file.path';

    /**
     * Deprecated, use `code.file.path` instead
     *
     * @deprecated {"note": "Replaced by `code.file.path`", "reason": "uncategorized"}
     */
    public const CODE_FILEPATH = 'code.filepath';

    /**
     * Deprecated, use `code.function.name` instead
     *
     * @deprecated {"note": "Replaced by `code.function.name`", "reason": "uncategorized"}
     */
    public const CODE_FUNCTION = 'code.function';

    /**
     * The method or function fully-qualified name without arguments. The value should fit the natural representation of the language runtime, which is also likely the same used within `code.stacktrace` attribute value. This attribute MUST NOT be used on the Profile signal since the data is already captured in 'message Function'. This constraint is imposed to prevent redundancy and maintain data integrity.
     *
     * Values and format depends on each language runtime, thus it is impossible to provide an exhaustive list of examples.
     * The values are usually the same (or prefixes of) the ones found in native stack trace representation stored in
     * `code.stacktrace` without information on arguments.
     *
     * Examples:
     *
     * - Java method: `com.example.MyHttpService.serveRequest`
     * - Java anonymous class method: `com.mycompany.Main$1.myMethod`
     * - Java lambda method: `com.mycompany.Main$$Lambda/0x0000748ae4149c00.myMethod`
     * - PHP function: `GuzzleHttp\Client::transfer`
     * - Go function: `github.com/my/repo/pkg.foo.func5`
     * - Elixir: `OpenTelemetry.Ctx.new`
     * - Erlang: `opentelemetry_ctx:new`
     * - Rust: `playground::my_module::my_cool_func`
     * - C function: `fopen`
     */
    public const CODE_FUNCTION_NAME = 'code.function.name';

    /**
     * The line number in `code.file.path` best representing the operation. It SHOULD point within the code unit named in `code.function.name`. This attribute MUST NOT be used on the Profile signal since the data is already captured in 'message Line'. This constraint is imposed to prevent redundancy and maintain data integrity.
     */
    public const CODE_LINE_NUMBER = 'code.line.number';

    /**
     * Deprecated, use `code.line.number` instead
     *
     * @deprecated {"note": "Replaced by `code.line.number`", "reason": "uncategorized"}
     */
    public const CODE_LINENO = 'code.lineno';

    /**
     * Deprecated, namespace is now included into `code.function.name`
     *
     * @deprecated {"note": "Value should be included in `code.function.name` which is expected to be a fully-qualified name.", "reason": "uncategorized"}
     */
    public const CODE_NAMESPACE = 'code.namespace';

    /**
     * A stacktrace as a string in the natural representation for the language runtime. The representation is identical to [`exception.stacktrace`](/docs/exceptions/exceptions-spans.md#stacktrace-representation). This attribute MUST NOT be used on the Profile signal since the data is already captured in 'message Location'. This constraint is imposed to prevent redundancy and maintain data integrity.
     */
    public const CODE_STACKTRACE = 'code.stacktrace';

    /**
     * The command used to run the container (i.e. the command name).
     *
     * If using embedded credentials or sensitive data, it is recommended to remove them to prevent potential leakage.
     */
    public const CONTAINER_COMMAND = 'container.command';

    /**
     * All the command arguments (including the command/executable itself) run by the container.
     */
    public const CONTAINER_COMMAND_ARGS = 'container.command_args';

    /**
     * The full command run by the container as a single string representing the full command.
     */
    public const CONTAINER_COMMAND_LINE = 'container.command_line';

    /**
     * Deprecated, use `cpu.mode` instead.
     *
     * @deprecated {"note": "Replaced by `cpu.mode`", "reason": "uncategorized"}
     */
    public const CONTAINER_CPU_STATE = 'container.cpu.state';

    /**
     * The name of the CSI ([Container Storage Interface](https://github.com/container-storage-interface/spec)) plugin used by the volume.
     *
     * This can sometimes be referred to as a "driver" in CSI implementations. This should represent the `name` field of the GetPluginInfo RPC.
     */
    public const CONTAINER_CSI_PLUGIN_NAME = 'container.csi.plugin.name';

    /**
     * The unique volume ID returned by the CSI ([Container Storage Interface](https://github.com/container-storage-interface/spec)) plugin.
     *
     * This can sometimes be referred to as a "volume handle" in CSI implementations. This should represent the `Volume.volume_id` field in CSI spec.
     */
    public const CONTAINER_CSI_VOLUME_ID = 'container.csi.volume.id';

    /**
     * Container ID. Usually a UUID, as for example used to [identify Docker containers](https://docs.docker.com/engine/containers/run/#container-identification). The UUID might be abbreviated.
     */
    public const CONTAINER_ID = 'container.id';

    /**
     * Runtime specific image identifier. Usually a hash algorithm followed by a UUID.
     *
     * Docker defines a sha256 of the image id; `container.image.id` corresponds to the `Image` field from the Docker container inspect [API](https://docs.docker.com/engine/api/v1.43/#tag/Container/operation/ContainerInspect) endpoint.
     * K8s defines a link to the container registry repository with digest `"imageID": "registry.azurecr.io /namespace/service/dockerfile@sha256:bdeabd40c3a8a492eaf9e8e44d0ebbb84bac7ee25ac0cf8a7159d25f62555625"`.
     * The ID is assigned by the container runtime and can vary in different environments. Consider using `oci.manifest.digest` if it is important to identify the same image in different environments/runtimes.
     */
    public const CONTAINER_IMAGE_ID = 'container.image.id';

    /**
     * Name of the image the container was built on.
     */
    public const CONTAINER_IMAGE_NAME = 'container.image.name';

    /**
     * Repo digests of the container image as provided by the container runtime.
     *
     * [Docker](https://docs.docker.com/engine/api/v1.43/#tag/Image/operation/ImageInspect) and [CRI](https://github.com/kubernetes/cri-api/blob/c75ef5b473bbe2d0a4fc92f82235efd665ea8e9f/pkg/apis/runtime/v1/api.proto#L1237-L1238) report those under the `RepoDigests` field.
     */
    public const CONTAINER_IMAGE_REPO_DIGESTS = 'container.image.repo_digests';

    /**
     * Container image tags. An example can be found in [Docker Image Inspect](https://docs.docker.com/engine/api/v1.43/#tag/Image/operation/ImageInspect). Should be only the `<tag>` section of the full name for example from `registry.example.com/my-org/my-image:<tag>`.
     */
    public const CONTAINER_IMAGE_TAGS = 'container.image.tags';

    /**
     * Container labels, `<key>` being the label name, the value being the label value.
     *
     * For example, a docker container label `app` with value `nginx` SHOULD be recorded as the `container.label.app` attribute with value `"nginx"`.
     */
    public const CONTAINER_LABEL = 'container.label';

    /**
     * Deprecated, use `container.label` instead.
     *
     * @deprecated {"note": "Replaced by `container.label`.", "reason": "uncategorized"}
     */
    public const CONTAINER_LABELS = 'container.labels';

    /**
     * Container name used by container runtime.
     */
    public const CONTAINER_NAME = 'container.name';

    /**
     * The container runtime managing this container.
     */
    public const CONTAINER_RUNTIME = 'container.runtime';

    /**
     * The logical CPU number [0..n-1]
     */
    public const CPU_LOGICAL_NUMBER = 'cpu.logical_number';

    /**
     * The mode of the CPU
     */
    public const CPU_MODE = 'cpu.mode';

    /**
     * Deprecated, use `cassandra.consistency.level` instead.
     *
     * @deprecated {"note": "Replaced by `cassandra.consistency.level`.", "reason": "uncategorized"}
     */
    public const DB_CASSANDRA_CONSISTENCY_LEVEL = 'db.cassandra.consistency_level';

    /**
     * Deprecated, use `cassandra.coordinator.dc` instead.
     *
     * @deprecated {"note": "Replaced by `cassandra.coordinator.dc`.", "reason": "uncategorized"}
     */
    public const DB_CASSANDRA_COORDINATOR_DC = 'db.cassandra.coordinator.dc';

    /**
     * Deprecated, use `cassandra.coordinator.id` instead.
     *
     * @deprecated {"note": "Replaced by `cassandra.coordinator.id`.", "reason": "uncategorized"}
     */
    public const DB_CASSANDRA_COORDINATOR_ID = 'db.cassandra.coordinator.id';

    /**
     * Deprecated, use `cassandra.query.idempotent` instead.
     *
     * @deprecated {"note": "Replaced by `cassandra.query.idempotent`.", "reason": "uncategorized"}
     */
    public const DB_CASSANDRA_IDEMPOTENCE = 'db.cassandra.idempotence';

    /**
     * Deprecated, use `cassandra.page.size` instead.
     *
     * @deprecated {"note": "Replaced by `cassandra.page.size`.", "reason": "uncategorized"}
     */
    public const DB_CASSANDRA_PAGE_SIZE = 'db.cassandra.page_size';

    /**
     * Deprecated, use `cassandra.speculative_execution.count` instead.
     *
     * @deprecated {"note": "Replaced by `cassandra.speculative_execution.count`.", "reason": "uncategorized"}
     */
    public const DB_CASSANDRA_SPECULATIVE_EXECUTION_COUNT = 'db.cassandra.speculative_execution_count';

    /**
     * Deprecated, use `db.collection.name` instead.
     *
     * @deprecated {"note": "Replaced by `db.collection.name`.", "reason": "uncategorized"}
     */
    public const DB_CASSANDRA_TABLE = 'db.cassandra.table';

    /**
     * The name of the connection pool; unique within the instrumented application. In case the connection pool implementation doesn't provide a name, instrumentation SHOULD use a combination of parameters that would make the name unique, for example, combining attributes `server.address`, `server.port`, and `db.namespace`, formatted as `server.address:server.port/db.namespace`. Instrumentations that generate connection pool name following different patterns SHOULD document it.
     */
    public const DB_CLIENT_CONNECTION_POOL_NAME = 'db.client.connection.pool.name';

    /**
     * The state of a connection in the pool
     */
    public const DB_CLIENT_CONNECTION_STATE = 'db.client.connection.state';

    /**
     * Deprecated, use `db.client.connection.pool.name` instead.
     *
     * @deprecated {"note": "Replaced by `db.client.connection.pool.name`.", "reason": "uncategorized"}
     */
    public const DB_CLIENT_CONNECTIONS_POOL_NAME = 'db.client.connections.pool.name';

    /**
     * Deprecated, use `db.client.connection.state` instead.
     *
     * @deprecated {"note": "Replaced by `db.client.connection.state`.", "reason": "uncategorized"}
     */
    public const DB_CLIENT_CONNECTIONS_STATE = 'db.client.connections.state';

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
     */
    public const DB_COLLECTION_NAME = 'db.collection.name';

    /**
     * Deprecated, use `server.address`, `server.port` attributes instead.
     *
     * @deprecated {"note": "Replaced by `server.address` and `server.port`.", "reason": "uncategorized"}
     */
    public const DB_CONNECTION_STRING = 'db.connection_string';

    /**
     * Deprecated, use `azure.client.id` instead.
     *
     * @deprecated {"note": "Replaced by `azure.client.id`.", "reason": "uncategorized"}
     */
    public const DB_COSMOSDB_CLIENT_ID = 'db.cosmosdb.client_id';

    /**
     * Deprecated, use `azure.cosmosdb.connection.mode` instead.
     *
     * @deprecated {"note": "Replaced by `azure.cosmosdb.connection.mode`.", "reason": "uncategorized"}
     */
    public const DB_COSMOSDB_CONNECTION_MODE = 'db.cosmosdb.connection_mode';

    /**
     * Deprecated, use `cosmosdb.consistency.level` instead.
     *
     * @deprecated {"note": "Replaced by `azure.cosmosdb.consistency.level`.", "reason": "uncategorized"}
     */
    public const DB_COSMOSDB_CONSISTENCY_LEVEL = 'db.cosmosdb.consistency_level';

    /**
     * Deprecated, use `db.collection.name` instead.
     *
     * @deprecated {"note": "Replaced by `db.collection.name`.", "reason": "uncategorized"}
     */
    public const DB_COSMOSDB_CONTAINER = 'db.cosmosdb.container';

    /**
     * Deprecated, no replacement at this time.
     *
     * @deprecated {"note": "No replacement at this time.", "reason": "uncategorized"}
     */
    public const DB_COSMOSDB_OPERATION_TYPE = 'db.cosmosdb.operation_type';

    /**
     * Deprecated, use `azure.cosmosdb.operation.contacted_regions` instead.
     *
     * @deprecated {"note": "Replaced by `azure.cosmosdb.operation.contacted_regions`.", "reason": "uncategorized"}
     */
    public const DB_COSMOSDB_REGIONS_CONTACTED = 'db.cosmosdb.regions_contacted';

    /**
     * Deprecated, use `azure.cosmosdb.operation.request_charge` instead.
     *
     * @deprecated {"note": "Replaced by `azure.cosmosdb.operation.request_charge`.", "reason": "uncategorized"}
     */
    public const DB_COSMOSDB_REQUEST_CHARGE = 'db.cosmosdb.request_charge';

    /**
     * Deprecated, use `azure.cosmosdb.request.body.size` instead.
     *
     * @deprecated {"note": "Replaced by `azure.cosmosdb.request.body.size`.", "reason": "uncategorized"}
     */
    public const DB_COSMOSDB_REQUEST_CONTENT_LENGTH = 'db.cosmosdb.request_content_length';

    /**
     * Deprecated, use `db.response.status_code` instead.
     *
     * @deprecated {"note": "Replaced by `db.response.status_code`.", "reason": "uncategorized"}
     */
    public const DB_COSMOSDB_STATUS_CODE = 'db.cosmosdb.status_code';

    /**
     * Deprecated, use `azure.cosmosdb.response.sub_status_code` instead.
     *
     * @deprecated {"note": "Replaced by `azure.cosmosdb.response.sub_status_code`.", "reason": "uncategorized"}
     */
    public const DB_COSMOSDB_SUB_STATUS_CODE = 'db.cosmosdb.sub_status_code';

    /**
     * Deprecated, use `db.namespace` instead.
     *
     * @deprecated {"note": "Replaced by `db.namespace`.", "reason": "uncategorized"}
     */
    public const DB_ELASTICSEARCH_CLUSTER_NAME = 'db.elasticsearch.cluster.name';

    /**
     * Deprecated, use `elasticsearch.node.name` instead.
     *
     * @deprecated {"note": "Replaced by `elasticsearch.node.name`.", "reason": "uncategorized"}
     */
    public const DB_ELASTICSEARCH_NODE_NAME = 'db.elasticsearch.node.name';

    /**
     * Deprecated, use `db.operation.parameter` instead.
     *
     * @deprecated {"note": "Replaced by `db.operation.parameter`.", "reason": "uncategorized"}
     */
    public const DB_ELASTICSEARCH_PATH_PARTS = 'db.elasticsearch.path_parts';

    /**
     * Deprecated, no general replacement at this time. For Elasticsearch, use `db.elasticsearch.node.name` instead.
     *
     * @deprecated {"note": "Deprecated, no general replacement at this time. For Elasticsearch, use `db.elasticsearch.node.name` instead.", "reason": "uncategorized"}
     */
    public const DB_INSTANCE_ID = 'db.instance.id';

    /**
     * Removed, no replacement at this time.
     *
     * @deprecated {"note": "Removed as not used.", "reason": "uncategorized"}
     */
    public const DB_JDBC_DRIVER_CLASSNAME = 'db.jdbc.driver_classname';

    /**
     * Deprecated, use `db.collection.name` instead.
     *
     * @deprecated {"note": "Replaced by `db.collection.name`.", "reason": "uncategorized"}
     */
    public const DB_MONGODB_COLLECTION = 'db.mongodb.collection';

    /**
     * Deprecated, SQL Server instance is now populated as a part of `db.namespace` attribute.
     *
     * @deprecated {"note": "Deprecated, no replacement at this time.", "reason": "uncategorized"}
     */
    public const DB_MSSQL_INSTANCE_NAME = 'db.mssql.instance_name';

    /**
     * Deprecated, use `db.namespace` instead.
     *
     * @deprecated {"note": "Replaced by `db.namespace`.", "reason": "uncategorized"}
     */
    public const DB_NAME = 'db.name';

    /**
     * The name of the database, fully qualified within the server address and port.
     *
     * If a database system has multiple namespace components, they SHOULD be concatenated from the most general to the most specific namespace component, using `|` as a separator between the components. Any missing components (and their associated separators) SHOULD be omitted.
     * Semantic conventions for individual database systems SHOULD document what `db.namespace` means in the context of that system.
     * It is RECOMMENDED to capture the value as provided by the application without attempting to do any case normalization.
     */
    public const DB_NAMESPACE = 'db.namespace';

    /**
     * Deprecated, use `db.operation.name` instead.
     *
     * @deprecated {"note": "Replaced by `db.operation.name`.", "reason": "uncategorized"}
     */
    public const DB_OPERATION = 'db.operation';

    /**
     * The number of queries included in a batch operation.
     * Operations are only considered batches when they contain two or more operations, and so `db.operation.batch.size` SHOULD never be `1`.
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
     * `db.query.parameter.<key>` SHOULD NOT be captured on batch operations.
     *
     * Examples:
     *
     * - For a query `SELECT * FROM users where username =  %s` with the parameter `"jdoe"`,
     *   the attribute `db.query.parameter.0` SHOULD be set to `"jdoe"`.
     * - For a query `"SELECT * FROM users WHERE username = %(username)s;` with parameter
     *   `username = "jdoe"`, the attribute `db.query.parameter.username` SHOULD be set to `"jdoe"`.
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
     * [Generating query summary](/docs/database/database-spans.md#generating-a-summary-of-the-query)
     * section.
     */
    public const DB_QUERY_SUMMARY = 'db.query.summary';

    /**
     * The database query being executed.
     *
     * For sanitization see [Sanitization of `db.query.text`](/docs/database/database-spans.md#sanitization-of-dbquerytext).
     * For batch operations, if the individual operations are known to have the same query text then that query text SHOULD be used, otherwise all of the individual query texts SHOULD be concatenated with separator `; ` or some other database system specific separator if more applicable.
     * Parameterized query text SHOULD NOT be sanitized. Even though parameterized query text can potentially have sensitive data, by using a parameterized query the user is giving a strong signal that any sensitive data will be passed as parameter values, and the benefit to observability of capturing the static part of the query text by default outweighs the risk.
     */
    public const DB_QUERY_TEXT = 'db.query.text';

    /**
     * Deprecated, use `db.namespace` instead.
     *
     * @deprecated {"note": "Replaced by `db.namespace`.", "reason": "uncategorized"}
     */
    public const DB_REDIS_DATABASE_INDEX = 'db.redis.database_index';

    /**
     * Number of rows returned by the operation.
     */
    public const DB_RESPONSE_RETURNED_ROWS = 'db.response.returned_rows';

    /**
     * Database response status code.
     * The status code returned by the database. Usually it represents an error code, but may also represent partial success, warning, or differentiate between various types of successful outcomes.
     * Semantic conventions for individual database systems SHOULD document what `db.response.status_code` means in the context of that system.
     */
    public const DB_RESPONSE_STATUS_CODE = 'db.response.status_code';

    /**
     * Deprecated, use `db.collection.name` instead, but only if not extracting the value from `db.query.text`.
     *
     * @deprecated {"note": "Replaced by `db.collection.name`, but only if not extracting the value from `db.query.text`.", "reason": "uncategorized"}
     */
    public const DB_SQL_TABLE = 'db.sql.table';

    /**
     * The database statement being executed.
     *
     * @deprecated {"note": "Replaced by `db.query.text`.", "reason": "uncategorized"}
     */
    public const DB_STATEMENT = 'db.statement';

    /**
     * The name of a stored procedure within the database.
     * It is RECOMMENDED to capture the value as provided by the application
     * without attempting to do any case normalization.
     *
     * For batch operations, if the individual operations are known to have the same
     * stored procedure name then that stored procedure name SHOULD be used.
     */
    public const DB_STORED_PROCEDURE_NAME = 'db.stored_procedure.name';

    /**
     * Deprecated, use `db.system.name` instead.
     *
     * @deprecated {"note": "Replaced by `db.system.name`.", "reason": "uncategorized"}
     */
    public const DB_SYSTEM = 'db.system';

    /**
     * The database management system (DBMS) product as identified by the client instrumentation.
     * The actual DBMS may differ from the one identified by the client. For example, when using PostgreSQL client libraries to connect to a CockroachDB, the `db.system.name` is set to `postgresql` based on the instrumentation's best knowledge.
     */
    public const DB_SYSTEM_NAME = 'db.system.name';

    /**
     * Deprecated, no replacement at this time.
     *
     * @deprecated {"note": "No replacement at this time.", "reason": "uncategorized"}
     */
    public const DB_USER = 'db.user';

    /**
     * 'Deprecated, use `deployment.environment.name` instead.'
     *
     * @deprecated {"note": "Deprecated, use `deployment.environment.name` instead.", "reason": "uncategorized"}
     */
    public const DEPLOYMENT_ENVIRONMENT = 'deployment.environment';

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
     */
    public const DEPLOYMENT_ENVIRONMENT_NAME = 'deployment.environment.name';

    /**
     * The id of the deployment.
     */
    public const DEPLOYMENT_ID = 'deployment.id';

    /**
     * The name of the deployment.
     */
    public const DEPLOYMENT_NAME = 'deployment.name';

    /**
     * The status of the deployment.
     */
    public const DEPLOYMENT_STATUS = 'deployment.status';

    /**
     * Destination address - domain name if available without reverse DNS lookup; otherwise, IP address or Unix domain socket name.
     * When observed from the source side, and when communicating through an intermediary, `destination.address` SHOULD represent the destination address behind any intermediaries, for example proxies, if it's available.
     */
    public const DESTINATION_ADDRESS = 'destination.address';

    /**
     * Destination port number
     */
    public const DESTINATION_PORT = 'destination.port';

    /**
     * A unique identifier representing the device
     *
     * Its value SHOULD be identical for all apps on a device and it SHOULD NOT change if an app is uninstalled and re-installed.
     * However, it might be resettable by the user for all apps on a device.
     * Hardware IDs (e.g. vendor-specific serial number, IMEI or MAC address) MAY be used as values.
     *
     * More information about Android identifier best practices can be found [here](https://developer.android.com/training/articles/user-data-ids).
     *
     * > [!WARNING]> This attribute may contain sensitive (PII) information. Caution should be taken when storing personal data or anything which can identify a user. GDPR and data protection laws may apply,
     * > ensure you do your own due diligence.> Due to these reasons, this identifier is not recommended for consumer applications and will likely result in rejection from both Google Play and App Store.
     * > However, it may be appropriate for specific enterprise scenarios, such as kiosk devices or enterprise-managed devices, with appropriate compliance clearance.
     * > Any instrumentation providing this identifier MUST implement it as an opt-in feature.> See [`app.installation.id`](/docs/attributes-registry/app.md#app-installation-id)>  for a more privacy-preserving alternative.
     */
    public const DEVICE_ID = 'device.id';

    /**
     * The name of the device manufacturer
     *
     * The Android OS provides this field via [Build](https://developer.android.com/reference/android/os/Build#MANUFACTURER). iOS apps SHOULD hardcode the value `Apple`.
     */
    public const DEVICE_MANUFACTURER = 'device.manufacturer';

    /**
     * The model identifier for the device
     *
     * It's recommended this value represents a machine-readable version of the model identifier rather than the market or consumer-friendly name of the device.
     */
    public const DEVICE_MODEL_IDENTIFIER = 'device.model.identifier';

    /**
     * The marketing name for the device model
     *
     * It's recommended this value represents a human-readable version of the device model rather than a machine-readable alternative.
     */
    public const DEVICE_MODEL_NAME = 'device.model.name';

    /**
     * The disk IO operation direction.
     */
    public const DISK_IO_DIRECTION = 'disk.io.direction';

    /**
     * The name being queried.
     * If the name field contains non-printable characters (below 32 or above 126), those characters should be represented as escaped base 10 integers (\DDD). Back slashes and quotes should be escaped. Tabs, carriage returns, and line feeds should be converted to \t, \r, and \n respectively.
     */
    public const DNS_QUESTION_NAME = 'dns.question.name';

    /**
     * Represents the human-readable identifier of the node/instance to which a request was routed.
     */
    public const ELASTICSEARCH_NODE_NAME = 'elasticsearch.node.name';

    /**
     * Unique identifier of an end user in the system. It maybe a username, email address, or other identifier.
     * Unique identifier of an end user in the system.
     *
     * > [!Warning]
     * > This field contains sensitive (PII) information.
     */
    public const ENDUSER_ID = 'enduser.id';

    /**
     * Pseudonymous identifier of an end user. This identifier should be a random value that is not directly linked or associated with the end user's actual identity.
     *
     * Pseudonymous identifier of an end user.
     *
     * > [!Warning]
     * > This field contains sensitive (linkable PII) information.
     */
    public const ENDUSER_PSEUDO_ID = 'enduser.pseudo.id';

    /**
     * Deprecated, use `user.roles` instead.
     *
     * @deprecated {"note": "Replaced by `user.roles` attribute.", "reason": "uncategorized"}
     */
    public const ENDUSER_ROLE = 'enduser.role';

    /**
     * Deprecated, no replacement at this time.
     *
     * @deprecated {"note": "Removed.", "reason": "uncategorized"}
     */
    public const ENDUSER_SCOPE = 'enduser.scope';

    /**
     * A message providing more detail about an error in human-readable form.
     * `error.message` should provide additional context and detail about an error.
     * It is NOT RECOMMENDED to duplicate the value of `error.type` in `error.message`.
     * It is also NOT RECOMMENDED to duplicate the value of `exception.message` in `error.message`.
     *
     * `error.message` is NOT RECOMMENDED for metrics or spans due to its unbounded cardinality and overlap with span status.
     */
    public const ERROR_MESSAGE = 'error.message';

    /**
     * Describes a class of error the operation ended with.
     *
     * The `error.type` SHOULD be predictable, and SHOULD have low cardinality.
     *
     * When `error.type` is set to a type (e.g., an exception type), its
     * canonical class name identifying the type within the artifact SHOULD be used.
     *
     * Instrumentations SHOULD document the list of errors they report.
     *
     * The cardinality of `error.type` within one instrumentation library SHOULD be low.
     * Telemetry consumers that aggregate data from multiple instrumentation libraries and applications
     * should be prepared for `error.type` to have high cardinality at query time when no
     * additional filters are applied.
     *
     * If the operation has completed successfully, instrumentations SHOULD NOT set `error.type`.
     *
     * If a specific domain defines its own set of error identifiers (such as HTTP or gRPC status codes),
     * it's RECOMMENDED to:
     *
     * - Use a domain-specific attribute
     * - Set `error.type` to capture all errors, regardless of whether they are defined within the domain-specific set or not.
     */
    public const ERROR_TYPE = 'error.type';

    /**
     * Identifies the class / type of event.
     *
     * @deprecated {"note": "Replaced by EventName top-level field on the LogRecord", "reason": "uncategorized"}
     */
    public const EVENT_NAME = 'event.name';

    /**
     * Indicates that the exception is escaping the scope of the span.
     *
     * @deprecated {"note": "It's no longer recommended to record exceptions that are handled and do not escape the scope of a span.", "reason": "uncategorized"}
     */
    public const EXCEPTION_ESCAPED = 'exception.escaped';

    /**
     * The exception message.
     */
    public const EXCEPTION_MESSAGE = 'exception.message';

    /**
     * A stacktrace as a string in the natural representation for the language runtime. The representation is to be determined and documented by each language SIG.
     */
    public const EXCEPTION_STACKTRACE = 'exception.stacktrace';

    /**
     * The type of the exception (its fully-qualified class name, if applicable). The dynamic type of the exception should be preferred over the static type in languages that support it.
     */
    public const EXCEPTION_TYPE = 'exception.type';

    /**
     * A boolean that is true if the serverless function is executed for the first time (aka cold-start).
     */
    public const FAAS_COLDSTART = 'faas.coldstart';

    /**
     * A string containing the schedule period as [Cron Expression](https://docs.oracle.com/cd/E12058_01/doc/doc.1014/e12030/cron_expressions.htm).
     */
    public const FAAS_CRON = 'faas.cron';

    /**
     * The name of the source on which the triggering operation was performed. For example, in Cloud Storage or S3 corresponds to the bucket name, and in Cosmos DB to the database name.
     */
    public const FAAS_DOCUMENT_COLLECTION = 'faas.document.collection';

    /**
     * The document name/table subjected to the operation. For example, in Cloud Storage or S3 is the name of the file, and in Cosmos DB the table name.
     */
    public const FAAS_DOCUMENT_NAME = 'faas.document.name';

    /**
     * Describes the type of the operation that was performed on the data.
     */
    public const FAAS_DOCUMENT_OPERATION = 'faas.document.operation';

    /**
     * A string containing the time when the data was accessed in the [ISO 8601](https://www.iso.org/iso-8601-date-and-time-format.html) format expressed in [UTC](https://www.w3.org/TR/NOTE-datetime).
     */
    public const FAAS_DOCUMENT_TIME = 'faas.document.time';

    /**
     * The execution environment ID as a string, that will be potentially reused for other invocations to the same function/function version.
     *
     * - **AWS Lambda:** Use the (full) log stream name.
     */
    public const FAAS_INSTANCE = 'faas.instance';

    /**
     * The invocation ID of the current function invocation.
     */
    public const FAAS_INVOCATION_ID = 'faas.invocation_id';

    /**
     * The name of the invoked function.
     *
     * SHOULD be equal to the `faas.name` resource attribute of the invoked function.
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
     */
    public const FAAS_INVOKED_REGION = 'faas.invoked_region';

    /**
     * The amount of memory available to the serverless function converted to Bytes.
     *
     * It's recommended to set this attribute since e.g. too little memory can easily stop a Java AWS Lambda function from working correctly. On AWS Lambda, the environment variable `AWS_LAMBDA_FUNCTION_MEMORY_SIZE` provides this information (which must be multiplied by 1,048,576).
     */
    public const FAAS_MAX_MEMORY = 'faas.max_memory';

    /**
     * The name of the single function that this runtime instance executes.
     *
     * This is the name of the function as configured/deployed on the FaaS
     * platform and is usually different from the name of the callback
     * function (which may be stored in the
     * [`code.namespace`/`code.function.name`](/docs/general/attributes.md#source-code-attributes)
     * span attributes).
     *
     * For some cloud providers, the above definition is ambiguous. The following
     * definition of function name MUST be used for this attribute
     * (and consequently the span name) for the listed cloud providers/products:
     *
     * - **Azure:**  The full name `<FUNCAPP>/<FUNC>`, i.e., function app name
     *   followed by a forward slash followed by the function name (this form
     *   can also be seen in the resource JSON for the function).
     *   This means that a span attribute MUST be used, as an Azure function
     *   app can host multiple functions that would usually share
     *   a TracerProvider (see also the `cloud.resource_id` attribute).
     */
    public const FAAS_NAME = 'faas.name';

    /**
     * A string containing the function invocation time in the [ISO 8601](https://www.iso.org/iso-8601-date-and-time-format.html) format expressed in [UTC](https://www.w3.org/TR/NOTE-datetime).
     */
    public const FAAS_TIME = 'faas.time';

    /**
     * Type of the trigger which caused this function invocation.
     */
    public const FAAS_TRIGGER = 'faas.trigger';

    /**
     * The immutable version of the function being executed.
     * Depending on the cloud provider and platform, use:
     *
     * - **AWS Lambda:** The [function version](https://docs.aws.amazon.com/lambda/latest/dg/configuration-versions.html)
     *   (an integer represented as a decimal string).
     * - **Google Cloud Run (Services):** The [revision](https://cloud.google.com/run/docs/managing/revisions)
     *   (i.e., the function name plus the revision suffix).
     * - **Google Cloud Functions:** The value of the
     *   [`K_REVISION` environment variable](https://cloud.google.com/functions/docs/env-var#runtime_environment_variables_set_automatically).
     * - **Azure Functions:** Not applicable. Do not set this attribute.
     */
    public const FAAS_VERSION = 'faas.version';

    /**
     * The unique identifier for the flag evaluation context. For example, the targeting key.
     */
    public const FEATURE_FLAG_CONTEXT_ID = 'feature_flag.context.id';

    /**
     * Deprecated, use `error.message` instead.
     *
     * @deprecated {"note": "Replaced by `error.message`.", "reason": "uncategorized"}
     */
    public const FEATURE_FLAG_EVALUATION_ERROR_MESSAGE = 'feature_flag.evaluation.error.message';

    /**
     * Deprecated, use `feature_flag.result.reason` instead.
     *
     * @deprecated {"note": "Replaced by `feature_flag.result.reason`.", "reason": "uncategorized"}
     */
    public const FEATURE_FLAG_EVALUATION_REASON = 'feature_flag.evaluation.reason';

    /**
     * The lookup key of the feature flag.
     */
    public const FEATURE_FLAG_KEY = 'feature_flag.key';

    /**
     * Identifies the feature flag provider.
     */
    public const FEATURE_FLAG_PROVIDER_NAME = 'feature_flag.provider.name';

    /**
     * The reason code which shows how a feature flag value was determined.
     */
    public const FEATURE_FLAG_RESULT_REASON = 'feature_flag.result.reason';

    /**
     * A semantic identifier for an evaluated flag value.
     *
     * A semantic identifier, commonly referred to as a variant, provides a means
     * for referring to a value without including the value itself. This can
     * provide additional context for understanding the meaning behind a value.
     * For example, the variant `red` maybe be used for the value `#c05543`.
     */
    public const FEATURE_FLAG_RESULT_VARIANT = 'feature_flag.result.variant';

    /**
     * The identifier of the [flag set](https://openfeature.dev/specification/glossary/#flag-set) to which the feature flag belongs.
     */
    public const FEATURE_FLAG_SET_ID = 'feature_flag.set.id';

    /**
     * Deprecated, use `feature_flag.result.variant` instead.
     *
     * @deprecated {"note": "Replaced by `feature_flag.result.variant`.", "reason": "uncategorized"}
     */
    public const FEATURE_FLAG_VARIANT = 'feature_flag.variant';

    /**
     * The version of the ruleset used during the evaluation. This may be any stable value which uniquely identifies the ruleset.
     */
    public const FEATURE_FLAG_VERSION = 'feature_flag.version';

    /**
     * Time when the file was last accessed, in ISO 8601 format.
     *
     * This attribute might not be supported by some file systems — NFS, FAT32, in embedded OS, etc.
     */
    public const FILE_ACCESSED = 'file.accessed';

    /**
     * Array of file attributes.
     *
     * Attributes names depend on the OS or file system. Here’s a non-exhaustive list of values expected for this attribute: `archive`, `compressed`, `directory`, `encrypted`, `execute`, `hidden`, `immutable`, `journaled`, `read`, `readonly`, `symbolic link`, `system`, `temporary`, `write`.
     */
    public const FILE_ATTRIBUTES = 'file.attributes';

    /**
     * Time when the file attributes or metadata was last changed, in ISO 8601 format.
     *
     * `file.changed` captures the time when any of the file's properties or attributes (including the content) are changed, while `file.modified` captures the timestamp when the file content is modified.
     */
    public const FILE_CHANGED = 'file.changed';

    /**
     * Time when the file was created, in ISO 8601 format.
     *
     * This attribute might not be supported by some file systems — NFS, FAT32, in embedded OS, etc.
     */
    public const FILE_CREATED = 'file.created';

    /**
     * Directory where the file is located. It should include the drive letter, when appropriate.
     */
    public const FILE_DIRECTORY = 'file.directory';

    /**
     * File extension, excluding the leading dot.
     *
     * When the file name has multiple extensions (example.tar.gz), only the last one should be captured ("gz", not "tar.gz").
     */
    public const FILE_EXTENSION = 'file.extension';

    /**
     * Name of the fork. A fork is additional data associated with a filesystem object.
     *
     * On Linux, a resource fork is used to store additional data with a filesystem object. A file always has at least one fork for the data portion, and additional forks may exist.
     * On NTFS, this is analogous to an Alternate Data Stream (ADS), and the default data stream for a file is just called $DATA. Zone.Identifier is commonly used by Windows to track contents downloaded from the Internet. An ADS is typically of the form: C:\path\to\filename.extension:some_fork_name, and some_fork_name is the value that should populate `fork_name`. `filename.extension` should populate `file.name`, and `extension` should populate `file.extension`. The full path, `file.path`, will include the fork name.
     */
    public const FILE_FORK_NAME = 'file.fork_name';

    /**
     * Primary Group ID (GID) of the file.
     */
    public const FILE_GROUP_ID = 'file.group.id';

    /**
     * Primary group name of the file.
     */
    public const FILE_GROUP_NAME = 'file.group.name';

    /**
     * Inode representing the file in the filesystem.
     */
    public const FILE_INODE = 'file.inode';

    /**
     * Mode of the file in octal representation.
     */
    public const FILE_MODE = 'file.mode';

    /**
     * Time when the file content was last modified, in ISO 8601 format.
     */
    public const FILE_MODIFIED = 'file.modified';

    /**
     * Name of the file including the extension, without the directory.
     */
    public const FILE_NAME = 'file.name';

    /**
     * The user ID (UID) or security identifier (SID) of the file owner.
     */
    public const FILE_OWNER_ID = 'file.owner.id';

    /**
     * Username of the file owner.
     */
    public const FILE_OWNER_NAME = 'file.owner.name';

    /**
     * Full path to the file, including the file name. It should include the drive letter, when appropriate.
     */
    public const FILE_PATH = 'file.path';

    /**
     * File size in bytes.
     */
    public const FILE_SIZE = 'file.size';

    /**
     * Path to the target of a symbolic link.
     *
     * This attribute is only applicable to symbolic links.
     */
    public const FILE_SYMBOLIC_LINK_TARGET_PATH = 'file.symbolic_link.target_path';

    /**
     * The container within GCP where the AppHub application is defined.
     */
    public const GCP_APPHUB_APPLICATION_CONTAINER = 'gcp.apphub.application.container';

    /**
     * The name of the application as configured in AppHub.
     */
    public const GCP_APPHUB_APPLICATION_ID = 'gcp.apphub.application.id';

    /**
     * The GCP zone or region where the application is defined.
     */
    public const GCP_APPHUB_APPLICATION_LOCATION = 'gcp.apphub.application.location';

    /**
     * Criticality of a service indicates its importance to the business.
     *
     * [See AppHub type enum](https://cloud.google.com/app-hub/docs/reference/rest/v1/Attributes#type)
     */
    public const GCP_APPHUB_SERVICE_CRITICALITY_TYPE = 'gcp.apphub.service.criticality_type';

    /**
     * Environment of a service is the stage of a software lifecycle.
     *
     * [See AppHub environment type](https://cloud.google.com/app-hub/docs/reference/rest/v1/Attributes#type_1)
     */
    public const GCP_APPHUB_SERVICE_ENVIRONMENT_TYPE = 'gcp.apphub.service.environment_type';

    /**
     * The name of the service as configured in AppHub.
     */
    public const GCP_APPHUB_SERVICE_ID = 'gcp.apphub.service.id';

    /**
     * Criticality of a workload indicates its importance to the business.
     *
     * [See AppHub type enum](https://cloud.google.com/app-hub/docs/reference/rest/v1/Attributes#type)
     */
    public const GCP_APPHUB_WORKLOAD_CRITICALITY_TYPE = 'gcp.apphub.workload.criticality_type';

    /**
     * Environment of a workload is the stage of a software lifecycle.
     *
     * [See AppHub environment type](https://cloud.google.com/app-hub/docs/reference/rest/v1/Attributes#type_1)
     */
    public const GCP_APPHUB_WORKLOAD_ENVIRONMENT_TYPE = 'gcp.apphub.workload.environment_type';

    /**
     * The name of the workload as configured in AppHub.
     */
    public const GCP_APPHUB_WORKLOAD_ID = 'gcp.apphub.workload.id';

    /**
     * Identifies the Google Cloud service for which the official client library is intended.
     * Intended to be a stable identifier for Google Cloud client libraries that is uniform across implementation languages. The value should be derived from the canonical service domain for the service; for example, 'foo.googleapis.com' should result in a value of 'foo'.
     */
    public const GCP_CLIENT_SERVICE = 'gcp.client.service';

    /**
     * The name of the Cloud Run [execution](https://cloud.google.com/run/docs/managing/job-executions) being run for the Job, as set by the [`CLOUD_RUN_EXECUTION`](https://cloud.google.com/run/docs/container-contract#jobs-env-vars) environment variable.
     */
    public const GCP_CLOUD_RUN_JOB_EXECUTION = 'gcp.cloud_run.job.execution';

    /**
     * The index for a task within an execution as provided by the [`CLOUD_RUN_TASK_INDEX`](https://cloud.google.com/run/docs/container-contract#jobs-env-vars) environment variable.
     */
    public const GCP_CLOUD_RUN_JOB_TASK_INDEX = 'gcp.cloud_run.job.task_index';

    /**
     * The hostname of a GCE instance. This is the full value of the default or [custom hostname](https://cloud.google.com/compute/docs/instances/custom-hostname-vm).
     */
    public const GCP_GCE_INSTANCE_HOSTNAME = 'gcp.gce.instance.hostname';

    /**
     * The instance name of a GCE instance. This is the value provided by `host.name`, the visible name of the instance in the Cloud Console UI, and the prefix for the default hostname of the instance as defined by the [default internal DNS name](https://cloud.google.com/compute/docs/internal-dns#instance-fully-qualified-domain-names).
     */
    public const GCP_GCE_INSTANCE_NAME = 'gcp.gce.instance.name';

    /**
     * Free-form description of the GenAI agent provided by the application.
     */
    public const GEN_AI_AGENT_DESCRIPTION = 'gen_ai.agent.description';

    /**
     * The unique identifier of the GenAI agent.
     */
    public const GEN_AI_AGENT_ID = 'gen_ai.agent.id';

    /**
     * Human-readable name of the GenAI agent provided by the application.
     */
    public const GEN_AI_AGENT_NAME = 'gen_ai.agent.name';

    /**
     * Deprecated, use Event API to report completions contents.
     *
     * @deprecated {"note": "Removed, no replacement at this time.", "reason": "uncategorized"}
     */
    public const GEN_AI_COMPLETION = 'gen_ai.completion';

    /**
     * Deprecated, use `gen_ai.output.type`.
     *
     * @deprecated {"note": "Replaced by `gen_ai.output.type`.", "reason": "uncategorized"}
     */
    public const GEN_AI_OPENAI_REQUEST_RESPONSE_FORMAT = 'gen_ai.openai.request.response_format';

    /**
     * Deprecated, use `gen_ai.request.seed`.
     *
     * @deprecated {"note": "Replaced by `gen_ai.request.seed` attribute.", "reason": "uncategorized"}
     */
    public const GEN_AI_OPENAI_REQUEST_SEED = 'gen_ai.openai.request.seed';

    /**
     * The service tier requested. May be a specific tier, default, or auto.
     */
    public const GEN_AI_OPENAI_REQUEST_SERVICE_TIER = 'gen_ai.openai.request.service_tier';

    /**
     * The service tier used for the response.
     */
    public const GEN_AI_OPENAI_RESPONSE_SERVICE_TIER = 'gen_ai.openai.response.service_tier';

    /**
     * A fingerprint to track any eventual change in the Generative AI environment.
     */
    public const GEN_AI_OPENAI_RESPONSE_SYSTEM_FINGERPRINT = 'gen_ai.openai.response.system_fingerprint';

    /**
     * The name of the operation being performed.
     * If one of the predefined values applies, but specific system uses a different name it's RECOMMENDED to document it in the semantic conventions for specific GenAI system and use system-specific name in the instrumentation. If a different name is not documented, instrumentation libraries SHOULD use applicable predefined value.
     */
    public const GEN_AI_OPERATION_NAME = 'gen_ai.operation.name';

    /**
     * Represents the content type requested by the client.
     * This attribute SHOULD be used when the client requests output of a specific type. The model may return zero or more outputs of this type.
     * This attribute specifies the output modality and not the actual output format. For example, if an image is requested, the actual output could be a URL pointing to an image file.
     * Additional output format details may be recorded in the future in the `gen_ai.output.{type}.*` attributes.
     */
    public const GEN_AI_OUTPUT_TYPE = 'gen_ai.output.type';

    /**
     * Deprecated, use Event API to report prompt contents.
     *
     * @deprecated {"note": "Removed, no replacement at this time.", "reason": "uncategorized"}
     */
    public const GEN_AI_PROMPT = 'gen_ai.prompt';

    /**
     * The target number of candidate completions to return.
     */
    public const GEN_AI_REQUEST_CHOICE_COUNT = 'gen_ai.request.choice.count';

    /**
     * The encoding formats requested in an embeddings operation, if specified.
     * In some GenAI systems the encoding formats are called embedding types. Also, some GenAI systems only accept a single format per request.
     */
    public const GEN_AI_REQUEST_ENCODING_FORMATS = 'gen_ai.request.encoding_formats';

    /**
     * The frequency penalty setting for the GenAI request.
     */
    public const GEN_AI_REQUEST_FREQUENCY_PENALTY = 'gen_ai.request.frequency_penalty';

    /**
     * The maximum number of tokens the model generates for a request.
     */
    public const GEN_AI_REQUEST_MAX_TOKENS = 'gen_ai.request.max_tokens';

    /**
     * The name of the GenAI model a request is being made to.
     */
    public const GEN_AI_REQUEST_MODEL = 'gen_ai.request.model';

    /**
     * The presence penalty setting for the GenAI request.
     */
    public const GEN_AI_REQUEST_PRESENCE_PENALTY = 'gen_ai.request.presence_penalty';

    /**
     * Requests with same seed value more likely to return same result.
     */
    public const GEN_AI_REQUEST_SEED = 'gen_ai.request.seed';

    /**
     * List of sequences that the model will use to stop generating further tokens.
     */
    public const GEN_AI_REQUEST_STOP_SEQUENCES = 'gen_ai.request.stop_sequences';

    /**
     * The temperature setting for the GenAI request.
     */
    public const GEN_AI_REQUEST_TEMPERATURE = 'gen_ai.request.temperature';

    /**
     * The top_k sampling setting for the GenAI request.
     */
    public const GEN_AI_REQUEST_TOP_K = 'gen_ai.request.top_k';

    /**
     * The top_p sampling setting for the GenAI request.
     */
    public const GEN_AI_REQUEST_TOP_P = 'gen_ai.request.top_p';

    /**
     * Array of reasons the model stopped generating tokens, corresponding to each generation received.
     */
    public const GEN_AI_RESPONSE_FINISH_REASONS = 'gen_ai.response.finish_reasons';

    /**
     * The unique identifier for the completion.
     */
    public const GEN_AI_RESPONSE_ID = 'gen_ai.response.id';

    /**
     * The name of the model that generated the response.
     */
    public const GEN_AI_RESPONSE_MODEL = 'gen_ai.response.model';

    /**
     * The Generative AI product as identified by the client or server instrumentation.
     * The `gen_ai.system` describes a family of GenAI models with specific model identified
     * by `gen_ai.request.model` and `gen_ai.response.model` attributes.
     *
     * The actual GenAI product may differ from the one identified by the client.
     * Multiple systems, including Azure OpenAI and Gemini, are accessible by OpenAI client
     * libraries. In such cases, the `gen_ai.system` is set to `openai` based on the
     * instrumentation's best knowledge, instead of the actual system. The `server.address`
     * attribute may help identify the actual system in use for `openai`.
     *
     * For custom model, a custom friendly name SHOULD be used.
     * If none of these options apply, the `gen_ai.system` SHOULD be set to `_OTHER`.
     */
    public const GEN_AI_SYSTEM = 'gen_ai.system';

    /**
     * The type of token being counted.
     */
    public const GEN_AI_TOKEN_TYPE = 'gen_ai.token.type';

    /**
     * The tool call identifier.
     */
    public const GEN_AI_TOOL_CALL_ID = 'gen_ai.tool.call.id';

    /**
     * The tool description.
     */
    public const GEN_AI_TOOL_DESCRIPTION = 'gen_ai.tool.description';

    /**
     * Name of the tool utilized by the agent.
     */
    public const GEN_AI_TOOL_NAME = 'gen_ai.tool.name';

    /**
     * Type of the tool utilized by the agent
     * Extension: A tool executed on the agent-side to directly call external APIs, bridging the gap between the agent and real-world systems.
     * Agent-side operations involve actions that are performed by the agent on the server or within the agent's controlled environment.
     * Function: A tool executed on the client-side, where the agent generates parameters for a predefined function, and the client executes the logic.
     * Client-side operations are actions taken on the user's end or within the client application.
     * Datastore: A tool used by the agent to access and query structured or unstructured external data for retrieval-augmented tasks or knowledge updates.
     */
    public const GEN_AI_TOOL_TYPE = 'gen_ai.tool.type';

    /**
     * Deprecated, use `gen_ai.usage.output_tokens` instead.
     *
     * @deprecated {"note": "Replaced by `gen_ai.usage.output_tokens` attribute.", "reason": "uncategorized"}
     */
    public const GEN_AI_USAGE_COMPLETION_TOKENS = 'gen_ai.usage.completion_tokens';

    /**
     * The number of tokens used in the GenAI input (prompt).
     */
    public const GEN_AI_USAGE_INPUT_TOKENS = 'gen_ai.usage.input_tokens';

    /**
     * The number of tokens used in the GenAI response (completion).
     */
    public const GEN_AI_USAGE_OUTPUT_TOKENS = 'gen_ai.usage.output_tokens';

    /**
     * Deprecated, use `gen_ai.usage.input_tokens` instead.
     *
     * @deprecated {"note": "Replaced by `gen_ai.usage.input_tokens` attribute.", "reason": "uncategorized"}
     */
    public const GEN_AI_USAGE_PROMPT_TOKENS = 'gen_ai.usage.prompt_tokens';

    /**
     * Two-letter code representing continent’s name.
     */
    public const GEO_CONTINENT_CODE = 'geo.continent.code';

    /**
     * Two-letter ISO Country Code ([ISO 3166-1 alpha2](https://wikipedia.org/wiki/ISO_3166-1#Codes)).
     */
    public const GEO_COUNTRY_ISO_CODE = 'geo.country.iso_code';

    /**
     * Locality name. Represents the name of a city, town, village, or similar populated place.
     */
    public const GEO_LOCALITY_NAME = 'geo.locality.name';

    /**
     * Latitude of the geo location in [WGS84](https://wikipedia.org/wiki/World_Geodetic_System#WGS84).
     */
    public const GEO_LOCATION_LAT = 'geo.location.lat';

    /**
     * Longitude of the geo location in [WGS84](https://wikipedia.org/wiki/World_Geodetic_System#WGS84).
     */
    public const GEO_LOCATION_LON = 'geo.location.lon';

    /**
     * Postal code associated with the location. Values appropriate for this field may also be known as a postcode or ZIP code and will vary widely from country to country.
     */
    public const GEO_POSTAL_CODE = 'geo.postal_code';

    /**
     * Region ISO code ([ISO 3166-2](https://wikipedia.org/wiki/ISO_3166-2)).
     */
    public const GEO_REGION_ISO_CODE = 'geo.region.iso_code';

    /**
     * The type of memory.
     */
    public const GO_MEMORY_TYPE = 'go.memory.type';

    /**
     * The GraphQL document being executed.
     * The value may be sanitized to exclude sensitive information.
     */
    public const GRAPHQL_DOCUMENT = 'graphql.document';

    /**
     * The name of the operation being executed.
     */
    public const GRAPHQL_OPERATION_NAME = 'graphql.operation.name';

    /**
     * The type of the operation being executed.
     */
    public const GRAPHQL_OPERATION_TYPE = 'graphql.operation.type';

    /**
     * Unique identifier for the application
     */
    public const HEROKU_APP_ID = 'heroku.app.id';

    /**
     * Commit hash for the current release
     */
    public const HEROKU_RELEASE_COMMIT = 'heroku.release.commit';

    /**
     * Time and date the release was created
     */
    public const HEROKU_RELEASE_CREATION_TIMESTAMP = 'heroku.release.creation_timestamp';

    /**
     * The CPU architecture the host system is running on.
     */
    public const HOST_ARCH = 'host.arch';

    /**
     * The amount of level 2 memory cache available to the processor (in Bytes).
     */
    public const HOST_CPU_CACHE_L2_SIZE = 'host.cpu.cache.l2.size';

    /**
     * Family or generation of the CPU.
     */
    public const HOST_CPU_FAMILY = 'host.cpu.family';

    /**
     * Model identifier. It provides more granular information about the CPU, distinguishing it from other CPUs within the same family.
     */
    public const HOST_CPU_MODEL_ID = 'host.cpu.model.id';

    /**
     * Model designation of the processor.
     */
    public const HOST_CPU_MODEL_NAME = 'host.cpu.model.name';

    /**
     * Stepping or core revisions.
     */
    public const HOST_CPU_STEPPING = 'host.cpu.stepping';

    /**
     * Processor manufacturer identifier. A maximum 12-character string.
     *
     * [CPUID](https://wiki.osdev.org/CPUID) command returns the vendor ID string in EBX, EDX and ECX registers. Writing these to memory in this order results in a 12-character string.
     */
    public const HOST_CPU_VENDOR_ID = 'host.cpu.vendor.id';

    /**
     * Unique host ID. For Cloud, this must be the instance_id assigned by the cloud provider. For non-containerized systems, this should be the `machine-id`. See the table below for the sources to use to determine the `machine-id` based on operating system.
     */
    public const HOST_ID = 'host.id';

    /**
     * VM image ID or host OS image ID. For Cloud, this value is from the provider.
     */
    public const HOST_IMAGE_ID = 'host.image.id';

    /**
     * Name of the VM image or OS install the host was instantiated from.
     */
    public const HOST_IMAGE_NAME = 'host.image.name';

    /**
     * The version string of the VM image or host OS as defined in [Version Attributes](/docs/resource/README.md#version-attributes).
     */
    public const HOST_IMAGE_VERSION = 'host.image.version';

    /**
     * Available IP addresses of the host, excluding loopback interfaces.
     *
     * IPv4 Addresses MUST be specified in dotted-quad notation. IPv6 addresses MUST be specified in the [RFC 5952](https://www.rfc-editor.org/rfc/rfc5952.html) format.
     */
    public const HOST_IP = 'host.ip';

    /**
     * Available MAC addresses of the host, excluding loopback interfaces.
     *
     * MAC Addresses MUST be represented in [IEEE RA hexadecimal form](https://standards.ieee.org/wp-content/uploads/import/documents/tutorials/eui.pdf): as hyphen-separated octets in uppercase hexadecimal form from most to least significant.
     */
    public const HOST_MAC = 'host.mac';

    /**
     * Name of the host. On Unix systems, it may contain what the hostname command returns, or the fully qualified hostname, or another name specified by the user.
     */
    public const HOST_NAME = 'host.name';

    /**
     * Type of host. For Cloud, this must be the machine type.
     */
    public const HOST_TYPE = 'host.type';

    /**
     * Deprecated, use `client.address` instead.
     *
     * @deprecated {"note": "Replaced by `client.address`.", "reason": "uncategorized"}
     */
    public const HTTP_CLIENT_IP = 'http.client_ip';

    /**
     * State of the HTTP connection in the HTTP connection pool.
     */
    public const HTTP_CONNECTION_STATE = 'http.connection.state';

    /**
     * Deprecated, use `network.protocol.name` instead.
     *
     * @deprecated {"note": "Replaced by `network.protocol.name`.", "reason": "uncategorized"}
     */
    public const HTTP_FLAVOR = 'http.flavor';

    /**
     * Deprecated, use one of `server.address`, `client.address` or `http.request.header.host` instead, depending on the usage.
     *
     * @deprecated {"note": "Replaced by one of `server.address`, `client.address` or `http.request.header.host`, depending on the usage.", "reason": "uncategorized"}
     */
    public const HTTP_HOST = 'http.host';

    /**
     * Deprecated, use `http.request.method` instead.
     *
     * @deprecated {"note": "Replaced by `http.request.method`.", "reason": "uncategorized"}
     */
    public const HTTP_METHOD = 'http.method';

    /**
     * The size of the request payload body in bytes. This is the number of bytes transferred excluding headers and is often, but not always, present as the [Content-Length](https://www.rfc-editor.org/rfc/rfc9110.html#field.content-length) header. For requests using transport encoding, this should be the compressed size.
     */
    public const HTTP_REQUEST_BODY_SIZE = 'http.request.body.size';

    /**
     * HTTP request headers, `<key>` being the normalized HTTP Header name (lowercase), the value being the header values.
     *
     * Instrumentations SHOULD require an explicit configuration of which headers are to be captured.
     * Including all request headers can be a security risk - explicit configuration helps avoid leaking sensitive information.
     *
     * The `User-Agent` header is already captured in the `user_agent.original` attribute.
     * Users MAY explicitly configure instrumentations to capture them even though it is not recommended.
     *
     * The attribute value MUST consist of either multiple header values as an array of strings
     * or a single-item array containing a possibly comma-concatenated string, depending on the way
     * the HTTP library provides access to headers.
     *
     * Examples:
     *
     * - A header `Content-Type: application/json` SHOULD be recorded as the `http.request.header.content-type`
     *   attribute with value `["application/json"]`.
     * - A header `X-Forwarded-For: 1.2.3.4, 1.2.3.5` SHOULD be recorded as the `http.request.header.x-forwarded-for`
     *   attribute with value `["1.2.3.4", "1.2.3.5"]` or `["1.2.3.4, 1.2.3.5"]` depending on the HTTP library.
     */
    public const HTTP_REQUEST_HEADER = 'http.request.header';

    /**
     * HTTP request method.
     * HTTP request method value SHOULD be "known" to the instrumentation.
     * By default, this convention defines "known" methods as the ones listed in [RFC9110](https://www.rfc-editor.org/rfc/rfc9110.html#name-methods)
     * and the PATCH method defined in [RFC5789](https://www.rfc-editor.org/rfc/rfc5789.html).
     *
     * If the HTTP request method is not known to instrumentation, it MUST set the `http.request.method` attribute to `_OTHER`.
     *
     * If the HTTP instrumentation could end up converting valid HTTP request methods to `_OTHER`, then it MUST provide a way to override
     * the list of known HTTP methods. If this override is done via environment variable, then the environment variable MUST be named
     * OTEL_INSTRUMENTATION_HTTP_KNOWN_METHODS and support a comma-separated list of case-sensitive known HTTP methods
     * (this list MUST be a full override of the default known method, it is not a list of known methods in addition to the defaults).
     *
     * HTTP method names are case-sensitive and `http.request.method` attribute value MUST match a known HTTP method name exactly.
     * Instrumentations for specific web frameworks that consider HTTP methods to be case insensitive, SHOULD populate a canonical equivalent.
     * Tracing instrumentations that do so, MUST also set `http.request.method_original` to the original value.
     */
    public const HTTP_REQUEST_METHOD = 'http.request.method';

    /**
     * Original HTTP method sent by the client in the request line.
     */
    public const HTTP_REQUEST_METHOD_ORIGINAL = 'http.request.method_original';

    /**
     * The ordinal number of request resending attempt (for any reason, including redirects).
     *
     * The resend count SHOULD be updated each time an HTTP request gets resent by the client, regardless of what was the cause of the resending (e.g. redirection, authorization failure, 503 Server Unavailable, network issues, or any other).
     */
    public const HTTP_REQUEST_RESEND_COUNT = 'http.request.resend_count';

    /**
     * The total size of the request in bytes. This should be the total number of bytes sent over the wire, including the request line (HTTP/1.1), framing (HTTP/2 and HTTP/3), headers, and request body if any.
     */
    public const HTTP_REQUEST_SIZE = 'http.request.size';

    /**
     * Deprecated, use `http.request.header.<key>` instead.
     *
     * @deprecated {"note": "Replaced by `http.request.header.<key>`.", "reason": "uncategorized"}
     */
    public const HTTP_REQUEST_CONTENT_LENGTH = 'http.request_content_length';

    /**
     * Deprecated, use `http.request.body.size` instead.
     *
     * @deprecated {"note": "Replaced by `http.request.body.size`.", "reason": "uncategorized"}
     */
    public const HTTP_REQUEST_CONTENT_LENGTH_UNCOMPRESSED = 'http.request_content_length_uncompressed';

    /**
     * The size of the response payload body in bytes. This is the number of bytes transferred excluding headers and is often, but not always, present as the [Content-Length](https://www.rfc-editor.org/rfc/rfc9110.html#field.content-length) header. For requests using transport encoding, this should be the compressed size.
     */
    public const HTTP_RESPONSE_BODY_SIZE = 'http.response.body.size';

    /**
     * HTTP response headers, `<key>` being the normalized HTTP Header name (lowercase), the value being the header values.
     *
     * Instrumentations SHOULD require an explicit configuration of which headers are to be captured.
     * Including all response headers can be a security risk - explicit configuration helps avoid leaking sensitive information.
     *
     * Users MAY explicitly configure instrumentations to capture them even though it is not recommended.
     *
     * The attribute value MUST consist of either multiple header values as an array of strings
     * or a single-item array containing a possibly comma-concatenated string, depending on the way
     * the HTTP library provides access to headers.
     *
     * Examples:
     *
     * - A header `Content-Type: application/json` header SHOULD be recorded as the `http.request.response.content-type`
     *   attribute with value `["application/json"]`.
     * - A header `My-custom-header: abc, def` header SHOULD be recorded as the `http.response.header.my-custom-header`
     *   attribute with value `["abc", "def"]` or `["abc, def"]` depending on the HTTP library.
     */
    public const HTTP_RESPONSE_HEADER = 'http.response.header';

    /**
     * The total size of the response in bytes. This should be the total number of bytes sent over the wire, including the status line (HTTP/1.1), framing (HTTP/2 and HTTP/3), headers, and response body and trailers if any.
     */
    public const HTTP_RESPONSE_SIZE = 'http.response.size';

    /**
     * [HTTP response status code](https://tools.ietf.org/html/rfc7231#section-6).
     */
    public const HTTP_RESPONSE_STATUS_CODE = 'http.response.status_code';

    /**
     * Deprecated, use `http.response.header.<key>` instead.
     *
     * @deprecated {"note": "Replaced by `http.response.header.<key>`.", "reason": "uncategorized"}
     */
    public const HTTP_RESPONSE_CONTENT_LENGTH = 'http.response_content_length';

    /**
     * Deprecated, use `http.response.body.size` instead.
     *
     * @deprecated {"note": "Replace by `http.response.body.size`.", "reason": "uncategorized"}
     */
    public const HTTP_RESPONSE_CONTENT_LENGTH_UNCOMPRESSED = 'http.response_content_length_uncompressed';

    /**
     * The matched route, that is, the path template in the format used by the respective server framework.
     *
     * MUST NOT be populated when this is not supported by the HTTP server framework as the route attribute should have low-cardinality and the URI path can NOT substitute it.
     * SHOULD include the [application root](/docs/http/http-spans.md#http-server-definitions) if there is one.
     */
    public const HTTP_ROUTE = 'http.route';

    /**
     * Deprecated, use `url.scheme` instead.
     *
     * @deprecated {"note": "Replaced by `url.scheme` instead.", "reason": "uncategorized"}
     */
    public const HTTP_SCHEME = 'http.scheme';

    /**
     * Deprecated, use `server.address` instead.
     *
     * @deprecated {"note": "Replaced by `server.address`.", "reason": "uncategorized"}
     */
    public const HTTP_SERVER_NAME = 'http.server_name';

    /**
     * Deprecated, use `http.response.status_code` instead.
     *
     * @deprecated {"note": "Replaced by `http.response.status_code`.", "reason": "uncategorized"}
     */
    public const HTTP_STATUS_CODE = 'http.status_code';

    /**
     * Deprecated, use `url.path` and `url.query` instead.
     *
     * @deprecated {"note": "Split to `url.path` and `url.query.", "reason": "uncategorized"}
     */
    public const HTTP_TARGET = 'http.target';

    /**
     * Deprecated, use `url.full` instead.
     *
     * @deprecated {"note": "Replaced by `url.full`.", "reason": "uncategorized"}
     */
    public const HTTP_URL = 'http.url';

    /**
     * Deprecated, use `user_agent.original` instead.
     *
     * @deprecated {"note": "Replaced by `user_agent.original`.", "reason": "uncategorized"}
     */
    public const HTTP_USER_AGENT = 'http.user_agent';

    /**
     * Name of the buffer pool.
     * Pool names are generally obtained via [BufferPoolMXBean#getName()](https://docs.oracle.com/en/java/javase/11/docs/api/java.management/java/lang/management/BufferPoolMXBean.html#getName()).
     */
    public const JVM_BUFFER_POOL_NAME = 'jvm.buffer.pool.name';

    /**
     * Name of the garbage collector action.
     * Garbage collector action is generally obtained via [GarbageCollectionNotificationInfo#getGcAction()](https://docs.oracle.com/en/java/javase/11/docs/api/jdk.management/com/sun/management/GarbageCollectionNotificationInfo.html#getGcAction()).
     */
    public const JVM_GC_ACTION = 'jvm.gc.action';

    /**
     * Name of the garbage collector cause.
     * Garbage collector cause is generally obtained via [GarbageCollectionNotificationInfo#getGcCause()](https://docs.oracle.com/en/java/javase/11/docs/api/jdk.management/com/sun/management/GarbageCollectionNotificationInfo.html#getGcCause()).
     */
    public const JVM_GC_CAUSE = 'jvm.gc.cause';

    /**
     * Name of the garbage collector.
     * Garbage collector name is generally obtained via [GarbageCollectionNotificationInfo#getGcName()](https://docs.oracle.com/en/java/javase/11/docs/api/jdk.management/com/sun/management/GarbageCollectionNotificationInfo.html#getGcName()).
     */
    public const JVM_GC_NAME = 'jvm.gc.name';

    /**
     * Name of the memory pool.
     * Pool names are generally obtained via [MemoryPoolMXBean#getName()](https://docs.oracle.com/en/java/javase/11/docs/api/java.management/java/lang/management/MemoryPoolMXBean.html#getName()).
     */
    public const JVM_MEMORY_POOL_NAME = 'jvm.memory.pool.name';

    /**
     * The type of memory.
     */
    public const JVM_MEMORY_TYPE = 'jvm.memory.type';

    /**
     * Whether the thread is daemon or not.
     */
    public const JVM_THREAD_DAEMON = 'jvm.thread.daemon';

    /**
     * State of the thread.
     */
    public const JVM_THREAD_STATE = 'jvm.thread.state';

    /**
     * The name of the cluster.
     */
    public const K8S_CLUSTER_NAME = 'k8s.cluster.name';

    /**
     * A pseudo-ID for the cluster, set to the UID of the `kube-system` namespace.
     *
     * K8s doesn't have support for obtaining a cluster ID. If this is ever
     * added, we will recommend collecting the `k8s.cluster.uid` through the
     * official APIs. In the meantime, we are able to use the `uid` of the
     * `kube-system` namespace as a proxy for cluster ID. Read on for the
     * rationale.
     *
     * Every object created in a K8s cluster is assigned a distinct UID. The
     * `kube-system` namespace is used by Kubernetes itself and will exist
     * for the lifetime of the cluster. Using the `uid` of the `kube-system`
     * namespace is a reasonable proxy for the K8s ClusterID as it will only
     * change if the cluster is rebuilt. Furthermore, Kubernetes UIDs are
     * UUIDs as standardized by
     * [ISO/IEC 9834-8 and ITU-T X.667](https://www.itu.int/ITU-T/studygroups/com17/oid.html).
     * Which states:
     *
     * > If generated according to one of the mechanisms defined in Rec.
     * > ITU-T X.667 | ISO/IEC 9834-8, a UUID is either guaranteed to be
     * > different from all other UUIDs generated before 3603 A.D., or is
     * > extremely likely to be different (depending on the mechanism chosen).
     *
     * Therefore, UIDs between clusters should be extremely unlikely to
     * conflict.
     */
    public const K8S_CLUSTER_UID = 'k8s.cluster.uid';

    /**
     * The name of the Container from Pod specification, must be unique within a Pod. Container runtime usually uses different globally unique name (`container.name`).
     */
    public const K8S_CONTAINER_NAME = 'k8s.container.name';

    /**
     * Number of times the container was restarted. This attribute can be used to identify a particular container (running or stopped) within a container spec.
     */
    public const K8S_CONTAINER_RESTART_COUNT = 'k8s.container.restart_count';

    /**
     * Last terminated reason of the Container.
     */
    public const K8S_CONTAINER_STATUS_LAST_TERMINATED_REASON = 'k8s.container.status.last_terminated_reason';

    /**
     * The cronjob annotation placed on the CronJob, the `<key>` being the annotation name, the value being the annotation value.
     *
     * Examples:
     *
     * - An annotation `retries` with value `4` SHOULD be recorded as the
     *   `k8s.cronjob.annotation.retries` attribute with value `"4"`.
     * - An annotation `data` with empty string value SHOULD be recorded as
     *   the `k8s.cronjob.annotation.data` attribute with value `""`.
     */
    public const K8S_CRONJOB_ANNOTATION = 'k8s.cronjob.annotation';

    /**
     * The label placed on the CronJob, the `<key>` being the label name, the value being the label value.
     *
     * Examples:
     *
     * - A label `type` with value `weekly` SHOULD be recorded as the
     *   `k8s.cronjob.label.type` attribute with value `"weekly"`.
     * - A label `automated` with empty string value SHOULD be recorded as
     *   the `k8s.cronjob.label.automated` attribute with value `""`.
     */
    public const K8S_CRONJOB_LABEL = 'k8s.cronjob.label';

    /**
     * The name of the CronJob.
     */
    public const K8S_CRONJOB_NAME = 'k8s.cronjob.name';

    /**
     * The UID of the CronJob.
     */
    public const K8S_CRONJOB_UID = 'k8s.cronjob.uid';

    /**
     * The annotation key-value pairs placed on the DaemonSet.
     *
     * The `<key>` being the annotation name, the value being the annotation value, even if the value is empty.
     */
    public const K8S_DAEMONSET_ANNOTATION = 'k8s.daemonset.annotation';

    /**
     * The label key-value pairs placed on the DaemonSet.
     *
     * The `<key>` being the label name, the value being the label value, even if the value is empty.
     */
    public const K8S_DAEMONSET_LABEL = 'k8s.daemonset.label';

    /**
     * The name of the DaemonSet.
     */
    public const K8S_DAEMONSET_NAME = 'k8s.daemonset.name';

    /**
     * The UID of the DaemonSet.
     */
    public const K8S_DAEMONSET_UID = 'k8s.daemonset.uid';

    /**
     * The annotation key-value pairs placed on the Deployment.
     *
     * The `<key>` being the annotation name, the value being the annotation value, even if the value is empty.
     */
    public const K8S_DEPLOYMENT_ANNOTATION = 'k8s.deployment.annotation';

    /**
     * The label key-value pairs placed on the Deployment.
     *
     * The `<key>` being the label name, the value being the label value, even if the value is empty.
     */
    public const K8S_DEPLOYMENT_LABEL = 'k8s.deployment.label';

    /**
     * The name of the Deployment.
     */
    public const K8S_DEPLOYMENT_NAME = 'k8s.deployment.name';

    /**
     * The UID of the Deployment.
     */
    public const K8S_DEPLOYMENT_UID = 'k8s.deployment.uid';

    /**
     * The name of the horizontal pod autoscaler.
     */
    public const K8S_HPA_NAME = 'k8s.hpa.name';

    /**
     * The UID of the horizontal pod autoscaler.
     */
    public const K8S_HPA_UID = 'k8s.hpa.uid';

    /**
     * The annotation key-value pairs placed on the Job.
     *
     * The `<key>` being the annotation name, the value being the annotation value, even if the value is empty.
     */
    public const K8S_JOB_ANNOTATION = 'k8s.job.annotation';

    /**
     * The label key-value pairs placed on the Job.
     *
     * The `<key>` being the label name, the value being the label value, even if the value is empty.
     */
    public const K8S_JOB_LABEL = 'k8s.job.label';

    /**
     * The name of the Job.
     */
    public const K8S_JOB_NAME = 'k8s.job.name';

    /**
     * The UID of the Job.
     */
    public const K8S_JOB_UID = 'k8s.job.uid';

    /**
     * The annotation key-value pairs placed on the Namespace.
     *
     * The `<key>` being the annotation name, the value being the annotation value, even if the value is empty.
     */
    public const K8S_NAMESPACE_ANNOTATION = 'k8s.namespace.annotation';

    /**
     * The label key-value pairs placed on the Namespace.
     *
     * The `<key>` being the label name, the value being the label value, even if the value is empty.
     */
    public const K8S_NAMESPACE_LABEL = 'k8s.namespace.label';

    /**
     * The name of the namespace that the pod is running in.
     */
    public const K8S_NAMESPACE_NAME = 'k8s.namespace.name';

    /**
     * The phase of the K8s namespace.
     *
     * This attribute aligns with the `phase` field of the
     * [K8s NamespaceStatus](https://kubernetes.io/docs/reference/generated/kubernetes-api/v1.30/#namespacestatus-v1-core)
     */
    public const K8S_NAMESPACE_PHASE = 'k8s.namespace.phase';

    /**
     * The annotation placed on the Node, the `<key>` being the annotation name, the value being the annotation value, even if the value is empty.
     *
     * Examples:
     *
     * - An annotation `node.alpha.kubernetes.io/ttl` with value `0` SHOULD be recorded as
     *   the `k8s.node.annotation.node.alpha.kubernetes.io/ttl` attribute with value `"0"`.
     * - An annotation `data` with empty string value SHOULD be recorded as
     *   the `k8s.node.annotation.data` attribute with value `""`.
     */
    public const K8S_NODE_ANNOTATION = 'k8s.node.annotation';

    /**
     * The label placed on the Node, the `<key>` being the label name, the value being the label value, even if the value is empty.
     *
     * Examples:
     *
     * - A label `kubernetes.io/arch` with value `arm64` SHOULD be recorded
     *   as the `k8s.node.label.kubernetes.io/arch` attribute with value `"arm64"`.
     * - A label `data` with empty string value SHOULD be recorded as
     *   the `k8s.node.label.data` attribute with value `""`.
     */
    public const K8S_NODE_LABEL = 'k8s.node.label';

    /**
     * The name of the Node.
     */
    public const K8S_NODE_NAME = 'k8s.node.name';

    /**
     * The UID of the Node.
     */
    public const K8S_NODE_UID = 'k8s.node.uid';

    /**
     * The annotation placed on the Pod, the `<key>` being the annotation name, the value being the annotation value.
     *
     * Examples:
     *
     * - An annotation `kubernetes.io/enforce-mountable-secrets` with value `true` SHOULD be recorded as
     *   the `k8s.pod.annotation.kubernetes.io/enforce-mountable-secrets` attribute with value `"true"`.
     * - An annotation `mycompany.io/arch` with value `x64` SHOULD be recorded as
     *   the `k8s.pod.annotation.mycompany.io/arch` attribute with value `"x64"`.
     * - An annotation `data` with empty string value SHOULD be recorded as
     *   the `k8s.pod.annotation.data` attribute with value `""`.
     */
    public const K8S_POD_ANNOTATION = 'k8s.pod.annotation';

    /**
     * The label placed on the Pod, the `<key>` being the label name, the value being the label value.
     *
     * Examples:
     *
     * - A label `app` with value `my-app` SHOULD be recorded as
     *   the `k8s.pod.label.app` attribute with value `"my-app"`.
     * - A label `mycompany.io/arch` with value `x64` SHOULD be recorded as
     *   the `k8s.pod.label.mycompany.io/arch` attribute with value `"x64"`.
     * - A label `data` with empty string value SHOULD be recorded as
     *   the `k8s.pod.label.data` attribute with value `""`.
     */
    public const K8S_POD_LABEL = 'k8s.pod.label';

    /**
     * Deprecated, use `k8s.pod.label` instead.
     *
     * @deprecated {"note": "Replaced by `k8s.pod.label`.", "reason": "uncategorized"}
     */
    public const K8S_POD_LABELS = 'k8s.pod.labels';

    /**
     * The name of the Pod.
     */
    public const K8S_POD_NAME = 'k8s.pod.name';

    /**
     * The UID of the Pod.
     */
    public const K8S_POD_UID = 'k8s.pod.uid';

    /**
     * The annotation key-value pairs placed on the ReplicaSet.
     *
     * The `<key>` being the annotation name, the value being the annotation value, even if the value is empty.
     */
    public const K8S_REPLICASET_ANNOTATION = 'k8s.replicaset.annotation';

    /**
     * The label key-value pairs placed on the ReplicaSet.
     *
     * The `<key>` being the label name, the value being the label value, even if the value is empty.
     */
    public const K8S_REPLICASET_LABEL = 'k8s.replicaset.label';

    /**
     * The name of the ReplicaSet.
     */
    public const K8S_REPLICASET_NAME = 'k8s.replicaset.name';

    /**
     * The UID of the ReplicaSet.
     */
    public const K8S_REPLICASET_UID = 'k8s.replicaset.uid';

    /**
     * The name of the replication controller.
     */
    public const K8S_REPLICATIONCONTROLLER_NAME = 'k8s.replicationcontroller.name';

    /**
     * The UID of the replication controller.
     */
    public const K8S_REPLICATIONCONTROLLER_UID = 'k8s.replicationcontroller.uid';

    /**
     * The name of the resource quota.
     */
    public const K8S_RESOURCEQUOTA_NAME = 'k8s.resourcequota.name';

    /**
     * The UID of the resource quota.
     */
    public const K8S_RESOURCEQUOTA_UID = 'k8s.resourcequota.uid';

    /**
     * The annotation key-value pairs placed on the StatefulSet.
     *
     * The `<key>` being the annotation name, the value being the annotation value, even if the value is empty.
     */
    public const K8S_STATEFULSET_ANNOTATION = 'k8s.statefulset.annotation';

    /**
     * The label key-value pairs placed on the StatefulSet.
     *
     * The `<key>` being the label name, the value being the label value, even if the value is empty.
     */
    public const K8S_STATEFULSET_LABEL = 'k8s.statefulset.label';

    /**
     * The name of the StatefulSet.
     */
    public const K8S_STATEFULSET_NAME = 'k8s.statefulset.name';

    /**
     * The UID of the StatefulSet.
     */
    public const K8S_STATEFULSET_UID = 'k8s.statefulset.uid';

    /**
     * The name of the K8s volume.
     */
    public const K8S_VOLUME_NAME = 'k8s.volume.name';

    /**
     * The type of the K8s volume.
     */
    public const K8S_VOLUME_TYPE = 'k8s.volume.type';

    /**
     * The Linux Slab memory state
     */
    public const LINUX_MEMORY_SLAB_STATE = 'linux.memory.slab.state';

    /**
     * The basename of the file.
     */
    public const LOG_FILE_NAME = 'log.file.name';

    /**
     * The basename of the file, with symlinks resolved.
     */
    public const LOG_FILE_NAME_RESOLVED = 'log.file.name_resolved';

    /**
     * The full path to the file.
     */
    public const LOG_FILE_PATH = 'log.file.path';

    /**
     * The full path to the file, with symlinks resolved.
     */
    public const LOG_FILE_PATH_RESOLVED = 'log.file.path_resolved';

    /**
     * The stream associated with the log. See below for a list of well-known values.
     */
    public const LOG_IOSTREAM = 'log.iostream';

    /**
     * The complete original Log Record.
     *
     * This value MAY be added when processing a Log Record which was originally transmitted as a string or equivalent data type AND the Body field of the Log Record does not contain the same value. (e.g. a syslog or a log record read from a file.)
     */
    public const LOG_RECORD_ORIGINAL = 'log.record.original';

    /**
     * A unique identifier for the Log Record.
     *
     * If an id is provided, other log records with the same id will be considered duplicates and can be removed safely. This means, that two distinguishable log records MUST have different values.
     * The id MAY be an [Universally Unique Lexicographically Sortable Identifier (ULID)](https://github.com/ulid/spec), but other identifiers (e.g. UUID) may be used as needed.
     */
    public const LOG_RECORD_UID = 'log.record.uid';

    /**
     * Deprecated, use `rpc.message.compressed_size` instead.
     *
     * @deprecated {"note": "Replaced by `rpc.message.compressed_size`.", "reason": "uncategorized"}
     */
    public const MESSAGE_COMPRESSED_SIZE = 'message.compressed_size';

    /**
     * Deprecated, use `rpc.message.id` instead.
     *
     * @deprecated {"note": "Replaced by `rpc.message.id`.", "reason": "uncategorized"}
     */
    public const MESSAGE_ID = 'message.id';

    /**
     * Deprecated, use `rpc.message.type` instead.
     *
     * @deprecated {"note": "Replaced by `rpc.message.type`.", "reason": "uncategorized"}
     */
    public const MESSAGE_TYPE = 'message.type';

    /**
     * Deprecated, use `rpc.message.uncompressed_size` instead.
     *
     * @deprecated {"note": "Replaced by `rpc.message.uncompressed_size`.", "reason": "uncategorized"}
     */
    public const MESSAGE_UNCOMPRESSED_SIZE = 'message.uncompressed_size';

    /**
     * The number of messages sent, received, or processed in the scope of the batching operation.
     * Instrumentations SHOULD NOT set `messaging.batch.message_count` on spans that operate with a single message. When a messaging client library supports both batch and single-message API for the same operation, instrumentations SHOULD use `messaging.batch.message_count` for batching APIs and SHOULD NOT use it for single-message APIs.
     */
    public const MESSAGING_BATCH_MESSAGE_COUNT = 'messaging.batch.message_count';

    /**
     * A unique identifier for the client that consumes or produces a message.
     */
    public const MESSAGING_CLIENT_ID = 'messaging.client.id';

    /**
     * The name of the consumer group with which a consumer is associated.
     *
     * Semantic conventions for individual messaging systems SHOULD document whether `messaging.consumer.group.name` is applicable and what it means in the context of that system.
     */
    public const MESSAGING_CONSUMER_GROUP_NAME = 'messaging.consumer.group.name';

    /**
     * A boolean that is true if the message destination is anonymous (could be unnamed or have auto-generated name).
     */
    public const MESSAGING_DESTINATION_ANONYMOUS = 'messaging.destination.anonymous';

    /**
     * The message destination name
     * Destination name SHOULD uniquely identify a specific queue, topic or other entity within the broker. If
     * the broker doesn't have such notion, the destination name SHOULD uniquely identify the broker.
     */
    public const MESSAGING_DESTINATION_NAME = 'messaging.destination.name';

    /**
     * The identifier of the partition messages are sent to or received from, unique within the `messaging.destination.name`.
     */
    public const MESSAGING_DESTINATION_PARTITION_ID = 'messaging.destination.partition.id';

    /**
     * The name of the destination subscription from which a message is consumed.
     * Semantic conventions for individual messaging systems SHOULD document whether `messaging.destination.subscription.name` is applicable and what it means in the context of that system.
     */
    public const MESSAGING_DESTINATION_SUBSCRIPTION_NAME = 'messaging.destination.subscription.name';

    /**
     * Low cardinality representation of the messaging destination name
     * Destination names could be constructed from templates. An example would be a destination name involving a user name or product id. Although the destination name in this case is of high cardinality, the underlying template is of low cardinality and can be effectively used for grouping and aggregation.
     */
    public const MESSAGING_DESTINATION_TEMPLATE = 'messaging.destination.template';

    /**
     * A boolean that is true if the message destination is temporary and might not exist anymore after messages are processed.
     */
    public const MESSAGING_DESTINATION_TEMPORARY = 'messaging.destination.temporary';

    /**
     * Deprecated, no replacement at this time.
     *
     * @deprecated {"note": "No replacement at this time.", "reason": "uncategorized"}
     */
    public const MESSAGING_DESTINATION_PUBLISH_ANONYMOUS = 'messaging.destination_publish.anonymous';

    /**
     * Deprecated, no replacement at this time.
     *
     * @deprecated {"note": "No replacement at this time.", "reason": "uncategorized"}
     */
    public const MESSAGING_DESTINATION_PUBLISH_NAME = 'messaging.destination_publish.name';

    /**
     * Deprecated, use `messaging.consumer.group.name` instead.
     *
     * @deprecated {"note": "Replaced by `messaging.consumer.group.name`.", "reason": "uncategorized"}
     */
    public const MESSAGING_EVENTHUBS_CONSUMER_GROUP = 'messaging.eventhubs.consumer.group';

    /**
     * The UTC epoch seconds at which the message has been accepted and stored in the entity.
     */
    public const MESSAGING_EVENTHUBS_MESSAGE_ENQUEUED_TIME = 'messaging.eventhubs.message.enqueued_time';

    /**
     * The ack deadline in seconds set for the modify ack deadline request.
     */
    public const MESSAGING_GCP_PUBSUB_MESSAGE_ACK_DEADLINE = 'messaging.gcp_pubsub.message.ack_deadline';

    /**
     * The ack id for a given message.
     */
    public const MESSAGING_GCP_PUBSUB_MESSAGE_ACK_ID = 'messaging.gcp_pubsub.message.ack_id';

    /**
     * The delivery attempt for a given message.
     */
    public const MESSAGING_GCP_PUBSUB_MESSAGE_DELIVERY_ATTEMPT = 'messaging.gcp_pubsub.message.delivery_attempt';

    /**
     * The ordering key for a given message. If the attribute is not present, the message does not have an ordering key.
     */
    public const MESSAGING_GCP_PUBSUB_MESSAGE_ORDERING_KEY = 'messaging.gcp_pubsub.message.ordering_key';

    /**
     * Deprecated, use `messaging.consumer.group.name` instead.
     *
     * @deprecated {"note": "Replaced by `messaging.consumer.group.name`.", "reason": "uncategorized"}
     */
    public const MESSAGING_KAFKA_CONSUMER_GROUP = 'messaging.kafka.consumer.group';

    /**
     * Deprecated, use `messaging.destination.partition.id` instead.
     *
     * @deprecated {"note": "Replaced by `messaging.destination.partition.id`.", "reason": "uncategorized"}
     */
    public const MESSAGING_KAFKA_DESTINATION_PARTITION = 'messaging.kafka.destination.partition';

    /**
     * Message keys in Kafka are used for grouping alike messages to ensure they're processed on the same partition. They differ from `messaging.message.id` in that they're not unique. If the key is `null`, the attribute MUST NOT be set.
     *
     * If the key type is not string, it's string representation has to be supplied for the attribute. If the key has no unambiguous, canonical string form, don't include its value.
     */
    public const MESSAGING_KAFKA_MESSAGE_KEY = 'messaging.kafka.message.key';

    /**
     * Deprecated, use `messaging.kafka.offset` instead.
     *
     * @deprecated {"note": "Replaced by `messaging.kafka.offset`.", "reason": "uncategorized"}
     */
    public const MESSAGING_KAFKA_MESSAGE_OFFSET = 'messaging.kafka.message.offset';

    /**
     * A boolean that is true if the message is a tombstone.
     */
    public const MESSAGING_KAFKA_MESSAGE_TOMBSTONE = 'messaging.kafka.message.tombstone';

    /**
     * The offset of a record in the corresponding Kafka partition.
     */
    public const MESSAGING_KAFKA_OFFSET = 'messaging.kafka.offset';

    /**
     * The size of the message body in bytes.
     *
     * This can refer to both the compressed or uncompressed body size. If both sizes are known, the uncompressed
     * body size should be used.
     */
    public const MESSAGING_MESSAGE_BODY_SIZE = 'messaging.message.body.size';

    /**
     * The conversation ID identifying the conversation to which the message belongs, represented as a string. Sometimes called "Correlation ID".
     */
    public const MESSAGING_MESSAGE_CONVERSATION_ID = 'messaging.message.conversation_id';

    /**
     * The size of the message body and metadata in bytes.
     *
     * This can refer to both the compressed or uncompressed size. If both sizes are known, the uncompressed
     * size should be used.
     */
    public const MESSAGING_MESSAGE_ENVELOPE_SIZE = 'messaging.message.envelope.size';

    /**
     * A value used by the messaging system as an identifier for the message, represented as a string.
     */
    public const MESSAGING_MESSAGE_ID = 'messaging.message.id';

    /**
     * Deprecated, use `messaging.operation.type` instead.
     *
     * @deprecated {"note": "Replaced by `messaging.operation.type`.", "reason": "uncategorized"}
     */
    public const MESSAGING_OPERATION = 'messaging.operation';

    /**
     * The system-specific name of the messaging operation.
     */
    public const MESSAGING_OPERATION_NAME = 'messaging.operation.name';

    /**
     * A string identifying the type of the messaging operation.
     *
     * If a custom value is used, it MUST be of low cardinality.
     */
    public const MESSAGING_OPERATION_TYPE = 'messaging.operation.type';

    /**
     * RabbitMQ message routing key.
     */
    public const MESSAGING_RABBITMQ_DESTINATION_ROUTING_KEY = 'messaging.rabbitmq.destination.routing_key';

    /**
     * RabbitMQ message delivery tag
     */
    public const MESSAGING_RABBITMQ_MESSAGE_DELIVERY_TAG = 'messaging.rabbitmq.message.delivery_tag';

    /**
     * Deprecated, use `messaging.consumer.group.name` instead.
     *
     * @deprecated {"note": "Replaced by `messaging.consumer.group.name` on the consumer spans. No replacement for producer spans.", "reason": "uncategorized"}
     */
    public const MESSAGING_ROCKETMQ_CLIENT_GROUP = 'messaging.rocketmq.client_group';

    /**
     * Model of message consumption. This only applies to consumer spans.
     */
    public const MESSAGING_ROCKETMQ_CONSUMPTION_MODEL = 'messaging.rocketmq.consumption_model';

    /**
     * The delay time level for delay message, which determines the message delay time.
     */
    public const MESSAGING_ROCKETMQ_MESSAGE_DELAY_TIME_LEVEL = 'messaging.rocketmq.message.delay_time_level';

    /**
     * The timestamp in milliseconds that the delay message is expected to be delivered to consumer.
     */
    public const MESSAGING_ROCKETMQ_MESSAGE_DELIVERY_TIMESTAMP = 'messaging.rocketmq.message.delivery_timestamp';

    /**
     * It is essential for FIFO message. Messages that belong to the same message group are always processed one by one within the same consumer group.
     */
    public const MESSAGING_ROCKETMQ_MESSAGE_GROUP = 'messaging.rocketmq.message.group';

    /**
     * Key(s) of message, another way to mark message besides message id.
     */
    public const MESSAGING_ROCKETMQ_MESSAGE_KEYS = 'messaging.rocketmq.message.keys';

    /**
     * The secondary classifier of message besides topic.
     */
    public const MESSAGING_ROCKETMQ_MESSAGE_TAG = 'messaging.rocketmq.message.tag';

    /**
     * Type of message.
     */
    public const MESSAGING_ROCKETMQ_MESSAGE_TYPE = 'messaging.rocketmq.message.type';

    /**
     * Namespace of RocketMQ resources, resources in different namespaces are individual.
     */
    public const MESSAGING_ROCKETMQ_NAMESPACE = 'messaging.rocketmq.namespace';

    /**
     * Deprecated, use `messaging.destination.subscription.name` instead.
     *
     * @deprecated {"note": "Replaced by `messaging.destination.subscription.name`.", "reason": "uncategorized"}
     */
    public const MESSAGING_SERVICEBUS_DESTINATION_SUBSCRIPTION_NAME = 'messaging.servicebus.destination.subscription_name';

    /**
     * Describes the [settlement type](https://learn.microsoft.com/azure/service-bus-messaging/message-transfers-locks-settlement#peeklock).
     */
    public const MESSAGING_SERVICEBUS_DISPOSITION_STATUS = 'messaging.servicebus.disposition_status';

    /**
     * Number of deliveries that have been attempted for this message.
     */
    public const MESSAGING_SERVICEBUS_MESSAGE_DELIVERY_COUNT = 'messaging.servicebus.message.delivery_count';

    /**
     * The UTC epoch seconds at which the message has been accepted and stored in the entity.
     */
    public const MESSAGING_SERVICEBUS_MESSAGE_ENQUEUED_TIME = 'messaging.servicebus.message.enqueued_time';

    /**
     * The messaging system as identified by the client instrumentation.
     * The actual messaging system may differ from the one known by the client. For example, when using Kafka client libraries to communicate with Azure Event Hubs, the `messaging.system` is set to `kafka` based on the instrumentation's best knowledge.
     */
    public const MESSAGING_SYSTEM = 'messaging.system';

    /**
     * Deprecated, use `network.local.address`.
     *
     * @deprecated {"note": "Replaced by `network.local.address`.", "reason": "uncategorized"}
     */
    public const NET_HOST_IP = 'net.host.ip';

    /**
     * Deprecated, use `server.address`.
     *
     * @deprecated {"note": "Replaced by `server.address`.", "reason": "uncategorized"}
     */
    public const NET_HOST_NAME = 'net.host.name';

    /**
     * Deprecated, use `server.port`.
     *
     * @deprecated {"note": "Replaced by `server.port`.", "reason": "uncategorized"}
     */
    public const NET_HOST_PORT = 'net.host.port';

    /**
     * Deprecated, use `network.peer.address`.
     *
     * @deprecated {"note": "Replaced by `network.peer.address`.", "reason": "uncategorized"}
     */
    public const NET_PEER_IP = 'net.peer.ip';

    /**
     * Deprecated, use `server.address` on client spans and `client.address` on server spans.
     *
     * @deprecated {"note": "Replaced by `server.address` on client spans and `client.address` on server spans.", "reason": "uncategorized"}
     */
    public const NET_PEER_NAME = 'net.peer.name';

    /**
     * Deprecated, use `server.port` on client spans and `client.port` on server spans.
     *
     * @deprecated {"note": "Replaced by `server.port` on client spans and `client.port` on server spans.", "reason": "uncategorized"}
     */
    public const NET_PEER_PORT = 'net.peer.port';

    /**
     * Deprecated, use `network.protocol.name`.
     *
     * @deprecated {"note": "Replaced by `network.protocol.name`.", "reason": "uncategorized"}
     */
    public const NET_PROTOCOL_NAME = 'net.protocol.name';

    /**
     * Deprecated, use `network.protocol.version`.
     *
     * @deprecated {"note": "Replaced by `network.protocol.version`.", "reason": "uncategorized"}
     */
    public const NET_PROTOCOL_VERSION = 'net.protocol.version';

    /**
     * Deprecated, use `network.transport` and `network.type`.
     *
     * @deprecated {"note": "Split to `network.transport` and `network.type`.", "reason": "uncategorized"}
     */
    public const NET_SOCK_FAMILY = 'net.sock.family';

    /**
     * Deprecated, use `network.local.address`.
     *
     * @deprecated {"note": "Replaced by `network.local.address`.", "reason": "uncategorized"}
     */
    public const NET_SOCK_HOST_ADDR = 'net.sock.host.addr';

    /**
     * Deprecated, use `network.local.port`.
     *
     * @deprecated {"note": "Replaced by `network.local.port`.", "reason": "uncategorized"}
     */
    public const NET_SOCK_HOST_PORT = 'net.sock.host.port';

    /**
     * Deprecated, use `network.peer.address`.
     *
     * @deprecated {"note": "Replaced by `network.peer.address`.", "reason": "uncategorized"}
     */
    public const NET_SOCK_PEER_ADDR = 'net.sock.peer.addr';

    /**
     * Deprecated, no replacement at this time.
     *
     * @deprecated {"note": "Removed.", "reason": "uncategorized"}
     */
    public const NET_SOCK_PEER_NAME = 'net.sock.peer.name';

    /**
     * Deprecated, use `network.peer.port`.
     *
     * @deprecated {"note": "Replaced by `network.peer.port`.", "reason": "uncategorized"}
     */
    public const NET_SOCK_PEER_PORT = 'net.sock.peer.port';

    /**
     * Deprecated, use `network.transport`.
     *
     * @deprecated {"note": "Replaced by `network.transport`.", "reason": "uncategorized"}
     */
    public const NET_TRANSPORT = 'net.transport';

    /**
     * The ISO 3166-1 alpha-2 2-character country code associated with the mobile carrier network.
     */
    public const NETWORK_CARRIER_ICC = 'network.carrier.icc';

    /**
     * The mobile carrier country code.
     */
    public const NETWORK_CARRIER_MCC = 'network.carrier.mcc';

    /**
     * The mobile carrier network code.
     */
    public const NETWORK_CARRIER_MNC = 'network.carrier.mnc';

    /**
     * The name of the mobile carrier.
     */
    public const NETWORK_CARRIER_NAME = 'network.carrier.name';

    /**
     * The state of network connection
     * Connection states are defined as part of the [rfc9293](https://datatracker.ietf.org/doc/html/rfc9293#section-3.3.2)
     */
    public const NETWORK_CONNECTION_STATE = 'network.connection.state';

    /**
     * This describes more details regarding the connection.type. It may be the type of cell technology connection, but it could be used for describing details about a wifi connection.
     */
    public const NETWORK_CONNECTION_SUBTYPE = 'network.connection.subtype';

    /**
     * The internet connection type.
     */
    public const NETWORK_CONNECTION_TYPE = 'network.connection.type';

    /**
     * The network interface name.
     */
    public const NETWORK_INTERFACE_NAME = 'network.interface.name';

    /**
     * The network IO operation direction.
     */
    public const NETWORK_IO_DIRECTION = 'network.io.direction';

    /**
     * Local address of the network connection - IP address or Unix domain socket name.
     */
    public const NETWORK_LOCAL_ADDRESS = 'network.local.address';

    /**
     * Local port number of the network connection.
     */
    public const NETWORK_LOCAL_PORT = 'network.local.port';

    /**
     * Peer address of the network connection - IP address or Unix domain socket name.
     */
    public const NETWORK_PEER_ADDRESS = 'network.peer.address';

    /**
     * Peer port number of the network connection.
     */
    public const NETWORK_PEER_PORT = 'network.peer.port';

    /**
     * [OSI application layer](https://wikipedia.org/wiki/Application_layer) or non-OSI equivalent.
     * The value SHOULD be normalized to lowercase.
     */
    public const NETWORK_PROTOCOL_NAME = 'network.protocol.name';

    /**
     * The actual version of the protocol used for network communication.
     * If protocol version is subject to negotiation (for example using [ALPN](https://www.rfc-editor.org/rfc/rfc7301.html)), this attribute SHOULD be set to the negotiated version. If the actual protocol version is not known, this attribute SHOULD NOT be set.
     */
    public const NETWORK_PROTOCOL_VERSION = 'network.protocol.version';

    /**
     * [OSI transport layer](https://wikipedia.org/wiki/Transport_layer) or [inter-process communication method](https://wikipedia.org/wiki/Inter-process_communication).
     *
     * The value SHOULD be normalized to lowercase.
     *
     * Consider always setting the transport when setting a port number, since
     * a port number is ambiguous without knowing the transport. For example
     * different processes could be listening on TCP port 12345 and UDP port 12345.
     */
    public const NETWORK_TRANSPORT = 'network.transport';

    /**
     * [OSI network layer](https://wikipedia.org/wiki/Network_layer) or non-OSI equivalent.
     * The value SHOULD be normalized to lowercase.
     */
    public const NETWORK_TYPE = 'network.type';

    /**
     * The digest of the OCI image manifest. For container images specifically is the digest by which the container image is known.
     *
     * Follows [OCI Image Manifest Specification](https://github.com/opencontainers/image-spec/blob/main/manifest.md), and specifically the [Digest property](https://github.com/opencontainers/image-spec/blob/main/descriptor.md#digests).
     * An example can be found in [Example Image Manifest](https://github.com/opencontainers/image-spec/blob/main/manifest.md#example-image-manifest).
     */
    public const OCI_MANIFEST_DIGEST = 'oci.manifest.digest';

    /**
     * Parent-child Reference type
     * The causal relationship between a child Span and a parent Span.
     */
    public const OPENTRACING_REF_TYPE = 'opentracing.ref_type';

    /**
     * Unique identifier for a particular build or compilation of the operating system.
     */
    public const OS_BUILD_ID = 'os.build_id';

    /**
     * Human readable (not intended to be parsed) OS version information, like e.g. reported by `ver` or `lsb_release -a` commands.
     */
    public const OS_DESCRIPTION = 'os.description';

    /**
     * Human readable operating system name.
     */
    public const OS_NAME = 'os.name';

    /**
     * The operating system type.
     */
    public const OS_TYPE = 'os.type';

    /**
     * The version string of the operating system as defined in [Version Attributes](/docs/resource/README.md#version-attributes).
     */
    public const OS_VERSION = 'os.version';

    /**
     * A name uniquely identifying the instance of the OpenTelemetry component within its containing SDK instance.
     *
     * Implementations SHOULD ensure a low cardinality for this attribute, even across application or SDK restarts.
     * E.g. implementations MUST NOT use UUIDs as values for this attribute.
     *
     * Implementations MAY achieve these goals by following a `<otel.component.type>/<instance-counter>` pattern, e.g. `batching_span_processor/0`.
     * Hereby `otel.component.type` refers to the corresponding attribute value of the component.
     *
     * The value of `instance-counter` MAY be automatically assigned by the component and uniqueness within the enclosing SDK instance MUST be guaranteed.
     * For example, `<instance-counter>` MAY be implemented by using a monotonically increasing counter (starting with `0`), which is incremented every time an
     * instance of the given component type is started.
     *
     * With this implementation, for example the first Batching Span Processor would have `batching_span_processor/0`
     * as `otel.component.name`, the second one `batching_span_processor/1` and so on.
     * These values will therefore be reused in the case of an application restart.
     */
    public const OTEL_COMPONENT_NAME = 'otel.component.name';

    /**
     * A name identifying the type of the OpenTelemetry component.
     *
     * If none of the standardized values apply, implementations SHOULD use the language-defined name of the type.
     * E.g. for Java the fully qualified classname SHOULD be used in this case.
     */
    public const OTEL_COMPONENT_TYPE = 'otel.component.type';

    /**
     * Deprecated. Use the `otel.scope.name` attribute
     *
     * @deprecated {"note": "Use the `otel.scope.name` attribute.", "reason": "uncategorized"}
     */
    public const OTEL_LIBRARY_NAME = 'otel.library.name';

    /**
     * Deprecated. Use the `otel.scope.version` attribute.
     *
     * @deprecated {"note": "Use the `otel.scope.version` attribute.", "reason": "uncategorized"}
     */
    public const OTEL_LIBRARY_VERSION = 'otel.library.version';

    /**
     * The name of the instrumentation scope - (`InstrumentationScope.Name` in OTLP).
     */
    public const OTEL_SCOPE_NAME = 'otel.scope.name';

    /**
     * The version of the instrumentation scope - (`InstrumentationScope.Version` in OTLP).
     */
    public const OTEL_SCOPE_VERSION = 'otel.scope.version';

    /**
     * The result value of the sampler for this span
     */
    public const OTEL_SPAN_SAMPLING_RESULT = 'otel.span.sampling_result';

    /**
     * Name of the code, either "OK" or "ERROR". MUST NOT be set if the status code is UNSET.
     */
    public const OTEL_STATUS_CODE = 'otel.status_code';

    /**
     * Description of the Status if it has a value, otherwise not set.
     */
    public const OTEL_STATUS_DESCRIPTION = 'otel.status_description';

    /**
     * Deprecated, use `db.client.connection.state` instead.
     *
     * @deprecated {"note": "Replaced by `db.client.connection.state`.", "reason": "uncategorized"}
     */
    public const STATE = 'state';

    /**
     * The [`service.name`](/docs/resource/README.md#service) of the remote service. SHOULD be equal to the actual `service.name` resource attribute of the remote service if any.
     */
    public const PEER_SERVICE = 'peer.service';

    /**
     * Deprecated, use `db.client.connection.pool.name` instead.
     *
     * @deprecated {"note": "Replaced by `db.client.connection.pool.name`.", "reason": "uncategorized"}
     */
    public const POOL_NAME = 'pool.name';

    /**
     * Length of the process.command_args array
     *
     * This field can be useful for querying or performing bucket analysis on how many arguments were provided to start a process. More arguments may be an indication of suspicious activity.
     */
    public const PROCESS_ARGS_COUNT = 'process.args_count';

    /**
     * The command used to launch the process (i.e. the command name). On Linux based systems, can be set to the zeroth string in `proc/[pid]/cmdline`. On Windows, can be set to the first parameter extracted from `GetCommandLineW`.
     */
    public const PROCESS_COMMAND = 'process.command';

    /**
     * All the command arguments (including the command/executable itself) as received by the process. On Linux-based systems (and some other Unixoid systems supporting procfs), can be set according to the list of null-delimited strings extracted from `proc/[pid]/cmdline`. For libc-based executables, this would be the full argv vector passed to `main`.
     */
    public const PROCESS_COMMAND_ARGS = 'process.command_args';

    /**
     * The full command used to launch the process as a single string representing the full command. On Windows, can be set to the result of `GetCommandLineW`. Do not set this if you have to assemble it just for monitoring; use `process.command_args` instead.
     */
    public const PROCESS_COMMAND_LINE = 'process.command_line';

    /**
     * Specifies whether the context switches for this data point were voluntary or involuntary.
     */
    public const PROCESS_CONTEXT_SWITCH_TYPE = 'process.context_switch_type';

    /**
     * Deprecated, use `cpu.mode` instead.
     *
     * @deprecated {"note": "Replaced by `cpu.mode`", "reason": "uncategorized"}
     */
    public const PROCESS_CPU_STATE = 'process.cpu.state';

    /**
     * The date and time the process was created, in ISO 8601 format.
     */
    public const PROCESS_CREATION_TIME = 'process.creation.time';

    /**
     * Process environment variables, <key> being the environment variable name, the value being the environment variable value.
     *
     * Examples:
     *
     * - an environment variable `USER` with value `"ubuntu"` SHOULD be recorded
     *   as the `process.environment_variable.USER` attribute with value `"ubuntu"`.
     * - an environment variable `PATH` with value `"/usr/local/bin:/usr/bin"`
     *   SHOULD be recorded as the `process.environment_variable.PATH` attribute
     *   with value `"/usr/local/bin:/usr/bin"`.
     */
    public const PROCESS_ENVIRONMENT_VARIABLE = 'process.environment_variable';

    /**
     * The GNU build ID as found in the `.note.gnu.build-id` ELF section (hex string).
     */
    public const PROCESS_EXECUTABLE_BUILD_ID_GNU = 'process.executable.build_id.gnu';

    /**
     * The Go build ID as retrieved by `go tool buildid <go executable>`.
     */
    public const PROCESS_EXECUTABLE_BUILD_ID_GO = 'process.executable.build_id.go';

    /**
     * Profiling specific build ID for executables. See the OTel specification for Profiles for more information.
     */
    public const PROCESS_EXECUTABLE_BUILD_ID_HTLHASH = 'process.executable.build_id.htlhash';

    /**
     * "Deprecated, use `process.executable.build_id.htlhash` instead."
     *
     * @deprecated {"note": "Replaced by `process.executable.build_id.htlhash`", "reason": "uncategorized"}
     */
    public const PROCESS_EXECUTABLE_BUILD_ID_PROFILING = 'process.executable.build_id.profiling';

    /**
     * The name of the process executable. On Linux based systems, this SHOULD be set to the base name of the target of `/proc/[pid]/exe`. On Windows, this SHOULD be set to the base name of `GetProcessImageFileNameW`.
     */
    public const PROCESS_EXECUTABLE_NAME = 'process.executable.name';

    /**
     * The full path to the process executable. On Linux based systems, can be set to the target of `proc/[pid]/exe`. On Windows, can be set to the result of `GetProcessImageFileNameW`.
     */
    public const PROCESS_EXECUTABLE_PATH = 'process.executable.path';

    /**
     * The exit code of the process.
     */
    public const PROCESS_EXIT_CODE = 'process.exit.code';

    /**
     * The date and time the process exited, in ISO 8601 format.
     */
    public const PROCESS_EXIT_TIME = 'process.exit.time';

    /**
     * The PID of the process's group leader. This is also the process group ID (PGID) of the process.
     */
    public const PROCESS_GROUP_LEADER_PID = 'process.group_leader.pid';

    /**
     * Whether the process is connected to an interactive shell.
     */
    public const PROCESS_INTERACTIVE = 'process.interactive';

    /**
     * The control group associated with the process.
     * Control groups (cgroups) are a kernel feature used to organize and manage process resources. This attribute provides the path(s) to the cgroup(s) associated with the process, which should match the contents of the [/proc/[PID]/cgroup](https://man7.org/linux/man-pages/man7/cgroups.7.html) file.
     */
    public const PROCESS_LINUX_CGROUP = 'process.linux.cgroup';

    /**
     * The username of the user that owns the process.
     */
    public const PROCESS_OWNER = 'process.owner';

    /**
     * The type of page fault for this data point. Type `major` is for major/hard page faults, and `minor` is for minor/soft page faults.
     */
    public const PROCESS_PAGING_FAULT_TYPE = 'process.paging.fault_type';

    /**
     * Parent Process identifier (PPID).
     */
    public const PROCESS_PARENT_PID = 'process.parent_pid';

    /**
     * Process identifier (PID).
     */
    public const PROCESS_PID = 'process.pid';

    /**
     * The real user ID (RUID) of the process.
     */
    public const PROCESS_REAL_USER_ID = 'process.real_user.id';

    /**
     * The username of the real user of the process.
     */
    public const PROCESS_REAL_USER_NAME = 'process.real_user.name';

    /**
     * An additional description about the runtime of the process, for example a specific vendor customization of the runtime environment.
     */
    public const PROCESS_RUNTIME_DESCRIPTION = 'process.runtime.description';

    /**
     * The name of the runtime of this process.
     */
    public const PROCESS_RUNTIME_NAME = 'process.runtime.name';

    /**
     * The version of the runtime of this process, as returned by the runtime without modification.
     */
    public const PROCESS_RUNTIME_VERSION = 'process.runtime.version';

    /**
     * The saved user ID (SUID) of the process.
     */
    public const PROCESS_SAVED_USER_ID = 'process.saved_user.id';

    /**
     * The username of the saved user.
     */
    public const PROCESS_SAVED_USER_NAME = 'process.saved_user.name';

    /**
     * The PID of the process's session leader. This is also the session ID (SID) of the process.
     */
    public const PROCESS_SESSION_LEADER_PID = 'process.session_leader.pid';

    /**
     * Process title (proctitle)
     *
     * In many Unix-like systems, process title (proctitle), is the string that represents the name or command line of a running process, displayed by system monitoring tools like ps, top, and htop.
     */
    public const PROCESS_TITLE = 'process.title';

    /**
     * The effective user ID (EUID) of the process.
     */
    public const PROCESS_USER_ID = 'process.user.id';

    /**
     * The username of the effective user of the process.
     */
    public const PROCESS_USER_NAME = 'process.user.name';

    /**
     * Virtual process identifier.
     *
     * The process ID within a PID namespace. This is not necessarily unique across all processes on the host but it is unique within the process namespace that the process exists within.
     */
    public const PROCESS_VPID = 'process.vpid';

    /**
     * The working directory of the process.
     */
    public const PROCESS_WORKING_DIRECTORY = 'process.working_directory';

    /**
     * The [error codes](https://connectrpc.com//docs/protocol/#error-codes) of the Connect request. Error codes are always string values.
     */
    public const RPC_CONNECT_RPC_ERROR_CODE = 'rpc.connect_rpc.error_code';

    /**
     * Connect request metadata, `<key>` being the normalized Connect Metadata key (lowercase), the value being the metadata values.
     *
     * Instrumentations SHOULD require an explicit configuration of which metadata values are to be captured.
     * Including all request metadata values can be a security risk - explicit configuration helps avoid leaking sensitive information.
     *
     * For example, a property `my-custom-key` with value `["1.2.3.4", "1.2.3.5"]` SHOULD be recorded as
     * the `rpc.connect_rpc.request.metadata.my-custom-key` attribute with value `["1.2.3.4", "1.2.3.5"]`
     */
    public const RPC_CONNECT_RPC_REQUEST_METADATA = 'rpc.connect_rpc.request.metadata';

    /**
     * Connect response metadata, `<key>` being the normalized Connect Metadata key (lowercase), the value being the metadata values.
     *
     * Instrumentations SHOULD require an explicit configuration of which metadata values are to be captured.
     * Including all response metadata values can be a security risk - explicit configuration helps avoid leaking sensitive information.
     *
     * For example, a property `my-custom-key` with value `"attribute_value"` SHOULD be recorded as
     * the `rpc.connect_rpc.response.metadata.my-custom-key` attribute with value `["attribute_value"]`
     */
    public const RPC_CONNECT_RPC_RESPONSE_METADATA = 'rpc.connect_rpc.response.metadata';

    /**
     * gRPC request metadata, `<key>` being the normalized gRPC Metadata key (lowercase), the value being the metadata values.
     *
     * Instrumentations SHOULD require an explicit configuration of which metadata values are to be captured.
     * Including all request metadata values can be a security risk - explicit configuration helps avoid leaking sensitive information.
     *
     * For example, a property `my-custom-key` with value `["1.2.3.4", "1.2.3.5"]` SHOULD be recorded as
     * `rpc.grpc.request.metadata.my-custom-key` attribute with value `["1.2.3.4", "1.2.3.5"]`
     */
    public const RPC_GRPC_REQUEST_METADATA = 'rpc.grpc.request.metadata';

    /**
     * gRPC response metadata, `<key>` being the normalized gRPC Metadata key (lowercase), the value being the metadata values.
     *
     * Instrumentations SHOULD require an explicit configuration of which metadata values are to be captured.
     * Including all response metadata values can be a security risk - explicit configuration helps avoid leaking sensitive information.
     *
     * For example, a property `my-custom-key` with value `["attribute_value"]` SHOULD be recorded as
     * the `rpc.grpc.response.metadata.my-custom-key` attribute with value `["attribute_value"]`
     */
    public const RPC_GRPC_RESPONSE_METADATA = 'rpc.grpc.response.metadata';

    /**
     * The [numeric status code](https://github.com/grpc/grpc/blob/v1.33.2/doc/statuscodes.md) of the gRPC request.
     */
    public const RPC_GRPC_STATUS_CODE = 'rpc.grpc.status_code';

    /**
     * `error.code` property of response if it is an error response.
     */
    public const RPC_JSONRPC_ERROR_CODE = 'rpc.jsonrpc.error_code';

    /**
     * `error.message` property of response if it is an error response.
     */
    public const RPC_JSONRPC_ERROR_MESSAGE = 'rpc.jsonrpc.error_message';

    /**
     * `id` property of request or response. Since protocol allows id to be int, string, `null` or missing (for notifications), value is expected to be cast to string for simplicity. Use empty string in case of `null` value. Omit entirely if this is a notification.
     */
    public const RPC_JSONRPC_REQUEST_ID = 'rpc.jsonrpc.request_id';

    /**
     * Protocol version as in `jsonrpc` property of request/response. Since JSON-RPC 1.0 doesn't specify this, the value can be omitted.
     */
    public const RPC_JSONRPC_VERSION = 'rpc.jsonrpc.version';

    /**
     * Compressed size of the message in bytes.
     */
    public const RPC_MESSAGE_COMPRESSED_SIZE = 'rpc.message.compressed_size';

    /**
     * MUST be calculated as two different counters starting from `1` one for sent messages and one for received message.
     * This way we guarantee that the values will be consistent between different implementations.
     */
    public const RPC_MESSAGE_ID = 'rpc.message.id';

    /**
     * Whether this is a received or sent message.
     */
    public const RPC_MESSAGE_TYPE = 'rpc.message.type';

    /**
     * Uncompressed size of the message in bytes.
     */
    public const RPC_MESSAGE_UNCOMPRESSED_SIZE = 'rpc.message.uncompressed_size';

    /**
     * The name of the (logical) method being called, must be equal to the $method part in the span name.
     * This is the logical name of the method from the RPC interface perspective, which can be different from the name of any implementing method/function. The `code.function.name` attribute may be used to store the latter (e.g., method actually executing the call on the server side, RPC client stub method on the client side).
     */
    public const RPC_METHOD = 'rpc.method';

    /**
     * The full (logical) name of the service being called, including its package name, if applicable.
     * This is the logical name of the service from the RPC interface perspective, which can be different from the name of any implementing class. The `code.namespace` attribute may be used to store the latter (despite the attribute name, it may include a class name; e.g., class with method actually executing the call on the server side, RPC client stub class on the client side).
     */
    public const RPC_SERVICE = 'rpc.service';

    /**
     * A string identifying the remoting system. See below for a list of well-known identifiers.
     */
    public const RPC_SYSTEM = 'rpc.system';

    /**
     * A categorization value keyword used by the entity using the rule for detection of this event
     */
    public const SECURITY_RULE_CATEGORY = 'security_rule.category';

    /**
     * The description of the rule generating the event.
     */
    public const SECURITY_RULE_DESCRIPTION = 'security_rule.description';

    /**
     * Name of the license under which the rule used to generate this event is made available.
     */
    public const SECURITY_RULE_LICENSE = 'security_rule.license';

    /**
     * The name of the rule or signature generating the event.
     */
    public const SECURITY_RULE_NAME = 'security_rule.name';

    /**
     * Reference URL to additional information about the rule used to generate this event.
     *
     * The URL can point to the vendor’s documentation about the rule. If that’s not available, it can also be a link to a more general page describing this type of alert.
     */
    public const SECURITY_RULE_REFERENCE = 'security_rule.reference';

    /**
     * Name of the ruleset, policy, group, or parent category in which the rule used to generate this event is a member.
     */
    public const SECURITY_RULE_RULESET_NAME = 'security_rule.ruleset.name';

    /**
     * A rule ID that is unique within the scope of a set or group of agents, observers, or other entities using the rule for detection of this event.
     */
    public const SECURITY_RULE_UUID = 'security_rule.uuid';

    /**
     * The version / revision of the rule being used for analysis.
     */
    public const SECURITY_RULE_VERSION = 'security_rule.version';

    /**
     * Server domain name if available without reverse DNS lookup; otherwise, IP address or Unix domain socket name.
     * When observed from the client side, and when communicating through an intermediary, `server.address` SHOULD represent the server address behind any intermediaries, for example proxies, if it's available.
     */
    public const SERVER_ADDRESS = 'server.address';

    /**
     * Server port number.
     * When observed from the client side, and when communicating through an intermediary, `server.port` SHOULD represent the server port behind any intermediaries, for example proxies, if it's available.
     */
    public const SERVER_PORT = 'server.port';

    /**
     * The string ID of the service instance.
     *
     * MUST be unique for each instance of the same `service.namespace,service.name` pair (in other words
     * `service.namespace,service.name,service.instance.id` triplet MUST be globally unique). The ID helps to
     * distinguish instances of the same service that exist at the same time (e.g. instances of a horizontally scaled
     * service).
     *
     * Implementations, such as SDKs, are recommended to generate a random Version 1 or Version 4 [RFC
     * 4122](https://www.ietf.org/rfc/rfc4122.txt) UUID, but are free to use an inherent unique ID as the source of
     * this value if stability is desirable. In that case, the ID SHOULD be used as source of a UUID Version 5 and
     * SHOULD use the following UUID as the namespace: `4d63009a-8d0f-11ee-aad7-4c796ed8e320`.
     *
     * UUIDs are typically recommended, as only an opaque value for the purposes of identifying a service instance is
     * needed. Similar to what can be seen in the man page for the
     * [`/etc/machine-id`](https://www.freedesktop.org/software/systemd/man/latest/machine-id.html) file, the underlying
     * data, such as pod name and namespace should be treated as confidential, being the user's choice to expose it
     * or not via another resource attribute.
     *
     * For applications running behind an application server (like unicorn), we do not recommend using one identifier
     * for all processes participating in the application. Instead, it's recommended each division (e.g. a worker
     * thread in unicorn) to have its own instance.id.
     *
     * It's not recommended for a Collector to set `service.instance.id` if it can't unambiguously determine the
     * service instance that is generating that telemetry. For instance, creating an UUID based on `pod.name` will
     * likely be wrong, as the Collector might not know from which container within that pod the telemetry originated.
     * However, Collectors can set the `service.instance.id` if they can unambiguously determine the service instance
     * for that telemetry. This is typically the case for scraping receivers, as they know the target address and
     * port.
     */
    public const SERVICE_INSTANCE_ID = 'service.instance.id';

    /**
     * Logical name of the service.
     *
     * MUST be the same for all instances of horizontally scaled services. If the value was not specified, SDKs MUST fallback to `unknown_service:` concatenated with [`process.executable.name`](process.md), e.g. `unknown_service:bash`. If `process.executable.name` is not available, the value MUST be set to `unknown_service`.
     */
    public const SERVICE_NAME = 'service.name';

    /**
     * A namespace for `service.name`.
     *
     * A string value having a meaning that helps to distinguish a group of services, for example the team name that owns a group of services. `service.name` is expected to be unique within the same namespace. If `service.namespace` is not specified in the Resource then `service.name` is expected to be unique for all services that have no explicit namespace defined (so the empty/unspecified namespace is simply one more valid namespace). Zero-length namespace string is assumed equal to unspecified namespace.
     */
    public const SERVICE_NAMESPACE = 'service.namespace';

    /**
     * The version string of the service API or implementation. The format is not defined by these conventions.
     */
    public const SERVICE_VERSION = 'service.version';

    /**
     * A unique id to identify a session.
     */
    public const SESSION_ID = 'session.id';

    /**
     * The previous `session.id` for this user, when known.
     */
    public const SESSION_PREVIOUS_ID = 'session.previous_id';

    /**
     * SignalR HTTP connection closure status.
     */
    public const SIGNALR_CONNECTION_STATUS = 'signalr.connection.status';

    /**
     * [SignalR transport type](https://github.com/dotnet/aspnetcore/blob/main/src/SignalR/docs/specs/TransportProtocols.md)
     */
    public const SIGNALR_TRANSPORT = 'signalr.transport';

    /**
     * Source address - domain name if available without reverse DNS lookup; otherwise, IP address or Unix domain socket name.
     * When observed from the destination side, and when communicating through an intermediary, `source.address` SHOULD represent the source address behind any intermediaries, for example proxies, if it's available.
     */
    public const SOURCE_ADDRESS = 'source.address';

    /**
     * Source port number
     */
    public const SOURCE_PORT = 'source.port';

    /**
     * Deprecated, use `cpu.logical_number` instead.
     */
    public const SYSTEM_CPU_LOGICAL_NUMBER = 'system.cpu.logical_number';

    /**
     * Deprecated, use `cpu.mode` instead.
     *
     * @deprecated {"note": "Replaced by `cpu.mode`", "reason": "uncategorized"}
     */
    public const SYSTEM_CPU_STATE = 'system.cpu.state';

    /**
     * The device identifier
     */
    public const SYSTEM_DEVICE = 'system.device';

    /**
     * The filesystem mode
     */
    public const SYSTEM_FILESYSTEM_MODE = 'system.filesystem.mode';

    /**
     * The filesystem mount path
     */
    public const SYSTEM_FILESYSTEM_MOUNTPOINT = 'system.filesystem.mountpoint';

    /**
     * The filesystem state
     */
    public const SYSTEM_FILESYSTEM_STATE = 'system.filesystem.state';

    /**
     * The filesystem type
     */
    public const SYSTEM_FILESYSTEM_TYPE = 'system.filesystem.type';

    /**
     * The memory state
     */
    public const SYSTEM_MEMORY_STATE = 'system.memory.state';

    /**
     * Deprecated, use `network.connection.state` instead.
     *
     * @deprecated {"note": "Removed, report network connection state with `network.connection.state` attribute", "reason": "uncategorized"}
     */
    public const SYSTEM_NETWORK_STATE = 'system.network.state';

    /**
     * The paging access direction
     */
    public const SYSTEM_PAGING_DIRECTION = 'system.paging.direction';

    /**
     * The memory paging state
     */
    public const SYSTEM_PAGING_STATE = 'system.paging.state';

    /**
     * The memory paging type
     */
    public const SYSTEM_PAGING_TYPE = 'system.paging.type';

    /**
     * The process state, e.g., [Linux Process State Codes](https://man7.org/linux/man-pages/man1/ps.1.html#PROCESS_STATE_CODES)
     */
    public const SYSTEM_PROCESS_STATUS = 'system.process.status';

    /**
     * Deprecated, use `system.process.status` instead.
     *
     * @deprecated {"note": "Replaced by `system.process.status`.", "reason": "uncategorized"}
     */
    public const SYSTEM_PROCESSES_STATUS = 'system.processes.status';

    /**
     * The name of the auto instrumentation agent or distribution, if used.
     *
     * Official auto instrumentation agents and distributions SHOULD set the `telemetry.distro.name` attribute to
     * a string starting with `opentelemetry-`, e.g. `opentelemetry-java-instrumentation`.
     */
    public const TELEMETRY_DISTRO_NAME = 'telemetry.distro.name';

    /**
     * The version string of the auto instrumentation agent or distribution, if used.
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
     */
    public const TELEMETRY_SDK_NAME = 'telemetry.sdk.name';

    /**
     * The version string of the telemetry SDK.
     */
    public const TELEMETRY_SDK_VERSION = 'telemetry.sdk.version';

    /**
     * The fully qualified human readable name of the [test case](https://wikipedia.org/wiki/Test_case).
     */
    public const TEST_CASE_NAME = 'test.case.name';

    /**
     * The status of the actual test case result from test execution.
     */
    public const TEST_CASE_RESULT_STATUS = 'test.case.result.status';

    /**
     * The human readable name of a [test suite](https://wikipedia.org/wiki/Test_suite).
     */
    public const TEST_SUITE_NAME = 'test.suite.name';

    /**
     * The status of the test suite run.
     */
    public const TEST_SUITE_RUN_STATUS = 'test.suite.run.status';

    /**
     * Current "managed" thread ID (as opposed to OS thread ID).
     */
    public const THREAD_ID = 'thread.id';

    /**
     * Current thread name.
     */
    public const THREAD_NAME = 'thread.name';

    /**
     * String indicating the [cipher](https://datatracker.ietf.org/doc/html/rfc5246#appendix-A.5) used during the current connection.
     *
     * The values allowed for `tls.cipher` MUST be one of the `Descriptions` of the [registered TLS Cipher Suits](https://www.iana.org/assignments/tls-parameters/tls-parameters.xhtml#table-tls-parameters-4).
     */
    public const TLS_CIPHER = 'tls.cipher';

    /**
     * PEM-encoded stand-alone certificate offered by the client. This is usually mutually-exclusive of `client.certificate_chain` since this value also exists in that list.
     */
    public const TLS_CLIENT_CERTIFICATE = 'tls.client.certificate';

    /**
     * Array of PEM-encoded certificates that make up the certificate chain offered by the client. This is usually mutually-exclusive of `client.certificate` since that value should be the first certificate in the chain.
     */
    public const TLS_CLIENT_CERTIFICATE_CHAIN = 'tls.client.certificate_chain';

    /**
     * Certificate fingerprint using the MD5 digest of DER-encoded version of certificate offered by the client. For consistency with other hash values, this value should be formatted as an uppercase hash.
     */
    public const TLS_CLIENT_HASH_MD5 = 'tls.client.hash.md5';

    /**
     * Certificate fingerprint using the SHA1 digest of DER-encoded version of certificate offered by the client. For consistency with other hash values, this value should be formatted as an uppercase hash.
     */
    public const TLS_CLIENT_HASH_SHA1 = 'tls.client.hash.sha1';

    /**
     * Certificate fingerprint using the SHA256 digest of DER-encoded version of certificate offered by the client. For consistency with other hash values, this value should be formatted as an uppercase hash.
     */
    public const TLS_CLIENT_HASH_SHA256 = 'tls.client.hash.sha256';

    /**
     * Distinguished name of [subject](https://datatracker.ietf.org/doc/html/rfc5280#section-4.1.2.6) of the issuer of the x.509 certificate presented by the client.
     */
    public const TLS_CLIENT_ISSUER = 'tls.client.issuer';

    /**
     * A hash that identifies clients based on how they perform an SSL/TLS handshake.
     */
    public const TLS_CLIENT_JA3 = 'tls.client.ja3';

    /**
     * Date/Time indicating when client certificate is no longer considered valid.
     */
    public const TLS_CLIENT_NOT_AFTER = 'tls.client.not_after';

    /**
     * Date/Time indicating when client certificate is first considered valid.
     */
    public const TLS_CLIENT_NOT_BEFORE = 'tls.client.not_before';

    /**
     * Deprecated, use `server.address` instead.
     *
     * @deprecated {"note": "Replaced by `server.address`.", "reason": "uncategorized"}
     */
    public const TLS_CLIENT_SERVER_NAME = 'tls.client.server_name';

    /**
     * Distinguished name of subject of the x.509 certificate presented by the client.
     */
    public const TLS_CLIENT_SUBJECT = 'tls.client.subject';

    /**
     * Array of ciphers offered by the client during the client hello.
     */
    public const TLS_CLIENT_SUPPORTED_CIPHERS = 'tls.client.supported_ciphers';

    /**
     * String indicating the curve used for the given cipher, when applicable
     */
    public const TLS_CURVE = 'tls.curve';

    /**
     * Boolean flag indicating if the TLS negotiation was successful and transitioned to an encrypted tunnel.
     */
    public const TLS_ESTABLISHED = 'tls.established';

    /**
     * String indicating the protocol being tunneled. Per the values in the [IANA registry](https://www.iana.org/assignments/tls-extensiontype-values/tls-extensiontype-values.xhtml#alpn-protocol-ids), this string should be lower case.
     */
    public const TLS_NEXT_PROTOCOL = 'tls.next_protocol';

    /**
     * Normalized lowercase protocol name parsed from original string of the negotiated [SSL/TLS protocol version](https://docs.openssl.org/1.1.1/man3/SSL_get_version/#return-values)
     */
    public const TLS_PROTOCOL_NAME = 'tls.protocol.name';

    /**
     * Numeric part of the version parsed from the original string of the negotiated [SSL/TLS protocol version](https://docs.openssl.org/1.1.1/man3/SSL_get_version/#return-values)
     */
    public const TLS_PROTOCOL_VERSION = 'tls.protocol.version';

    /**
     * Boolean flag indicating if this TLS connection was resumed from an existing TLS negotiation.
     */
    public const TLS_RESUMED = 'tls.resumed';

    /**
     * PEM-encoded stand-alone certificate offered by the server. This is usually mutually-exclusive of `server.certificate_chain` since this value also exists in that list.
     */
    public const TLS_SERVER_CERTIFICATE = 'tls.server.certificate';

    /**
     * Array of PEM-encoded certificates that make up the certificate chain offered by the server. This is usually mutually-exclusive of `server.certificate` since that value should be the first certificate in the chain.
     */
    public const TLS_SERVER_CERTIFICATE_CHAIN = 'tls.server.certificate_chain';

    /**
     * Certificate fingerprint using the MD5 digest of DER-encoded version of certificate offered by the server. For consistency with other hash values, this value should be formatted as an uppercase hash.
     */
    public const TLS_SERVER_HASH_MD5 = 'tls.server.hash.md5';

    /**
     * Certificate fingerprint using the SHA1 digest of DER-encoded version of certificate offered by the server. For consistency with other hash values, this value should be formatted as an uppercase hash.
     */
    public const TLS_SERVER_HASH_SHA1 = 'tls.server.hash.sha1';

    /**
     * Certificate fingerprint using the SHA256 digest of DER-encoded version of certificate offered by the server. For consistency with other hash values, this value should be formatted as an uppercase hash.
     */
    public const TLS_SERVER_HASH_SHA256 = 'tls.server.hash.sha256';

    /**
     * Distinguished name of [subject](https://datatracker.ietf.org/doc/html/rfc5280#section-4.1.2.6) of the issuer of the x.509 certificate presented by the client.
     */
    public const TLS_SERVER_ISSUER = 'tls.server.issuer';

    /**
     * A hash that identifies servers based on how they perform an SSL/TLS handshake.
     */
    public const TLS_SERVER_JA3S = 'tls.server.ja3s';

    /**
     * Date/Time indicating when server certificate is no longer considered valid.
     */
    public const TLS_SERVER_NOT_AFTER = 'tls.server.not_after';

    /**
     * Date/Time indicating when server certificate is first considered valid.
     */
    public const TLS_SERVER_NOT_BEFORE = 'tls.server.not_before';

    /**
     * Distinguished name of subject of the x.509 certificate presented by the server.
     */
    public const TLS_SERVER_SUBJECT = 'tls.server.subject';

    /**
     * Domain extracted from the `url.full`, such as "opentelemetry.io".
     *
     * In some cases a URL may refer to an IP and/or port directly, without a domain name. In this case, the IP address would go to the domain field. If the URL contains a [literal IPv6 address](https://www.rfc-editor.org/rfc/rfc2732#section-2) enclosed by `[` and `]`, the `[` and `]` characters should also be captured in the domain field.
     */
    public const URL_DOMAIN = 'url.domain';

    /**
     * The file extension extracted from the `url.full`, excluding the leading dot.
     *
     * The file extension is only set if it exists, as not every url has a file extension. When the file name has multiple extensions `example.tar.gz`, only the last one should be captured `gz`, not `tar.gz`.
     */
    public const URL_EXTENSION = 'url.extension';

    /**
     * The [URI fragment](https://www.rfc-editor.org/rfc/rfc3986#section-3.5) component
     */
    public const URL_FRAGMENT = 'url.fragment';

    /**
     * Absolute URL describing a network resource according to [RFC3986](https://www.rfc-editor.org/rfc/rfc3986)
     * For network calls, URL usually has `scheme://host[:port][path][?query][#fragment]` format, where the fragment
     * is not transmitted over HTTP, but if it is known, it SHOULD be included nevertheless.
     *
     * `url.full` MUST NOT contain credentials passed via URL in form of `https://username:password@www.example.com/`.
     * In such case username and password SHOULD be redacted and attribute's value SHOULD be `https://REDACTED:REDACTED@www.example.com/`.
     *
     * `url.full` SHOULD capture the absolute URL when it is available (or can be reconstructed).
     *
     * Sensitive content provided in `url.full` SHOULD be scrubbed when instrumentations can identify it.
     *
     *
     * Query string values for the following keys SHOULD be redacted by default and replaced by the
     * value `REDACTED`:
     *
     * - [`AWSAccessKeyId`](https://docs.aws.amazon.com/AmazonS3/latest/userguide/RESTAuthentication.html#RESTAuthenticationQueryStringAuth)
     * - [`Signature`](https://docs.aws.amazon.com/AmazonS3/latest/userguide/RESTAuthentication.html#RESTAuthenticationQueryStringAuth)
     * - [`sig`](https://learn.microsoft.com/azure/storage/common/storage-sas-overview#sas-token)
     * - [`X-Goog-Signature`](https://cloud.google.com/storage/docs/access-control/signed-urls)
     *
     * This list is subject to change over time.
     *
     * When a query string value is redacted, the query string key SHOULD still be preserved, e.g.
     * `https://www.example.com/path?color=blue&sig=REDACTED`.
     */
    public const URL_FULL = 'url.full';

    /**
     * Unmodified original URL as seen in the event source.
     *
     * In network monitoring, the observed URL may be a full URL, whereas in access logs, the URL is often just represented as a path. This field is meant to represent the URL as it was observed, complete or not.
     * `url.original` might contain credentials passed via URL in form of `https://username:password@www.example.com/`. In such case password and username SHOULD NOT be redacted and attribute's value SHOULD remain the same.
     */
    public const URL_ORIGINAL = 'url.original';

    /**
     * The [URI path](https://www.rfc-editor.org/rfc/rfc3986#section-3.3) component
     *
     * Sensitive content provided in `url.path` SHOULD be scrubbed when instrumentations can identify it.
     */
    public const URL_PATH = 'url.path';

    /**
     * Port extracted from the `url.full`
     */
    public const URL_PORT = 'url.port';

    /**
     * The [URI query](https://www.rfc-editor.org/rfc/rfc3986#section-3.4) component
     *
     * Sensitive content provided in `url.query` SHOULD be scrubbed when instrumentations can identify it.
     *
     *
     * Query string values for the following keys SHOULD be redacted by default and replaced by the value `REDACTED`:
     *
     * - [`AWSAccessKeyId`](https://docs.aws.amazon.com/AmazonS3/latest/userguide/RESTAuthentication.html#RESTAuthenticationQueryStringAuth)
     * - [`Signature`](https://docs.aws.amazon.com/AmazonS3/latest/userguide/RESTAuthentication.html#RESTAuthenticationQueryStringAuth)
     * - [`sig`](https://learn.microsoft.com/azure/storage/common/storage-sas-overview#sas-token)
     * - [`X-Goog-Signature`](https://cloud.google.com/storage/docs/access-control/signed-urls)
     *
     * This list is subject to change over time.
     *
     * When a query string value is redacted, the query string key SHOULD still be preserved, e.g.
     * `q=OpenTelemetry&sig=REDACTED`.
     */
    public const URL_QUERY = 'url.query';

    /**
     * The highest registered url domain, stripped of the subdomain.
     *
     * This value can be determined precisely with the [public suffix list](https://publicsuffix.org/). For example, the registered domain for `foo.example.com` is `example.com`. Trying to approximate this by simply taking the last two labels will not work well for TLDs such as `co.uk`.
     */
    public const URL_REGISTERED_DOMAIN = 'url.registered_domain';

    /**
     * The [URI scheme](https://www.rfc-editor.org/rfc/rfc3986#section-3.1) component identifying the used protocol.
     */
    public const URL_SCHEME = 'url.scheme';

    /**
     * The subdomain portion of a fully qualified domain name includes all of the names except the host name under the registered_domain. In a partially qualified domain, or if the qualification level of the full name cannot be determined, subdomain contains all of the names below the registered domain.
     *
     * The subdomain portion of `www.east.mydomain.co.uk` is `east`. If the domain has multiple levels of subdomain, such as `sub2.sub1.example.com`, the subdomain field should contain `sub2.sub1`, with no trailing period.
     */
    public const URL_SUBDOMAIN = 'url.subdomain';

    /**
     * The low-cardinality template of an [absolute path reference](https://www.rfc-editor.org/rfc/rfc3986#section-4.2).
     */
    public const URL_TEMPLATE = 'url.template';

    /**
     * The effective top level domain (eTLD), also known as the domain suffix, is the last part of the domain name. For example, the top level domain for example.com is `com`.
     *
     * This value can be determined precisely with the [public suffix list](https://publicsuffix.org/).
     */
    public const URL_TOP_LEVEL_DOMAIN = 'url.top_level_domain';

    /**
     * User email address.
     */
    public const USER_EMAIL = 'user.email';

    /**
     * User's full name
     */
    public const USER_FULL_NAME = 'user.full_name';

    /**
     * Unique user hash to correlate information for a user in anonymized form.
     *
     * Useful if `user.id` or `user.name` contain confidential information and cannot be used.
     */
    public const USER_HASH = 'user.hash';

    /**
     * Unique identifier of the user.
     */
    public const USER_ID = 'user.id';

    /**
     * Short name or login/username of the user.
     */
    public const USER_NAME = 'user.name';

    /**
     * Array of user roles at the time of the event.
     */
    public const USER_ROLES = 'user.roles';

    /**
     * Name of the user-agent extracted from original. Usually refers to the browser's name.
     *
     * [Example](https://www.whatsmyua.info) of extracting browser's name from original string. In the case of using a user-agent for non-browser products, such as microservices with multiple names/versions inside the `user_agent.original`, the most significant name SHOULD be selected. In such a scenario it should align with `user_agent.version`
     */
    public const USER_AGENT_NAME = 'user_agent.name';

    /**
     * Value of the [HTTP User-Agent](https://www.rfc-editor.org/rfc/rfc9110.html#field.user-agent) header sent by the client.
     */
    public const USER_AGENT_ORIGINAL = 'user_agent.original';

    /**
     * Human readable operating system name.
     * For mapping user agent strings to OS names, libraries such as [ua-parser](https://github.com/ua-parser) can be utilized.
     */
    public const USER_AGENT_OS_NAME = 'user_agent.os.name';

    /**
     * The version string of the operating system as defined in [Version Attributes](/docs/resource/README.md#version-attributes).
     *
     * For mapping user agent strings to OS versions, libraries such as [ua-parser](https://github.com/ua-parser) can be utilized.
     */
    public const USER_AGENT_OS_VERSION = 'user_agent.os.version';

    /**
     * Specifies the category of synthetic traffic, such as tests or bots.
     *
     * This attribute MAY be derived from the contents of the `user_agent.original` attribute. Components that populate the attribute are responsible for determining what they consider to be synthetic bot or test traffic. This attribute can either be set for self-identification purposes, or on telemetry detected to be generated as a result of a synthetic request. This attribute is useful for distinguishing between genuine client traffic and synthetic traffic generated by bots or tests.
     */
    public const USER_AGENT_SYNTHETIC_TYPE = 'user_agent.synthetic.type';

    /**
     * Version of the user-agent extracted from original. Usually refers to the browser's version
     *
     * [Example](https://www.whatsmyua.info) of extracting browser's version from original string. In the case of using a user-agent for non-browser products, such as microservices with multiple names/versions inside the `user_agent.original`, the most significant version SHOULD be selected. In such a scenario it should align with `user_agent.name`
     */
    public const USER_AGENT_VERSION = 'user_agent.version';

    /**
     * The type of garbage collection.
     */
    public const V8JS_GC_TYPE = 'v8js.gc.type';

    /**
     * The name of the space type of heap memory.
     * Value can be retrieved from value `space_name` of [`v8.getHeapSpaceStatistics()`](https://nodejs.org/api/v8.html#v8getheapspacestatistics)
     */
    public const V8JS_HEAP_SPACE_NAME = 'v8js.heap.space.name';

    /**
     * The ID of the change (pull request/merge request/changelist) if applicable. This is usually a unique (within repository) identifier generated by the VCS system.
     */
    public const VCS_CHANGE_ID = 'vcs.change.id';

    /**
     * The state of the change (pull request/merge request/changelist).
     */
    public const VCS_CHANGE_STATE = 'vcs.change.state';

    /**
     * The human readable title of the change (pull request/merge request/changelist). This title is often a brief summary of the change and may get merged in to a ref as the commit summary.
     */
    public const VCS_CHANGE_TITLE = 'vcs.change.title';

    /**
     * The type of line change being measured on a branch or change.
     */
    public const VCS_LINE_CHANGE_TYPE = 'vcs.line_change.type';

    /**
     * The group owner within the version control system.
     */
    public const VCS_OWNER_NAME = 'vcs.owner.name';

    /**
     * The name of the version control system provider.
     */
    public const VCS_PROVIDER_NAME = 'vcs.provider.name';

    /**
     * The name of the [reference](https://git-scm.com/docs/gitglossary#def_ref) such as **branch** or **tag** in the repository.
     *
     * `base` refers to the starting point of a change. For example, `main`
     * would be the base reference of type branch if you've created a new
     * reference of type branch from it and created new commits.
     */
    public const VCS_REF_BASE_NAME = 'vcs.ref.base.name';

    /**
     * The revision, literally [revised version](https://www.merriam-webster.com/dictionary/revision), The revision most often refers to a commit object in Git, or a revision number in SVN.
     *
     * `base` refers to the starting point of a change. For example, `main`
     * would be the base reference of type branch if you've created a new
     * reference of type branch from it and created new commits. The
     * revision can be a full [hash value (see
     * glossary)](https://nvlpubs.nist.gov/nistpubs/FIPS/NIST.FIPS.186-5.pdf),
     * of the recorded change to a ref within a repository pointing to a
     * commit [commit](https://git-scm.com/docs/git-commit) object. It does
     * not necessarily have to be a hash; it can simply define a [revision
     * number](https://svnbook.red-bean.com/en/1.7/svn.tour.revs.specifiers.html)
     * which is an integer that is monotonically increasing. In cases where
     * it is identical to the `ref.base.name`, it SHOULD still be included.
     * It is up to the implementer to decide which value to set as the
     * revision based on the VCS system and situational context.
     */
    public const VCS_REF_BASE_REVISION = 'vcs.ref.base.revision';

    /**
     * The type of the [reference](https://git-scm.com/docs/gitglossary#def_ref) in the repository.
     *
     * `base` refers to the starting point of a change. For example, `main`
     * would be the base reference of type branch if you've created a new
     * reference of type branch from it and created new commits.
     */
    public const VCS_REF_BASE_TYPE = 'vcs.ref.base.type';

    /**
     * The name of the [reference](https://git-scm.com/docs/gitglossary#def_ref) such as **branch** or **tag** in the repository.
     *
     * `head` refers to where you are right now; the current reference at a
     * given time.
     */
    public const VCS_REF_HEAD_NAME = 'vcs.ref.head.name';

    /**
     * The revision, literally [revised version](https://www.merriam-webster.com/dictionary/revision), The revision most often refers to a commit object in Git, or a revision number in SVN.
     *
     * `head` refers to where you are right now; the current reference at a
     * given time.The revision can be a full [hash value (see
     * glossary)](https://nvlpubs.nist.gov/nistpubs/FIPS/NIST.FIPS.186-5.pdf),
     * of the recorded change to a ref within a repository pointing to a
     * commit [commit](https://git-scm.com/docs/git-commit) object. It does
     * not necessarily have to be a hash; it can simply define a [revision
     * number](https://svnbook.red-bean.com/en/1.7/svn.tour.revs.specifiers.html)
     * which is an integer that is monotonically increasing. In cases where
     * it is identical to the `ref.head.name`, it SHOULD still be included.
     * It is up to the implementer to decide which value to set as the
     * revision based on the VCS system and situational context.
     */
    public const VCS_REF_HEAD_REVISION = 'vcs.ref.head.revision';

    /**
     * The type of the [reference](https://git-scm.com/docs/gitglossary#def_ref) in the repository.
     *
     * `head` refers to where you are right now; the current reference at a
     * given time.
     */
    public const VCS_REF_HEAD_TYPE = 'vcs.ref.head.type';

    /**
     * The type of the [reference](https://git-scm.com/docs/gitglossary#def_ref) in the repository.
     */
    public const VCS_REF_TYPE = 'vcs.ref.type';

    /**
     * Deprecated, use `vcs.change.id` instead.
     *
     * @deprecated {"note": "Deprecated, use `vcs.change.id` instead.", "reason": "uncategorized"}
     */
    public const VCS_REPOSITORY_CHANGE_ID = 'vcs.repository.change.id';

    /**
     * Deprecated, use `vcs.change.title` instead.
     *
     * @deprecated {"note": "Deprecated, use `vcs.change.title` instead.", "reason": "uncategorized"}
     */
    public const VCS_REPOSITORY_CHANGE_TITLE = 'vcs.repository.change.title';

    /**
     * The human readable name of the repository. It SHOULD NOT include any additional identifier like Group/SubGroup in GitLab or organization in GitHub.
     *
     * Due to it only being the name, it can clash with forks of the same
     * repository if collecting telemetry across multiple orgs or groups in
     * the same backends.
     */
    public const VCS_REPOSITORY_NAME = 'vcs.repository.name';

    /**
     * Deprecated, use `vcs.ref.head.name` instead.
     *
     * @deprecated {"note": "Deprecated, use `vcs.ref.head.name` instead.", "reason": "uncategorized"}
     */
    public const VCS_REPOSITORY_REF_NAME = 'vcs.repository.ref.name';

    /**
     * Deprecated, use `vcs.ref.head.revision` instead.
     *
     * @deprecated {"note": "Deprecated, use `vcs.ref.head.revision` instead.", "reason": "uncategorized"}
     */
    public const VCS_REPOSITORY_REF_REVISION = 'vcs.repository.ref.revision';

    /**
     * Deprecated, use `vcs.ref.head.type` instead.
     *
     * @deprecated {"note": "Deprecated, use `vcs.ref.head.type` instead.", "reason": "uncategorized"}
     */
    public const VCS_REPOSITORY_REF_TYPE = 'vcs.repository.ref.type';

    /**
     * The [canonical URL](https://support.google.com/webmasters/answer/10347851?hl=en#:~:text=A%20canonical%20URL%20is%20the,Google%20chooses%20one%20as%20canonical.) of the repository providing the complete HTTP(S) address in order to locate and identify the repository through a browser.
     *
     * In Git Version Control Systems, the canonical URL SHOULD NOT include
     * the `.git` extension.
     */
    public const VCS_REPOSITORY_URL_FULL = 'vcs.repository.url.full';

    /**
     * The type of revision comparison.
     */
    public const VCS_REVISION_DELTA_DIRECTION = 'vcs.revision_delta.direction';

    /**
     * Additional description of the web engine (e.g. detailed version and edition information).
     */
    public const WEBENGINE_DESCRIPTION = 'webengine.description';

    /**
     * The name of the web engine.
     */
    public const WEBENGINE_NAME = 'webengine.name';

    /**
     * The version of the web engine.
     */
    public const WEBENGINE_VERSION = 'webengine.version';

}
