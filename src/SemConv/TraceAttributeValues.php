<?php

// DO NOT EDIT, this is an Auto-generated file from script/semantic-convention/templates/AttributeValues.php.j2

declare(strict_types=1);

namespace OpenTelemetry\SemConv;

interface TraceAttributeValues
{
    /**
     * The URL of the OpenTelemetry schema for these keys and values.
     */
    public const SCHEMA_URL = 'https://opentelemetry.io/schemas/1.25.0';
    /**
     * @see TraceAttributes::ANDROID_STATE Any time before Activity.onResume() or, if the app has no Activity, Context.startService() has been called in the app for the first time
     */
    public const ANDROID_STATE_CREATED = 'created';

    /**
     * @see TraceAttributes::ANDROID_STATE Any time after Activity.onPause() or, if the app has no Activity, Context.stopService() has been called when the app was in the foreground state
     */
    public const ANDROID_STATE_BACKGROUND = 'background';

    /**
     * @see TraceAttributes::ANDROID_STATE Any time after Activity.onResume() or, if the app has no Activity, Context.startService() has been called when the app was in either the created or background states
     */
    public const ANDROID_STATE_FOREGROUND = 'foreground';

    /**
     * @see TraceAttributes::ASPNETCORE_RATE_LIMITING_RESULT Lease was acquired
     */
    public const ASPNETCORE_RATE_LIMITING_RESULT_ACQUIRED = 'acquired';

    /**
     * @see TraceAttributes::ASPNETCORE_RATE_LIMITING_RESULT Lease request was rejected by the endpoint limiter
     */
    public const ASPNETCORE_RATE_LIMITING_RESULT_ENDPOINT_LIMITER = 'endpoint_limiter';

    /**
     * @see TraceAttributes::ASPNETCORE_RATE_LIMITING_RESULT Lease request was rejected by the global limiter
     */
    public const ASPNETCORE_RATE_LIMITING_RESULT_GLOBAL_LIMITER = 'global_limiter';

    /**
     * @see TraceAttributes::ASPNETCORE_RATE_LIMITING_RESULT Lease request was canceled
     */
    public const ASPNETCORE_RATE_LIMITING_RESULT_REQUEST_CANCELED = 'request_canceled';

    /**
     * @see TraceAttributes::CLOUD_PLATFORM Alibaba Cloud Elastic Compute Service
     */
    public const CLOUD_PLATFORM_ALIBABA_CLOUD_ECS = 'alibaba_cloud_ecs';

    /**
     * @see TraceAttributes::CLOUD_PLATFORM Alibaba Cloud Function Compute
     */
    public const CLOUD_PLATFORM_ALIBABA_CLOUD_FC = 'alibaba_cloud_fc';

    /**
     * @see TraceAttributes::CLOUD_PLATFORM Red Hat OpenShift on Alibaba Cloud
     */
    public const CLOUD_PLATFORM_ALIBABA_CLOUD_OPENSHIFT = 'alibaba_cloud_openshift';

    /**
     * @see TraceAttributes::CLOUD_PLATFORM AWS Elastic Compute Cloud
     */
    public const CLOUD_PLATFORM_AWS_EC2 = 'aws_ec2';

    /**
     * @see TraceAttributes::CLOUD_PLATFORM AWS Elastic Container Service
     */
    public const CLOUD_PLATFORM_AWS_ECS = 'aws_ecs';

    /**
     * @see TraceAttributes::CLOUD_PLATFORM AWS Elastic Kubernetes Service
     */
    public const CLOUD_PLATFORM_AWS_EKS = 'aws_eks';

    /**
     * @see TraceAttributes::CLOUD_PLATFORM AWS Lambda
     */
    public const CLOUD_PLATFORM_AWS_LAMBDA = 'aws_lambda';

    /**
     * @see TraceAttributes::CLOUD_PLATFORM AWS Elastic Beanstalk
     */
    public const CLOUD_PLATFORM_AWS_ELASTIC_BEANSTALK = 'aws_elastic_beanstalk';

    /**
     * @see TraceAttributes::CLOUD_PLATFORM AWS App Runner
     */
    public const CLOUD_PLATFORM_AWS_APP_RUNNER = 'aws_app_runner';

    /**
     * @see TraceAttributes::CLOUD_PLATFORM Red Hat OpenShift on AWS (ROSA)
     */
    public const CLOUD_PLATFORM_AWS_OPENSHIFT = 'aws_openshift';

    /**
     * @see TraceAttributes::CLOUD_PLATFORM Azure Virtual Machines
     */
    public const CLOUD_PLATFORM_AZURE_VM = 'azure_vm';

    /**
     * @see TraceAttributes::CLOUD_PLATFORM Azure Container Apps
     */
    public const CLOUD_PLATFORM_AZURE_CONTAINER_APPS = 'azure_container_apps';

    /**
     * @see TraceAttributes::CLOUD_PLATFORM Azure Container Instances
     */
    public const CLOUD_PLATFORM_AZURE_CONTAINER_INSTANCES = 'azure_container_instances';

    /**
     * @see TraceAttributes::CLOUD_PLATFORM Azure Kubernetes Service
     */
    public const CLOUD_PLATFORM_AZURE_AKS = 'azure_aks';

    /**
     * @see TraceAttributes::CLOUD_PLATFORM Azure Functions
     */
    public const CLOUD_PLATFORM_AZURE_FUNCTIONS = 'azure_functions';

    /**
     * @see TraceAttributes::CLOUD_PLATFORM Azure App Service
     */
    public const CLOUD_PLATFORM_AZURE_APP_SERVICE = 'azure_app_service';

    /**
     * @see TraceAttributes::CLOUD_PLATFORM Azure Red Hat OpenShift
     */
    public const CLOUD_PLATFORM_AZURE_OPENSHIFT = 'azure_openshift';

    /**
     * @see TraceAttributes::CLOUD_PLATFORM Google Bare Metal Solution (BMS)
     */
    public const CLOUD_PLATFORM_GCP_BARE_METAL_SOLUTION = 'gcp_bare_metal_solution';

    /**
     * @see TraceAttributes::CLOUD_PLATFORM Google Cloud Compute Engine (GCE)
     */
    public const CLOUD_PLATFORM_GCP_COMPUTE_ENGINE = 'gcp_compute_engine';

    /**
     * @see TraceAttributes::CLOUD_PLATFORM Google Cloud Run
     */
    public const CLOUD_PLATFORM_GCP_CLOUD_RUN = 'gcp_cloud_run';

    /**
     * @see TraceAttributes::CLOUD_PLATFORM Google Cloud Kubernetes Engine (GKE)
     */
    public const CLOUD_PLATFORM_GCP_KUBERNETES_ENGINE = 'gcp_kubernetes_engine';

    /**
     * @see TraceAttributes::CLOUD_PLATFORM Google Cloud Functions (GCF)
     */
    public const CLOUD_PLATFORM_GCP_CLOUD_FUNCTIONS = 'gcp_cloud_functions';

    /**
     * @see TraceAttributes::CLOUD_PLATFORM Google Cloud App Engine (GAE)
     */
    public const CLOUD_PLATFORM_GCP_APP_ENGINE = 'gcp_app_engine';

    /**
     * @see TraceAttributes::CLOUD_PLATFORM Red Hat OpenShift on Google Cloud
     */
    public const CLOUD_PLATFORM_GCP_OPENSHIFT = 'gcp_openshift';

    /**
     * @see TraceAttributes::CLOUD_PLATFORM Red Hat OpenShift on IBM Cloud
     */
    public const CLOUD_PLATFORM_IBM_CLOUD_OPENSHIFT = 'ibm_cloud_openshift';

    /**
     * @see TraceAttributes::CLOUD_PLATFORM Tencent Cloud Cloud Virtual Machine (CVM)
     */
    public const CLOUD_PLATFORM_TENCENT_CLOUD_CVM = 'tencent_cloud_cvm';

    /**
     * @see TraceAttributes::CLOUD_PLATFORM Tencent Cloud Elastic Kubernetes Service (EKS)
     */
    public const CLOUD_PLATFORM_TENCENT_CLOUD_EKS = 'tencent_cloud_eks';

    /**
     * @see TraceAttributes::CLOUD_PLATFORM Tencent Cloud Serverless Cloud Function (SCF)
     */
    public const CLOUD_PLATFORM_TENCENT_CLOUD_SCF = 'tencent_cloud_scf';

    /**
     * @see TraceAttributes::CLOUD_PROVIDER Alibaba Cloud
     */
    public const CLOUD_PROVIDER_ALIBABA_CLOUD = 'alibaba_cloud';

    /**
     * @see TraceAttributes::CLOUD_PROVIDER Amazon Web Services
     */
    public const CLOUD_PROVIDER_AWS = 'aws';

    /**
     * @see TraceAttributes::CLOUD_PROVIDER Microsoft Azure
     */
    public const CLOUD_PROVIDER_AZURE = 'azure';

    /**
     * @see TraceAttributes::CLOUD_PROVIDER Google Cloud Platform
     */
    public const CLOUD_PROVIDER_GCP = 'gcp';

    /**
     * @see TraceAttributes::CLOUD_PROVIDER Heroku Platform as a Service
     */
    public const CLOUD_PROVIDER_HEROKU = 'heroku';

    /**
     * @see TraceAttributes::CLOUD_PROVIDER IBM Cloud
     */
    public const CLOUD_PROVIDER_IBM_CLOUD = 'ibm_cloud';

    /**
     * @see TraceAttributes::CLOUD_PROVIDER Tencent Cloud
     */
    public const CLOUD_PROVIDER_TENCENT_CLOUD = 'tencent_cloud';

    /**
     * @see TraceAttributes::CONTAINER_CPU_STATE When tasks of the cgroup are in user mode (Linux). When all container processes are in user mode (Windows)
     */
    public const CONTAINER_CPU_STATE_USER = 'user';

    /**
     * @see TraceAttributes::CONTAINER_CPU_STATE When CPU is used by the system (host OS)
     */
    public const CONTAINER_CPU_STATE_SYSTEM = 'system';

    /**
     * @see TraceAttributes::CONTAINER_CPU_STATE When tasks of the cgroup are in kernel mode (Linux). When all container processes are in kernel mode (Windows)
     */
    public const CONTAINER_CPU_STATE_KERNEL = 'kernel';

    /**
     * @see TraceAttributes::DB_CASSANDRA_CONSISTENCY_LEVEL all
     */
    public const DB_CASSANDRA_CONSISTENCY_LEVEL_ALL = 'all';

    /**
     * @see TraceAttributes::DB_CASSANDRA_CONSISTENCY_LEVEL each_quorum
     */
    public const DB_CASSANDRA_CONSISTENCY_LEVEL_EACH_QUORUM = 'each_quorum';

    /**
     * @see TraceAttributes::DB_CASSANDRA_CONSISTENCY_LEVEL quorum
     */
    public const DB_CASSANDRA_CONSISTENCY_LEVEL_QUORUM = 'quorum';

    /**
     * @see TraceAttributes::DB_CASSANDRA_CONSISTENCY_LEVEL local_quorum
     */
    public const DB_CASSANDRA_CONSISTENCY_LEVEL_LOCAL_QUORUM = 'local_quorum';

    /**
     * @see TraceAttributes::DB_CASSANDRA_CONSISTENCY_LEVEL one
     */
    public const DB_CASSANDRA_CONSISTENCY_LEVEL_ONE = 'one';

    /**
     * @see TraceAttributes::DB_CASSANDRA_CONSISTENCY_LEVEL two
     */
    public const DB_CASSANDRA_CONSISTENCY_LEVEL_TWO = 'two';

    /**
     * @see TraceAttributes::DB_CASSANDRA_CONSISTENCY_LEVEL three
     */
    public const DB_CASSANDRA_CONSISTENCY_LEVEL_THREE = 'three';

    /**
     * @see TraceAttributes::DB_CASSANDRA_CONSISTENCY_LEVEL local_one
     */
    public const DB_CASSANDRA_CONSISTENCY_LEVEL_LOCAL_ONE = 'local_one';

    /**
     * @see TraceAttributes::DB_CASSANDRA_CONSISTENCY_LEVEL any
     */
    public const DB_CASSANDRA_CONSISTENCY_LEVEL_ANY = 'any';

    /**
     * @see TraceAttributes::DB_CASSANDRA_CONSISTENCY_LEVEL serial
     */
    public const DB_CASSANDRA_CONSISTENCY_LEVEL_SERIAL = 'serial';

    /**
     * @see TraceAttributes::DB_CASSANDRA_CONSISTENCY_LEVEL local_serial
     */
    public const DB_CASSANDRA_CONSISTENCY_LEVEL_LOCAL_SERIAL = 'local_serial';

    /**
     * @see TraceAttributes::DB_COSMOSDB_CONNECTION_MODE Gateway (HTTP) connections mode
     */
    public const DB_COSMOSDB_CONNECTION_MODE_GATEWAY = 'gateway';

    /**
     * @see TraceAttributes::DB_COSMOSDB_CONNECTION_MODE Direct connection
     */
    public const DB_COSMOSDB_CONNECTION_MODE_DIRECT = 'direct';

    /**
     * @see TraceAttributes::DB_COSMOSDB_OPERATION_TYPE invalid
     */
    public const DB_COSMOSDB_OPERATION_TYPE_INVALID = 'Invalid';

    /**
     * @see TraceAttributes::DB_COSMOSDB_OPERATION_TYPE create
     */
    public const DB_COSMOSDB_OPERATION_TYPE_CREATE = 'Create';

    /**
     * @see TraceAttributes::DB_COSMOSDB_OPERATION_TYPE patch
     */
    public const DB_COSMOSDB_OPERATION_TYPE_PATCH = 'Patch';

    /**
     * @see TraceAttributes::DB_COSMOSDB_OPERATION_TYPE read
     */
    public const DB_COSMOSDB_OPERATION_TYPE_READ = 'Read';

    /**
     * @see TraceAttributes::DB_COSMOSDB_OPERATION_TYPE read_feed
     */
    public const DB_COSMOSDB_OPERATION_TYPE_READ_FEED = 'ReadFeed';

    /**
     * @see TraceAttributes::DB_COSMOSDB_OPERATION_TYPE delete
     */
    public const DB_COSMOSDB_OPERATION_TYPE_DELETE = 'Delete';

    /**
     * @see TraceAttributes::DB_COSMOSDB_OPERATION_TYPE replace
     */
    public const DB_COSMOSDB_OPERATION_TYPE_REPLACE = 'Replace';

    /**
     * @see TraceAttributes::DB_COSMOSDB_OPERATION_TYPE execute
     */
    public const DB_COSMOSDB_OPERATION_TYPE_EXECUTE = 'Execute';

    /**
     * @see TraceAttributes::DB_COSMOSDB_OPERATION_TYPE query
     */
    public const DB_COSMOSDB_OPERATION_TYPE_QUERY = 'Query';

    /**
     * @see TraceAttributes::DB_COSMOSDB_OPERATION_TYPE head
     */
    public const DB_COSMOSDB_OPERATION_TYPE_HEAD = 'Head';

    /**
     * @see TraceAttributes::DB_COSMOSDB_OPERATION_TYPE head_feed
     */
    public const DB_COSMOSDB_OPERATION_TYPE_HEAD_FEED = 'HeadFeed';

    /**
     * @see TraceAttributes::DB_COSMOSDB_OPERATION_TYPE upsert
     */
    public const DB_COSMOSDB_OPERATION_TYPE_UPSERT = 'Upsert';

    /**
     * @see TraceAttributes::DB_COSMOSDB_OPERATION_TYPE batch
     */
    public const DB_COSMOSDB_OPERATION_TYPE_BATCH = 'Batch';

    /**
     * @see TraceAttributes::DB_COSMOSDB_OPERATION_TYPE query_plan
     */
    public const DB_COSMOSDB_OPERATION_TYPE_QUERY_PLAN = 'QueryPlan';

    /**
     * @see TraceAttributes::DB_COSMOSDB_OPERATION_TYPE execute_javascript
     */
    public const DB_COSMOSDB_OPERATION_TYPE_EXECUTE_JAVASCRIPT = 'ExecuteJavaScript';

    /**
     * @see TraceAttributes::DB_SYSTEM Some other SQL database. Fallback only. See notes
     */
    public const DB_SYSTEM_OTHER_SQL = 'other_sql';

    /**
     * @see TraceAttributes::DB_SYSTEM Microsoft SQL Server
     */
    public const DB_SYSTEM_MSSQL = 'mssql';

    /**
     * @see TraceAttributes::DB_SYSTEM Microsoft SQL Server Compact
     */
    public const DB_SYSTEM_MSSQLCOMPACT = 'mssqlcompact';

    /**
     * @see TraceAttributes::DB_SYSTEM MySQL
     */
    public const DB_SYSTEM_MYSQL = 'mysql';

    /**
     * @see TraceAttributes::DB_SYSTEM Oracle Database
     */
    public const DB_SYSTEM_ORACLE = 'oracle';

    /**
     * @see TraceAttributes::DB_SYSTEM IBM Db2
     */
    public const DB_SYSTEM_DB2 = 'db2';

    /**
     * @see TraceAttributes::DB_SYSTEM PostgreSQL
     */
    public const DB_SYSTEM_POSTGRESQL = 'postgresql';

    /**
     * @see TraceAttributes::DB_SYSTEM Amazon Redshift
     */
    public const DB_SYSTEM_REDSHIFT = 'redshift';

    /**
     * @see TraceAttributes::DB_SYSTEM Apache Hive
     */
    public const DB_SYSTEM_HIVE = 'hive';

    /**
     * @see TraceAttributes::DB_SYSTEM Cloudscape
     */
    public const DB_SYSTEM_CLOUDSCAPE = 'cloudscape';

    /**
     * @see TraceAttributes::DB_SYSTEM HyperSQL DataBase
     */
    public const DB_SYSTEM_HSQLDB = 'hsqldb';

    /**
     * @see TraceAttributes::DB_SYSTEM Progress Database
     */
    public const DB_SYSTEM_PROGRESS = 'progress';

    /**
     * @see TraceAttributes::DB_SYSTEM SAP MaxDB
     */
    public const DB_SYSTEM_MAXDB = 'maxdb';

    /**
     * @see TraceAttributes::DB_SYSTEM SAP HANA
     */
    public const DB_SYSTEM_HANADB = 'hanadb';

    /**
     * @see TraceAttributes::DB_SYSTEM Ingres
     */
    public const DB_SYSTEM_INGRES = 'ingres';

    /**
     * @see TraceAttributes::DB_SYSTEM FirstSQL
     */
    public const DB_SYSTEM_FIRSTSQL = 'firstsql';

    /**
     * @see TraceAttributes::DB_SYSTEM EnterpriseDB
     */
    public const DB_SYSTEM_EDB = 'edb';

    /**
     * @see TraceAttributes::DB_SYSTEM InterSystems Caché
     */
    public const DB_SYSTEM_CACHE = 'cache';

    /**
     * @see TraceAttributes::DB_SYSTEM Adabas (Adaptable Database System)
     */
    public const DB_SYSTEM_ADABAS = 'adabas';

    /**
     * @see TraceAttributes::DB_SYSTEM Firebird
     */
    public const DB_SYSTEM_FIREBIRD = 'firebird';

    /**
     * @see TraceAttributes::DB_SYSTEM Apache Derby
     */
    public const DB_SYSTEM_DERBY = 'derby';

    /**
     * @see TraceAttributes::DB_SYSTEM FileMaker
     */
    public const DB_SYSTEM_FILEMAKER = 'filemaker';

    /**
     * @see TraceAttributes::DB_SYSTEM Informix
     */
    public const DB_SYSTEM_INFORMIX = 'informix';

    /**
     * @see TraceAttributes::DB_SYSTEM InstantDB
     */
    public const DB_SYSTEM_INSTANTDB = 'instantdb';

    /**
     * @see TraceAttributes::DB_SYSTEM InterBase
     */
    public const DB_SYSTEM_INTERBASE = 'interbase';

    /**
     * @see TraceAttributes::DB_SYSTEM MariaDB
     */
    public const DB_SYSTEM_MARIADB = 'mariadb';

    /**
     * @see TraceAttributes::DB_SYSTEM Netezza
     */
    public const DB_SYSTEM_NETEZZA = 'netezza';

    /**
     * @see TraceAttributes::DB_SYSTEM Pervasive PSQL
     */
    public const DB_SYSTEM_PERVASIVE = 'pervasive';

    /**
     * @see TraceAttributes::DB_SYSTEM PointBase
     */
    public const DB_SYSTEM_POINTBASE = 'pointbase';

    /**
     * @see TraceAttributes::DB_SYSTEM SQLite
     */
    public const DB_SYSTEM_SQLITE = 'sqlite';

    /**
     * @see TraceAttributes::DB_SYSTEM Sybase
     */
    public const DB_SYSTEM_SYBASE = 'sybase';

    /**
     * @see TraceAttributes::DB_SYSTEM Teradata
     */
    public const DB_SYSTEM_TERADATA = 'teradata';

    /**
     * @see TraceAttributes::DB_SYSTEM Vertica
     */
    public const DB_SYSTEM_VERTICA = 'vertica';

    /**
     * @see TraceAttributes::DB_SYSTEM H2
     */
    public const DB_SYSTEM_H2 = 'h2';

    /**
     * @see TraceAttributes::DB_SYSTEM ColdFusion IMQ
     */
    public const DB_SYSTEM_COLDFUSION = 'coldfusion';

    /**
     * @see TraceAttributes::DB_SYSTEM Apache Cassandra
     */
    public const DB_SYSTEM_CASSANDRA = 'cassandra';

    /**
     * @see TraceAttributes::DB_SYSTEM Apache HBase
     */
    public const DB_SYSTEM_HBASE = 'hbase';

    /**
     * @see TraceAttributes::DB_SYSTEM MongoDB
     */
    public const DB_SYSTEM_MONGODB = 'mongodb';

    /**
     * @see TraceAttributes::DB_SYSTEM Redis
     */
    public const DB_SYSTEM_REDIS = 'redis';

    /**
     * @see TraceAttributes::DB_SYSTEM Couchbase
     */
    public const DB_SYSTEM_COUCHBASE = 'couchbase';

    /**
     * @see TraceAttributes::DB_SYSTEM CouchDB
     */
    public const DB_SYSTEM_COUCHDB = 'couchdb';

    /**
     * @see TraceAttributes::DB_SYSTEM Microsoft Azure Cosmos DB
     */
    public const DB_SYSTEM_COSMOSDB = 'cosmosdb';

    /**
     * @see TraceAttributes::DB_SYSTEM Amazon DynamoDB
     */
    public const DB_SYSTEM_DYNAMODB = 'dynamodb';

    /**
     * @see TraceAttributes::DB_SYSTEM Neo4j
     */
    public const DB_SYSTEM_NEO4J = 'neo4j';

    /**
     * @see TraceAttributes::DB_SYSTEM Apache Geode
     */
    public const DB_SYSTEM_GEODE = 'geode';

    /**
     * @see TraceAttributes::DB_SYSTEM Elasticsearch
     */
    public const DB_SYSTEM_ELASTICSEARCH = 'elasticsearch';

    /**
     * @see TraceAttributes::DB_SYSTEM Memcached
     */
    public const DB_SYSTEM_MEMCACHED = 'memcached';

    /**
     * @see TraceAttributes::DB_SYSTEM CockroachDB
     */
    public const DB_SYSTEM_COCKROACHDB = 'cockroachdb';

    /**
     * @see TraceAttributes::DB_SYSTEM OpenSearch
     */
    public const DB_SYSTEM_OPENSEARCH = 'opensearch';

    /**
     * @see TraceAttributes::DB_SYSTEM ClickHouse
     */
    public const DB_SYSTEM_CLICKHOUSE = 'clickhouse';

    /**
     * @see TraceAttributes::DB_SYSTEM Cloud Spanner
     */
    public const DB_SYSTEM_SPANNER = 'spanner';

    /**
     * @see TraceAttributes::DB_SYSTEM Trino
     */
    public const DB_SYSTEM_TRINO = 'trino';

    /**
     * @see TraceAttributes::DISK_IO_DIRECTION read
     */
    public const DISK_IO_DIRECTION_READ = 'read';

    /**
     * @see TraceAttributes::DISK_IO_DIRECTION write
     */
    public const DISK_IO_DIRECTION_WRITE = 'write';

    /**
     * @see TraceAttributes::ERROR_TYPE A fallback error value to be used when the instrumentation doesn&#39;t define a custom value
     */
    public const ERROR_TYPE_OTHER = '_OTHER';

    /**
     * @see TraceAttributes::FAAS_DOCUMENT_OPERATION When a new object is created
     */
    public const FAAS_DOCUMENT_OPERATION_INSERT = 'insert';

    /**
     * @see TraceAttributes::FAAS_DOCUMENT_OPERATION When an object is modified
     */
    public const FAAS_DOCUMENT_OPERATION_EDIT = 'edit';

    /**
     * @see TraceAttributes::FAAS_DOCUMENT_OPERATION When an object is deleted
     */
    public const FAAS_DOCUMENT_OPERATION_DELETE = 'delete';

    /**
     * @see TraceAttributes::FAAS_INVOKED_PROVIDER Alibaba Cloud
     */
    public const FAAS_INVOKED_PROVIDER_ALIBABA_CLOUD = 'alibaba_cloud';

    /**
     * @see TraceAttributes::FAAS_INVOKED_PROVIDER Amazon Web Services
     */
    public const FAAS_INVOKED_PROVIDER_AWS = 'aws';

    /**
     * @see TraceAttributes::FAAS_INVOKED_PROVIDER Microsoft Azure
     */
    public const FAAS_INVOKED_PROVIDER_AZURE = 'azure';

    /**
     * @see TraceAttributes::FAAS_INVOKED_PROVIDER Google Cloud Platform
     */
    public const FAAS_INVOKED_PROVIDER_GCP = 'gcp';

    /**
     * @see TraceAttributes::FAAS_INVOKED_PROVIDER Tencent Cloud
     */
    public const FAAS_INVOKED_PROVIDER_TENCENT_CLOUD = 'tencent_cloud';

    /**
     * @see TraceAttributes::FAAS_TRIGGER A response to some data source operation such as a database or filesystem read/write
     */
    public const FAAS_TRIGGER_DATASOURCE = 'datasource';

    /**
     * @see TraceAttributes::FAAS_TRIGGER To provide an answer to an inbound HTTP request
     */
    public const FAAS_TRIGGER_HTTP = 'http';

    /**
     * @see TraceAttributes::FAAS_TRIGGER A function is set to be executed when messages are sent to a messaging system
     */
    public const FAAS_TRIGGER_PUBSUB = 'pubsub';

    /**
     * @see TraceAttributes::FAAS_TRIGGER A function is scheduled to be executed regularly
     */
    public const FAAS_TRIGGER_TIMER = 'timer';

    /**
     * @see TraceAttributes::FAAS_TRIGGER If none of the others apply
     */
    public const FAAS_TRIGGER_OTHER = 'other';

    /**
     * @see TraceAttributes::GRAPHQL_OPERATION_TYPE GraphQL query
     */
    public const GRAPHQL_OPERATION_TYPE_QUERY = 'query';

    /**
     * @see TraceAttributes::GRAPHQL_OPERATION_TYPE GraphQL mutation
     */
    public const GRAPHQL_OPERATION_TYPE_MUTATION = 'mutation';

    /**
     * @see TraceAttributes::GRAPHQL_OPERATION_TYPE GraphQL subscription
     */
    public const GRAPHQL_OPERATION_TYPE_SUBSCRIPTION = 'subscription';

    /**
     * @see TraceAttributes::HOST_ARCH AMD64
     */
    public const HOST_ARCH_AMD64 = 'amd64';

    /**
     * @see TraceAttributes::HOST_ARCH ARM32
     */
    public const HOST_ARCH_ARM32 = 'arm32';

    /**
     * @see TraceAttributes::HOST_ARCH ARM64
     */
    public const HOST_ARCH_ARM64 = 'arm64';

    /**
     * @see TraceAttributes::HOST_ARCH Itanium
     */
    public const HOST_ARCH_IA64 = 'ia64';

    /**
     * @see TraceAttributes::HOST_ARCH 32-bit PowerPC
     */
    public const HOST_ARCH_PPC32 = 'ppc32';

    /**
     * @see TraceAttributes::HOST_ARCH 64-bit PowerPC
     */
    public const HOST_ARCH_PPC64 = 'ppc64';

    /**
     * @see TraceAttributes::HOST_ARCH IBM z/Architecture
     */
    public const HOST_ARCH_S390X = 's390x';

    /**
     * @see TraceAttributes::HOST_ARCH 32-bit x86
     */
    public const HOST_ARCH_X86 = 'x86';

    /**
     * @see TraceAttributes::HTTP_CONNECTION_STATE active state
     */
    public const HTTP_CONNECTION_STATE_ACTIVE = 'active';

    /**
     * @see TraceAttributes::HTTP_CONNECTION_STATE idle state
     */
    public const HTTP_CONNECTION_STATE_IDLE = 'idle';

    /**
     * @see TraceAttributes::HTTP_FLAVOR HTTP/1.0
     */
    public const HTTP_FLAVOR_HTTP_1_0 = '1.0';

    /**
     * @see TraceAttributes::HTTP_FLAVOR HTTP/1.1
     */
    public const HTTP_FLAVOR_HTTP_1_1 = '1.1';

    /**
     * @see TraceAttributes::HTTP_FLAVOR HTTP/2
     */
    public const HTTP_FLAVOR_HTTP_2_0 = '2.0';

    /**
     * @see TraceAttributes::HTTP_FLAVOR HTTP/3
     */
    public const HTTP_FLAVOR_HTTP_3_0 = '3.0';

    /**
     * @see TraceAttributes::HTTP_FLAVOR SPDY protocol
     */
    public const HTTP_FLAVOR_SPDY = 'SPDY';

    /**
     * @see TraceAttributes::HTTP_FLAVOR QUIC protocol
     */
    public const HTTP_FLAVOR_QUIC = 'QUIC';

    /**
     * @see TraceAttributes::HTTP_REQUEST_METHOD CONNECT method
     */
    public const HTTP_REQUEST_METHOD_CONNECT = 'CONNECT';

    /**
     * @see TraceAttributes::HTTP_REQUEST_METHOD DELETE method
     */
    public const HTTP_REQUEST_METHOD_DELETE = 'DELETE';

    /**
     * @see TraceAttributes::HTTP_REQUEST_METHOD GET method
     */
    public const HTTP_REQUEST_METHOD_GET = 'GET';

    /**
     * @see TraceAttributes::HTTP_REQUEST_METHOD HEAD method
     */
    public const HTTP_REQUEST_METHOD_HEAD = 'HEAD';

    /**
     * @see TraceAttributes::HTTP_REQUEST_METHOD OPTIONS method
     */
    public const HTTP_REQUEST_METHOD_OPTIONS = 'OPTIONS';

    /**
     * @see TraceAttributes::HTTP_REQUEST_METHOD PATCH method
     */
    public const HTTP_REQUEST_METHOD_PATCH = 'PATCH';

    /**
     * @see TraceAttributes::HTTP_REQUEST_METHOD POST method
     */
    public const HTTP_REQUEST_METHOD_POST = 'POST';

    /**
     * @see TraceAttributes::HTTP_REQUEST_METHOD PUT method
     */
    public const HTTP_REQUEST_METHOD_PUT = 'PUT';

    /**
     * @see TraceAttributes::HTTP_REQUEST_METHOD TRACE method
     */
    public const HTTP_REQUEST_METHOD_TRACE = 'TRACE';

    /**
     * @see TraceAttributes::HTTP_REQUEST_METHOD Any HTTP method that the instrumentation has no prior knowledge of
     */
    public const HTTP_REQUEST_METHOD_OTHER = '_OTHER';

    /**
     * @see TraceAttributes::IOS_STATE The app has become `active`. Associated with UIKit notification `applicationDidBecomeActive`
     */
    public const IOS_STATE_ACTIVE = 'active';

    /**
     * @see TraceAttributes::IOS_STATE The app is now `inactive`. Associated with UIKit notification `applicationWillResignActive`
     */
    public const IOS_STATE_INACTIVE = 'inactive';

    /**
     * @see TraceAttributes::IOS_STATE The app is now in the background. This value is associated with UIKit notification `applicationDidEnterBackground`
     */
    public const IOS_STATE_BACKGROUND = 'background';

    /**
     * @see TraceAttributes::IOS_STATE The app is now in the foreground. This value is associated with UIKit notification `applicationWillEnterForeground`
     */
    public const IOS_STATE_FOREGROUND = 'foreground';

    /**
     * @see TraceAttributes::IOS_STATE The app is about to terminate. Associated with UIKit notification `applicationWillTerminate`
     */
    public const IOS_STATE_TERMINATE = 'terminate';

    /**
     * @see TraceAttributes::JVM_MEMORY_TYPE Heap memory
     */
    public const JVM_MEMORY_TYPE_HEAP = 'heap';

    /**
     * @see TraceAttributes::JVM_MEMORY_TYPE Non-heap memory
     */
    public const JVM_MEMORY_TYPE_NON_HEAP = 'non_heap';

    /**
     * @see TraceAttributes::LOG_IOSTREAM Logs from stdout stream
     */
    public const LOG_IOSTREAM_STDOUT = 'stdout';

    /**
     * @see TraceAttributes::LOG_IOSTREAM Events from stderr stream
     */
    public const LOG_IOSTREAM_STDERR = 'stderr';

    /**
     * @see TraceAttributes::MESSAGE_TYPE sent
     */
    public const MESSAGE_TYPE_SENT = 'SENT';

    /**
     * @see TraceAttributes::MESSAGE_TYPE received
     */
    public const MESSAGE_TYPE_RECEIVED = 'RECEIVED';

    /**
     * @see TraceAttributes::MESSAGING_OPERATION One or more messages are provided for publishing to an intermediary. If a single message is published, the context of the &#34;Publish&#34; span can be used as the creation context and no &#34;Create&#34; span needs to be created
     */
    public const MESSAGING_OPERATION_PUBLISH = 'publish';

    /**
     * @see TraceAttributes::MESSAGING_OPERATION A message is created. &#34;Create&#34; spans always refer to a single message and are used to provide a unique creation context for messages in batch publishing scenarios
     */
    public const MESSAGING_OPERATION_CREATE = 'create';

    /**
     * @see TraceAttributes::MESSAGING_OPERATION One or more messages are requested by a consumer. This operation refers to pull-based scenarios, where consumers explicitly call methods of messaging SDKs to receive messages
     */
    public const MESSAGING_OPERATION_RECEIVE = 'receive';

    /**
     * @see TraceAttributes::MESSAGING_OPERATION One or more messages are delivered to or processed by a consumer
     */
    public const MESSAGING_OPERATION_DELIVER = 'process';

    /**
     * @see TraceAttributes::MESSAGING_OPERATION One or more messages are settled
     */
    public const MESSAGING_OPERATION_SETTLE = 'settle';

    /**
     * @see TraceAttributes::MESSAGING_ROCKETMQ_CONSUMPTION_MODEL Clustering consumption model
     */
    public const MESSAGING_ROCKETMQ_CONSUMPTION_MODEL_CLUSTERING = 'clustering';

    /**
     * @see TraceAttributes::MESSAGING_ROCKETMQ_CONSUMPTION_MODEL Broadcasting consumption model
     */
    public const MESSAGING_ROCKETMQ_CONSUMPTION_MODEL_BROADCASTING = 'broadcasting';

    /**
     * @see TraceAttributes::MESSAGING_ROCKETMQ_MESSAGE_TYPE Normal message
     */
    public const MESSAGING_ROCKETMQ_MESSAGE_TYPE_NORMAL = 'normal';

    /**
     * @see TraceAttributes::MESSAGING_ROCKETMQ_MESSAGE_TYPE FIFO message
     */
    public const MESSAGING_ROCKETMQ_MESSAGE_TYPE_FIFO = 'fifo';

    /**
     * @see TraceAttributes::MESSAGING_ROCKETMQ_MESSAGE_TYPE Delay message
     */
    public const MESSAGING_ROCKETMQ_MESSAGE_TYPE_DELAY = 'delay';

    /**
     * @see TraceAttributes::MESSAGING_ROCKETMQ_MESSAGE_TYPE Transaction message
     */
    public const MESSAGING_ROCKETMQ_MESSAGE_TYPE_TRANSACTION = 'transaction';

    /**
     * @see TraceAttributes::MESSAGING_SERVICEBUS_DISPOSITION_STATUS Message is completed
     */
    public const MESSAGING_SERVICEBUS_DISPOSITION_STATUS_COMPLETE = 'complete';

    /**
     * @see TraceAttributes::MESSAGING_SERVICEBUS_DISPOSITION_STATUS Message is abandoned
     */
    public const MESSAGING_SERVICEBUS_DISPOSITION_STATUS_ABANDON = 'abandon';

    /**
     * @see TraceAttributes::MESSAGING_SERVICEBUS_DISPOSITION_STATUS Message is sent to dead letter queue
     */
    public const MESSAGING_SERVICEBUS_DISPOSITION_STATUS_DEAD_LETTER = 'dead_letter';

    /**
     * @see TraceAttributes::MESSAGING_SERVICEBUS_DISPOSITION_STATUS Message is deferred
     */
    public const MESSAGING_SERVICEBUS_DISPOSITION_STATUS_DEFER = 'defer';

    /**
     * @see TraceAttributes::MESSAGING_SYSTEM Apache ActiveMQ
     */
    public const MESSAGING_SYSTEM_ACTIVEMQ = 'activemq';

    /**
     * @see TraceAttributes::MESSAGING_SYSTEM Amazon Simple Queue Service (SQS)
     */
    public const MESSAGING_SYSTEM_AWS_SQS = 'aws_sqs';

    /**
     * @see TraceAttributes::MESSAGING_SYSTEM Azure Event Grid
     */
    public const MESSAGING_SYSTEM_EVENTGRID = 'eventgrid';

    /**
     * @see TraceAttributes::MESSAGING_SYSTEM Azure Event Hubs
     */
    public const MESSAGING_SYSTEM_EVENTHUBS = 'eventhubs';

    /**
     * @see TraceAttributes::MESSAGING_SYSTEM Azure Service Bus
     */
    public const MESSAGING_SYSTEM_SERVICEBUS = 'servicebus';

    /**
     * @see TraceAttributes::MESSAGING_SYSTEM Google Cloud Pub/Sub
     */
    public const MESSAGING_SYSTEM_GCP_PUBSUB = 'gcp_pubsub';

    /**
     * @see TraceAttributes::MESSAGING_SYSTEM Java Message Service
     */
    public const MESSAGING_SYSTEM_JMS = 'jms';

    /**
     * @see TraceAttributes::MESSAGING_SYSTEM Apache Kafka
     */
    public const MESSAGING_SYSTEM_KAFKA = 'kafka';

    /**
     * @see TraceAttributes::MESSAGING_SYSTEM RabbitMQ
     */
    public const MESSAGING_SYSTEM_RABBITMQ = 'rabbitmq';

    /**
     * @see TraceAttributes::MESSAGING_SYSTEM Apache RocketMQ
     */
    public const MESSAGING_SYSTEM_ROCKETMQ = 'rocketmq';

    /**
     * @see TraceAttributes::NET_SOCK_FAMILY IPv4 address
     */
    public const NET_SOCK_FAMILY_INET = 'inet';

    /**
     * @see TraceAttributes::NET_SOCK_FAMILY IPv6 address
     */
    public const NET_SOCK_FAMILY_INET6 = 'inet6';

    /**
     * @see TraceAttributes::NET_SOCK_FAMILY Unix domain socket path
     */
    public const NET_SOCK_FAMILY_UNIX = 'unix';

    /**
     * @see TraceAttributes::NET_TRANSPORT ip_tcp
     */
    public const NET_TRANSPORT_IP_TCP = 'ip_tcp';

    /**
     * @see TraceAttributes::NET_TRANSPORT ip_udp
     */
    public const NET_TRANSPORT_IP_UDP = 'ip_udp';

    /**
     * @see TraceAttributes::NET_TRANSPORT Named or anonymous pipe
     */
    public const NET_TRANSPORT_PIPE = 'pipe';

    /**
     * @see TraceAttributes::NET_TRANSPORT In-process communication
     *
     * Signals that there is only in-process communication not using a &quot;real&quot; network protocol in cases where network attributes would normally be expected. Usually all other network attributes can be left out in that case.
     */
    public const NET_TRANSPORT_INPROC = 'inproc';

    /**
     * @see TraceAttributes::NET_TRANSPORT Something else (non IP-based)
     */
    public const NET_TRANSPORT_OTHER = 'other';

    /**
     * @see TraceAttributes::NETWORK_CONNECTION_SUBTYPE GPRS
     */
    public const NETWORK_CONNECTION_SUBTYPE_GPRS = 'gprs';

    /**
     * @see TraceAttributes::NETWORK_CONNECTION_SUBTYPE EDGE
     */
    public const NETWORK_CONNECTION_SUBTYPE_EDGE = 'edge';

    /**
     * @see TraceAttributes::NETWORK_CONNECTION_SUBTYPE UMTS
     */
    public const NETWORK_CONNECTION_SUBTYPE_UMTS = 'umts';

    /**
     * @see TraceAttributes::NETWORK_CONNECTION_SUBTYPE CDMA
     */
    public const NETWORK_CONNECTION_SUBTYPE_CDMA = 'cdma';

    /**
     * @see TraceAttributes::NETWORK_CONNECTION_SUBTYPE EVDO Rel. 0
     */
    public const NETWORK_CONNECTION_SUBTYPE_EVDO_0 = 'evdo_0';

    /**
     * @see TraceAttributes::NETWORK_CONNECTION_SUBTYPE EVDO Rev. A
     */
    public const NETWORK_CONNECTION_SUBTYPE_EVDO_A = 'evdo_a';

    /**
     * @see TraceAttributes::NETWORK_CONNECTION_SUBTYPE CDMA2000 1XRTT
     */
    public const NETWORK_CONNECTION_SUBTYPE_CDMA2000_1XRTT = 'cdma2000_1xrtt';

    /**
     * @see TraceAttributes::NETWORK_CONNECTION_SUBTYPE HSDPA
     */
    public const NETWORK_CONNECTION_SUBTYPE_HSDPA = 'hsdpa';

    /**
     * @see TraceAttributes::NETWORK_CONNECTION_SUBTYPE HSUPA
     */
    public const NETWORK_CONNECTION_SUBTYPE_HSUPA = 'hsupa';

    /**
     * @see TraceAttributes::NETWORK_CONNECTION_SUBTYPE HSPA
     */
    public const NETWORK_CONNECTION_SUBTYPE_HSPA = 'hspa';

    /**
     * @see TraceAttributes::NETWORK_CONNECTION_SUBTYPE IDEN
     */
    public const NETWORK_CONNECTION_SUBTYPE_IDEN = 'iden';

    /**
     * @see TraceAttributes::NETWORK_CONNECTION_SUBTYPE EVDO Rev. B
     */
    public const NETWORK_CONNECTION_SUBTYPE_EVDO_B = 'evdo_b';

    /**
     * @see TraceAttributes::NETWORK_CONNECTION_SUBTYPE LTE
     */
    public const NETWORK_CONNECTION_SUBTYPE_LTE = 'lte';

    /**
     * @see TraceAttributes::NETWORK_CONNECTION_SUBTYPE EHRPD
     */
    public const NETWORK_CONNECTION_SUBTYPE_EHRPD = 'ehrpd';

    /**
     * @see TraceAttributes::NETWORK_CONNECTION_SUBTYPE HSPAP
     */
    public const NETWORK_CONNECTION_SUBTYPE_HSPAP = 'hspap';

    /**
     * @see TraceAttributes::NETWORK_CONNECTION_SUBTYPE GSM
     */
    public const NETWORK_CONNECTION_SUBTYPE_GSM = 'gsm';

    /**
     * @see TraceAttributes::NETWORK_CONNECTION_SUBTYPE TD-SCDMA
     */
    public const NETWORK_CONNECTION_SUBTYPE_TD_SCDMA = 'td_scdma';

    /**
     * @see TraceAttributes::NETWORK_CONNECTION_SUBTYPE IWLAN
     */
    public const NETWORK_CONNECTION_SUBTYPE_IWLAN = 'iwlan';

    /**
     * @see TraceAttributes::NETWORK_CONNECTION_SUBTYPE 5G NR (New Radio)
     */
    public const NETWORK_CONNECTION_SUBTYPE_NR = 'nr';

    /**
     * @see TraceAttributes::NETWORK_CONNECTION_SUBTYPE 5G NRNSA (New Radio Non-Standalone)
     */
    public const NETWORK_CONNECTION_SUBTYPE_NRNSA = 'nrnsa';

    /**
     * @see TraceAttributes::NETWORK_CONNECTION_SUBTYPE LTE CA
     */
    public const NETWORK_CONNECTION_SUBTYPE_LTE_CA = 'lte_ca';

    /**
     * @see TraceAttributes::NETWORK_CONNECTION_TYPE wifi
     */
    public const NETWORK_CONNECTION_TYPE_WIFI = 'wifi';

    /**
     * @see TraceAttributes::NETWORK_CONNECTION_TYPE wired
     */
    public const NETWORK_CONNECTION_TYPE_WIRED = 'wired';

    /**
     * @see TraceAttributes::NETWORK_CONNECTION_TYPE cell
     */
    public const NETWORK_CONNECTION_TYPE_CELL = 'cell';

    /**
     * @see TraceAttributes::NETWORK_CONNECTION_TYPE unavailable
     */
    public const NETWORK_CONNECTION_TYPE_UNAVAILABLE = 'unavailable';

    /**
     * @see TraceAttributes::NETWORK_CONNECTION_TYPE unknown
     */
    public const NETWORK_CONNECTION_TYPE_UNKNOWN = 'unknown';

    /**
     * @see TraceAttributes::NETWORK_IO_DIRECTION transmit
     */
    public const NETWORK_IO_DIRECTION_TRANSMIT = 'transmit';

    /**
     * @see TraceAttributes::NETWORK_IO_DIRECTION receive
     */
    public const NETWORK_IO_DIRECTION_RECEIVE = 'receive';

    /**
     * @see TraceAttributes::NETWORK_TRANSPORT TCP
     */
    public const NETWORK_TRANSPORT_TCP = 'tcp';

    /**
     * @see TraceAttributes::NETWORK_TRANSPORT UDP
     */
    public const NETWORK_TRANSPORT_UDP = 'udp';

    /**
     * @see TraceAttributes::NETWORK_TRANSPORT Named or anonymous pipe
     */
    public const NETWORK_TRANSPORT_PIPE = 'pipe';

    /**
     * @see TraceAttributes::NETWORK_TRANSPORT Unix domain socket
     */
    public const NETWORK_TRANSPORT_UNIX = 'unix';

    /**
     * @see TraceAttributes::NETWORK_TYPE IPv4
     */
    public const NETWORK_TYPE_IPV4 = 'ipv4';

    /**
     * @see TraceAttributes::NETWORK_TYPE IPv6
     */
    public const NETWORK_TYPE_IPV6 = 'ipv6';

    /**
     * @see TraceAttributes::OPENTRACING_REF_TYPE The parent Span depends on the child Span in some capacity
     */
    public const OPENTRACING_REF_TYPE_CHILD_OF = 'child_of';

    /**
     * @see TraceAttributes::OPENTRACING_REF_TYPE The parent Span doesn&#39;t depend in any way on the result of the child Span
     */
    public const OPENTRACING_REF_TYPE_FOLLOWS_FROM = 'follows_from';

    /**
     * @see TraceAttributes::OS_TYPE Microsoft Windows
     */
    public const OS_TYPE_WINDOWS = 'windows';

    /**
     * @see TraceAttributes::OS_TYPE Linux
     */
    public const OS_TYPE_LINUX = 'linux';

    /**
     * @see TraceAttributes::OS_TYPE Apple Darwin
     */
    public const OS_TYPE_DARWIN = 'darwin';

    /**
     * @see TraceAttributes::OS_TYPE FreeBSD
     */
    public const OS_TYPE_FREEBSD = 'freebsd';

    /**
     * @see TraceAttributes::OS_TYPE NetBSD
     */
    public const OS_TYPE_NETBSD = 'netbsd';

    /**
     * @see TraceAttributes::OS_TYPE OpenBSD
     */
    public const OS_TYPE_OPENBSD = 'openbsd';

    /**
     * @see TraceAttributes::OS_TYPE DragonFly BSD
     */
    public const OS_TYPE_DRAGONFLYBSD = 'dragonflybsd';

    /**
     * @see TraceAttributes::OS_TYPE HP-UX (Hewlett Packard Unix)
     */
    public const OS_TYPE_HPUX = 'hpux';

    /**
     * @see TraceAttributes::OS_TYPE AIX (Advanced Interactive eXecutive)
     */
    public const OS_TYPE_AIX = 'aix';

    /**
     * @see TraceAttributes::OS_TYPE SunOS, Oracle Solaris
     */
    public const OS_TYPE_SOLARIS = 'solaris';

    /**
     * @see TraceAttributes::OS_TYPE IBM z/OS
     */
    public const OS_TYPE_Z_OS = 'z_os';

    /**
     * @see TraceAttributes::OTEL_STATUS_CODE The operation has been validated by an Application developer or Operator to have completed successfully
     */
    public const OTEL_STATUS_CODE_OK = 'OK';

    /**
     * @see TraceAttributes::OTEL_STATUS_CODE The operation contains an error
     */
    public const OTEL_STATUS_CODE_ERROR = 'ERROR';

    /**
     * @see TraceAttributes::PROCESS_CPU_STATE system
     */
    public const PROCESS_CPU_STATE_SYSTEM = 'system';

    /**
     * @see TraceAttributes::PROCESS_CPU_STATE user
     */
    public const PROCESS_CPU_STATE_USER = 'user';

    /**
     * @see TraceAttributes::PROCESS_CPU_STATE wait
     */
    public const PROCESS_CPU_STATE_WAIT = 'wait';

    /**
     * @see TraceAttributes::RPC_CONNECT_RPC_ERROR_CODE cancelled
     */
    public const RPC_CONNECT_RPC_ERROR_CODE_CANCELLED = 'cancelled';

    /**
     * @see TraceAttributes::RPC_CONNECT_RPC_ERROR_CODE unknown
     */
    public const RPC_CONNECT_RPC_ERROR_CODE_UNKNOWN = 'unknown';

    /**
     * @see TraceAttributes::RPC_CONNECT_RPC_ERROR_CODE invalid_argument
     */
    public const RPC_CONNECT_RPC_ERROR_CODE_INVALID_ARGUMENT = 'invalid_argument';

    /**
     * @see TraceAttributes::RPC_CONNECT_RPC_ERROR_CODE deadline_exceeded
     */
    public const RPC_CONNECT_RPC_ERROR_CODE_DEADLINE_EXCEEDED = 'deadline_exceeded';

    /**
     * @see TraceAttributes::RPC_CONNECT_RPC_ERROR_CODE not_found
     */
    public const RPC_CONNECT_RPC_ERROR_CODE_NOT_FOUND = 'not_found';

    /**
     * @see TraceAttributes::RPC_CONNECT_RPC_ERROR_CODE already_exists
     */
    public const RPC_CONNECT_RPC_ERROR_CODE_ALREADY_EXISTS = 'already_exists';

    /**
     * @see TraceAttributes::RPC_CONNECT_RPC_ERROR_CODE permission_denied
     */
    public const RPC_CONNECT_RPC_ERROR_CODE_PERMISSION_DENIED = 'permission_denied';

    /**
     * @see TraceAttributes::RPC_CONNECT_RPC_ERROR_CODE resource_exhausted
     */
    public const RPC_CONNECT_RPC_ERROR_CODE_RESOURCE_EXHAUSTED = 'resource_exhausted';

    /**
     * @see TraceAttributes::RPC_CONNECT_RPC_ERROR_CODE failed_precondition
     */
    public const RPC_CONNECT_RPC_ERROR_CODE_FAILED_PRECONDITION = 'failed_precondition';

    /**
     * @see TraceAttributes::RPC_CONNECT_RPC_ERROR_CODE aborted
     */
    public const RPC_CONNECT_RPC_ERROR_CODE_ABORTED = 'aborted';

    /**
     * @see TraceAttributes::RPC_CONNECT_RPC_ERROR_CODE out_of_range
     */
    public const RPC_CONNECT_RPC_ERROR_CODE_OUT_OF_RANGE = 'out_of_range';

    /**
     * @see TraceAttributes::RPC_CONNECT_RPC_ERROR_CODE unimplemented
     */
    public const RPC_CONNECT_RPC_ERROR_CODE_UNIMPLEMENTED = 'unimplemented';

    /**
     * @see TraceAttributes::RPC_CONNECT_RPC_ERROR_CODE internal
     */
    public const RPC_CONNECT_RPC_ERROR_CODE_INTERNAL = 'internal';

    /**
     * @see TraceAttributes::RPC_CONNECT_RPC_ERROR_CODE unavailable
     */
    public const RPC_CONNECT_RPC_ERROR_CODE_UNAVAILABLE = 'unavailable';

    /**
     * @see TraceAttributes::RPC_CONNECT_RPC_ERROR_CODE data_loss
     */
    public const RPC_CONNECT_RPC_ERROR_CODE_DATA_LOSS = 'data_loss';

    /**
     * @see TraceAttributes::RPC_CONNECT_RPC_ERROR_CODE unauthenticated
     */
    public const RPC_CONNECT_RPC_ERROR_CODE_UNAUTHENTICATED = 'unauthenticated';

    /**
     * @see TraceAttributes::RPC_GRPC_STATUS_CODE OK
     */
    public const RPC_GRPC_STATUS_CODE_OK = '0';

    /**
     * @see TraceAttributes::RPC_GRPC_STATUS_CODE CANCELLED
     */
    public const RPC_GRPC_STATUS_CODE_CANCELLED = '1';

    /**
     * @see TraceAttributes::RPC_GRPC_STATUS_CODE UNKNOWN
     */
    public const RPC_GRPC_STATUS_CODE_UNKNOWN = '2';

    /**
     * @see TraceAttributes::RPC_GRPC_STATUS_CODE INVALID_ARGUMENT
     */
    public const RPC_GRPC_STATUS_CODE_INVALID_ARGUMENT = '3';

    /**
     * @see TraceAttributes::RPC_GRPC_STATUS_CODE DEADLINE_EXCEEDED
     */
    public const RPC_GRPC_STATUS_CODE_DEADLINE_EXCEEDED = '4';

    /**
     * @see TraceAttributes::RPC_GRPC_STATUS_CODE NOT_FOUND
     */
    public const RPC_GRPC_STATUS_CODE_NOT_FOUND = '5';

    /**
     * @see TraceAttributes::RPC_GRPC_STATUS_CODE ALREADY_EXISTS
     */
    public const RPC_GRPC_STATUS_CODE_ALREADY_EXISTS = '6';

    /**
     * @see TraceAttributes::RPC_GRPC_STATUS_CODE PERMISSION_DENIED
     */
    public const RPC_GRPC_STATUS_CODE_PERMISSION_DENIED = '7';

    /**
     * @see TraceAttributes::RPC_GRPC_STATUS_CODE RESOURCE_EXHAUSTED
     */
    public const RPC_GRPC_STATUS_CODE_RESOURCE_EXHAUSTED = '8';

    /**
     * @see TraceAttributes::RPC_GRPC_STATUS_CODE FAILED_PRECONDITION
     */
    public const RPC_GRPC_STATUS_CODE_FAILED_PRECONDITION = '9';

    /**
     * @see TraceAttributes::RPC_GRPC_STATUS_CODE ABORTED
     */
    public const RPC_GRPC_STATUS_CODE_ABORTED = '10';

    /**
     * @see TraceAttributes::RPC_GRPC_STATUS_CODE OUT_OF_RANGE
     */
    public const RPC_GRPC_STATUS_CODE_OUT_OF_RANGE = '11';

    /**
     * @see TraceAttributes::RPC_GRPC_STATUS_CODE UNIMPLEMENTED
     */
    public const RPC_GRPC_STATUS_CODE_UNIMPLEMENTED = '12';

    /**
     * @see TraceAttributes::RPC_GRPC_STATUS_CODE INTERNAL
     */
    public const RPC_GRPC_STATUS_CODE_INTERNAL = '13';

    /**
     * @see TraceAttributes::RPC_GRPC_STATUS_CODE UNAVAILABLE
     */
    public const RPC_GRPC_STATUS_CODE_UNAVAILABLE = '14';

    /**
     * @see TraceAttributes::RPC_GRPC_STATUS_CODE DATA_LOSS
     */
    public const RPC_GRPC_STATUS_CODE_DATA_LOSS = '15';

    /**
     * @see TraceAttributes::RPC_GRPC_STATUS_CODE UNAUTHENTICATED
     */
    public const RPC_GRPC_STATUS_CODE_UNAUTHENTICATED = '16';

    /**
     * @see TraceAttributes::RPC_SYSTEM gRPC
     */
    public const RPC_SYSTEM_GRPC = 'grpc';

    /**
     * @see TraceAttributes::RPC_SYSTEM Java RMI
     */
    public const RPC_SYSTEM_JAVA_RMI = 'java_rmi';

    /**
     * @see TraceAttributes::RPC_SYSTEM .NET WCF
     */
    public const RPC_SYSTEM_DOTNET_WCF = 'dotnet_wcf';

    /**
     * @see TraceAttributes::RPC_SYSTEM Apache Dubbo
     */
    public const RPC_SYSTEM_APACHE_DUBBO = 'apache_dubbo';

    /**
     * @see TraceAttributes::RPC_SYSTEM Connect RPC
     */
    public const RPC_SYSTEM_CONNECT_RPC = 'connect_rpc';

    /**
     * @see TraceAttributes::SIGNALR_CONNECTION_STATUS The connection was closed normally
     */
    public const SIGNALR_CONNECTION_STATUS_NORMAL_CLOSURE = 'normal_closure';

    /**
     * @see TraceAttributes::SIGNALR_CONNECTION_STATUS The connection was closed due to a timeout
     */
    public const SIGNALR_CONNECTION_STATUS_TIMEOUT = 'timeout';

    /**
     * @see TraceAttributes::SIGNALR_CONNECTION_STATUS The connection was closed because the app is shutting down
     */
    public const SIGNALR_CONNECTION_STATUS_APP_SHUTDOWN = 'app_shutdown';

    /**
     * @see TraceAttributes::SIGNALR_TRANSPORT ServerSentEvents protocol
     */
    public const SIGNALR_TRANSPORT_SERVER_SENT_EVENTS = 'server_sent_events';

    /**
     * @see TraceAttributes::SIGNALR_TRANSPORT LongPolling protocol
     */
    public const SIGNALR_TRANSPORT_LONG_POLLING = 'long_polling';

    /**
     * @see TraceAttributes::SIGNALR_TRANSPORT WebSockets protocol
     */
    public const SIGNALR_TRANSPORT_WEB_SOCKETS = 'web_sockets';

    /**
     * @see TraceAttributes::STATE idle
     */
    public const STATE_IDLE = 'idle';

    /**
     * @see TraceAttributes::STATE used
     */
    public const STATE_USED = 'used';

    /**
     * @see TraceAttributes::SYSTEM_CPU_STATE user
     */
    public const SYSTEM_CPU_STATE_USER = 'user';

    /**
     * @see TraceAttributes::SYSTEM_CPU_STATE system
     */
    public const SYSTEM_CPU_STATE_SYSTEM = 'system';

    /**
     * @see TraceAttributes::SYSTEM_CPU_STATE nice
     */
    public const SYSTEM_CPU_STATE_NICE = 'nice';

    /**
     * @see TraceAttributes::SYSTEM_CPU_STATE idle
     */
    public const SYSTEM_CPU_STATE_IDLE = 'idle';

    /**
     * @see TraceAttributes::SYSTEM_CPU_STATE iowait
     */
    public const SYSTEM_CPU_STATE_IOWAIT = 'iowait';

    /**
     * @see TraceAttributes::SYSTEM_CPU_STATE interrupt
     */
    public const SYSTEM_CPU_STATE_INTERRUPT = 'interrupt';

    /**
     * @see TraceAttributes::SYSTEM_CPU_STATE steal
     */
    public const SYSTEM_CPU_STATE_STEAL = 'steal';

    /**
     * @see TraceAttributes::SYSTEM_FILESYSTEM_STATE used
     */
    public const SYSTEM_FILESYSTEM_STATE_USED = 'used';

    /**
     * @see TraceAttributes::SYSTEM_FILESYSTEM_STATE free
     */
    public const SYSTEM_FILESYSTEM_STATE_FREE = 'free';

    /**
     * @see TraceAttributes::SYSTEM_FILESYSTEM_STATE reserved
     */
    public const SYSTEM_FILESYSTEM_STATE_RESERVED = 'reserved';

    /**
     * @see TraceAttributes::SYSTEM_FILESYSTEM_TYPE fat32
     */
    public const SYSTEM_FILESYSTEM_TYPE_FAT32 = 'fat32';

    /**
     * @see TraceAttributes::SYSTEM_FILESYSTEM_TYPE exfat
     */
    public const SYSTEM_FILESYSTEM_TYPE_EXFAT = 'exfat';

    /**
     * @see TraceAttributes::SYSTEM_FILESYSTEM_TYPE ntfs
     */
    public const SYSTEM_FILESYSTEM_TYPE_NTFS = 'ntfs';

    /**
     * @see TraceAttributes::SYSTEM_FILESYSTEM_TYPE refs
     */
    public const SYSTEM_FILESYSTEM_TYPE_REFS = 'refs';

    /**
     * @see TraceAttributes::SYSTEM_FILESYSTEM_TYPE hfsplus
     */
    public const SYSTEM_FILESYSTEM_TYPE_HFSPLUS = 'hfsplus';

    /**
     * @see TraceAttributes::SYSTEM_FILESYSTEM_TYPE ext4
     */
    public const SYSTEM_FILESYSTEM_TYPE_EXT4 = 'ext4';

    /**
     * @see TraceAttributes::SYSTEM_MEMORY_STATE used
     */
    public const SYSTEM_MEMORY_STATE_USED = 'used';

    /**
     * @see TraceAttributes::SYSTEM_MEMORY_STATE free
     */
    public const SYSTEM_MEMORY_STATE_FREE = 'free';

    /**
     * @see TraceAttributes::SYSTEM_MEMORY_STATE shared
     */
    public const SYSTEM_MEMORY_STATE_SHARED = 'shared';

    /**
     * @see TraceAttributes::SYSTEM_MEMORY_STATE buffers
     */
    public const SYSTEM_MEMORY_STATE_BUFFERS = 'buffers';

    /**
     * @see TraceAttributes::SYSTEM_MEMORY_STATE cached
     */
    public const SYSTEM_MEMORY_STATE_CACHED = 'cached';

    /**
     * @see TraceAttributes::SYSTEM_NETWORK_STATE close
     */
    public const SYSTEM_NETWORK_STATE_CLOSE = 'close';

    /**
     * @see TraceAttributes::SYSTEM_NETWORK_STATE close_wait
     */
    public const SYSTEM_NETWORK_STATE_CLOSE_WAIT = 'close_wait';

    /**
     * @see TraceAttributes::SYSTEM_NETWORK_STATE closing
     */
    public const SYSTEM_NETWORK_STATE_CLOSING = 'closing';

    /**
     * @see TraceAttributes::SYSTEM_NETWORK_STATE delete
     */
    public const SYSTEM_NETWORK_STATE_DELETE = 'delete';

    /**
     * @see TraceAttributes::SYSTEM_NETWORK_STATE established
     */
    public const SYSTEM_NETWORK_STATE_ESTABLISHED = 'established';

    /**
     * @see TraceAttributes::SYSTEM_NETWORK_STATE fin_wait_1
     */
    public const SYSTEM_NETWORK_STATE_FIN_WAIT_1 = 'fin_wait_1';

    /**
     * @see TraceAttributes::SYSTEM_NETWORK_STATE fin_wait_2
     */
    public const SYSTEM_NETWORK_STATE_FIN_WAIT_2 = 'fin_wait_2';

    /**
     * @see TraceAttributes::SYSTEM_NETWORK_STATE last_ack
     */
    public const SYSTEM_NETWORK_STATE_LAST_ACK = 'last_ack';

    /**
     * @see TraceAttributes::SYSTEM_NETWORK_STATE listen
     */
    public const SYSTEM_NETWORK_STATE_LISTEN = 'listen';

    /**
     * @see TraceAttributes::SYSTEM_NETWORK_STATE syn_recv
     */
    public const SYSTEM_NETWORK_STATE_SYN_RECV = 'syn_recv';

    /**
     * @see TraceAttributes::SYSTEM_NETWORK_STATE syn_sent
     */
    public const SYSTEM_NETWORK_STATE_SYN_SENT = 'syn_sent';

    /**
     * @see TraceAttributes::SYSTEM_NETWORK_STATE time_wait
     */
    public const SYSTEM_NETWORK_STATE_TIME_WAIT = 'time_wait';

    /**
     * @see TraceAttributes::SYSTEM_PAGING_DIRECTION in
     */
    public const SYSTEM_PAGING_DIRECTION_IN = 'in';

    /**
     * @see TraceAttributes::SYSTEM_PAGING_DIRECTION out
     */
    public const SYSTEM_PAGING_DIRECTION_OUT = 'out';

    /**
     * @see TraceAttributes::SYSTEM_PAGING_STATE used
     */
    public const SYSTEM_PAGING_STATE_USED = 'used';

    /**
     * @see TraceAttributes::SYSTEM_PAGING_STATE free
     */
    public const SYSTEM_PAGING_STATE_FREE = 'free';

    /**
     * @see TraceAttributes::SYSTEM_PAGING_TYPE major
     */
    public const SYSTEM_PAGING_TYPE_MAJOR = 'major';

    /**
     * @see TraceAttributes::SYSTEM_PAGING_TYPE minor
     */
    public const SYSTEM_PAGING_TYPE_MINOR = 'minor';

    /**
     * @see TraceAttributes::SYSTEM_PROCESS_STATUS running
     */
    public const SYSTEM_PROCESS_STATUS_RUNNING = 'running';

    /**
     * @see TraceAttributes::SYSTEM_PROCESS_STATUS sleeping
     */
    public const SYSTEM_PROCESS_STATUS_SLEEPING = 'sleeping';

    /**
     * @see TraceAttributes::SYSTEM_PROCESS_STATUS stopped
     */
    public const SYSTEM_PROCESS_STATUS_STOPPED = 'stopped';

    /**
     * @see TraceAttributes::SYSTEM_PROCESS_STATUS defunct
     */
    public const SYSTEM_PROCESS_STATUS_DEFUNCT = 'defunct';

    /**
     * @see TraceAttributes::SYSTEM_PROCESSES_STATUS running
     */
    public const SYSTEM_PROCESSES_STATUS_RUNNING = 'running';

    /**
     * @see TraceAttributes::SYSTEM_PROCESSES_STATUS sleeping
     */
    public const SYSTEM_PROCESSES_STATUS_SLEEPING = 'sleeping';

    /**
     * @see TraceAttributes::SYSTEM_PROCESSES_STATUS stopped
     */
    public const SYSTEM_PROCESSES_STATUS_STOPPED = 'stopped';

    /**
     * @see TraceAttributes::SYSTEM_PROCESSES_STATUS defunct
     */
    public const SYSTEM_PROCESSES_STATUS_DEFUNCT = 'defunct';

    /**
     * @see TraceAttributes::TELEMETRY_SDK_LANGUAGE cpp
     */
    public const TELEMETRY_SDK_LANGUAGE_CPP = 'cpp';

    /**
     * @see TraceAttributes::TELEMETRY_SDK_LANGUAGE dotnet
     */
    public const TELEMETRY_SDK_LANGUAGE_DOTNET = 'dotnet';

    /**
     * @see TraceAttributes::TELEMETRY_SDK_LANGUAGE erlang
     */
    public const TELEMETRY_SDK_LANGUAGE_ERLANG = 'erlang';

    /**
     * @see TraceAttributes::TELEMETRY_SDK_LANGUAGE go
     */
    public const TELEMETRY_SDK_LANGUAGE_GO = 'go';

    /**
     * @see TraceAttributes::TELEMETRY_SDK_LANGUAGE java
     */
    public const TELEMETRY_SDK_LANGUAGE_JAVA = 'java';

    /**
     * @see TraceAttributes::TELEMETRY_SDK_LANGUAGE nodejs
     */
    public const TELEMETRY_SDK_LANGUAGE_NODEJS = 'nodejs';

    /**
     * @see TraceAttributes::TELEMETRY_SDK_LANGUAGE php
     */
    public const TELEMETRY_SDK_LANGUAGE_PHP = 'php';

    /**
     * @see TraceAttributes::TELEMETRY_SDK_LANGUAGE python
     */
    public const TELEMETRY_SDK_LANGUAGE_PYTHON = 'python';

    /**
     * @see TraceAttributes::TELEMETRY_SDK_LANGUAGE ruby
     */
    public const TELEMETRY_SDK_LANGUAGE_RUBY = 'ruby';

    /**
     * @see TraceAttributes::TELEMETRY_SDK_LANGUAGE rust
     */
    public const TELEMETRY_SDK_LANGUAGE_RUST = 'rust';

    /**
     * @see TraceAttributes::TELEMETRY_SDK_LANGUAGE swift
     */
    public const TELEMETRY_SDK_LANGUAGE_SWIFT = 'swift';

    /**
     * @see TraceAttributes::TELEMETRY_SDK_LANGUAGE webjs
     */
    public const TELEMETRY_SDK_LANGUAGE_WEBJS = 'webjs';

    /**
     * @see TraceAttributes::TLS_PROTOCOL_NAME ssl
     */
    public const TLS_PROTOCOL_NAME_SSL = 'ssl';

    /**
     * @see TraceAttributes::TLS_PROTOCOL_NAME tls
     */
    public const TLS_PROTOCOL_NAME_TLS = 'tls';
}
