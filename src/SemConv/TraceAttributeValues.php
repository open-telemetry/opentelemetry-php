<?php

// DO NOT EDIT, this is an Auto-generated file from script/semantic-conventions/

declare(strict_types=1);

namespace OpenTelemetry\SemConv;

interface TraceAttributeValues
{
    /**
     * The URL of the OpenTelemetry schema for these values.
     */
    public const SCHEMA_URL = 'https://opentelemetry.io/schemas/1.30.0';
    /**
     * Any time before Activity.onResume() or, if the app has no Activity, Context.startService() has been called in the app for the first time.
     *
     * @see TraceAttributes::ANDROID_STATE
     */
    public const ANDROID_STATE_CREATED = 'created';

    /**
     * Any time after Activity.onPause() or, if the app has no Activity, Context.stopService() has been called when the app was in the foreground state.
     *
     * @see TraceAttributes::ANDROID_STATE
     */
    public const ANDROID_STATE_BACKGROUND = 'background';

    /**
     * Any time after Activity.onResume() or, if the app has no Activity, Context.startService() has been called when the app was in either the created or background states.
     *
     * @see TraceAttributes::ANDROID_STATE
     */
    public const ANDROID_STATE_FOREGROUND = 'foreground';

    /**
     * Exception was handled by the exception handling middleware.
     *
     * @see TraceAttributes::ASPNETCORE_DIAGNOSTICS_EXCEPTION_RESULT
     */
    public const ASPNETCORE_DIAGNOSTICS_EXCEPTION_RESULT_HANDLED = 'handled';

    /**
     * Exception was not handled by the exception handling middleware.
     *
     * @see TraceAttributes::ASPNETCORE_DIAGNOSTICS_EXCEPTION_RESULT
     */
    public const ASPNETCORE_DIAGNOSTICS_EXCEPTION_RESULT_UNHANDLED = 'unhandled';

    /**
     * Exception handling was skipped because the response had started.
     *
     * @see TraceAttributes::ASPNETCORE_DIAGNOSTICS_EXCEPTION_RESULT
     */
    public const ASPNETCORE_DIAGNOSTICS_EXCEPTION_RESULT_SKIPPED = 'skipped';

    /**
     * Exception handling didn't run because the request was aborted.
     *
     * @see TraceAttributes::ASPNETCORE_DIAGNOSTICS_EXCEPTION_RESULT
     */
    public const ASPNETCORE_DIAGNOSTICS_EXCEPTION_RESULT_ABORTED = 'aborted';

    /**
     * Lease was acquired
     *
     * @see TraceAttributes::ASPNETCORE_RATE_LIMITING_RESULT
     */
    public const ASPNETCORE_RATE_LIMITING_RESULT_ACQUIRED = 'acquired';

    /**
     * Lease request was rejected by the endpoint limiter
     *
     * @see TraceAttributes::ASPNETCORE_RATE_LIMITING_RESULT
     */
    public const ASPNETCORE_RATE_LIMITING_RESULT_ENDPOINT_LIMITER = 'endpoint_limiter';

    /**
     * Lease request was rejected by the global limiter
     *
     * @see TraceAttributes::ASPNETCORE_RATE_LIMITING_RESULT
     */
    public const ASPNETCORE_RATE_LIMITING_RESULT_GLOBAL_LIMITER = 'global_limiter';

    /**
     * Lease request was canceled
     *
     * @see TraceAttributes::ASPNETCORE_RATE_LIMITING_RESULT
     */
    public const ASPNETCORE_RATE_LIMITING_RESULT_REQUEST_CANCELED = 'request_canceled';

    /**
     * Match succeeded
     *
     * @see TraceAttributes::ASPNETCORE_ROUTING_MATCH_STATUS
     */
    public const ASPNETCORE_ROUTING_MATCH_STATUS_SUCCESS = 'success';

    /**
     * Match failed
     *
     * @see TraceAttributes::ASPNETCORE_ROUTING_MATCH_STATUS
     */
    public const ASPNETCORE_ROUTING_MATCH_STATUS_FAILURE = 'failure';

    /**
     * ec2
     *
     * @see TraceAttributes::AWS_ECS_LAUNCHTYPE
     */
    public const AWS_ECS_LAUNCHTYPE_EC2 = 'ec2';

    /**
     * fargate
     *
     * @see TraceAttributes::AWS_ECS_LAUNCHTYPE
     */
    public const AWS_ECS_LAUNCHTYPE_FARGATE = 'fargate';

    /**
     * Gateway (HTTP) connection.
     *
     * @see TraceAttributes::AZURE_COSMOSDB_CONNECTION_MODE
     */
    public const AZURE_COSMOSDB_CONNECTION_MODE_GATEWAY = 'gateway';

    /**
     * Direct connection.
     *
     * @see TraceAttributes::AZURE_COSMOSDB_CONNECTION_MODE
     */
    public const AZURE_COSMOSDB_CONNECTION_MODE_DIRECT = 'direct';

    /**
     * strong
     *
     * @see TraceAttributes::AZURE_COSMOSDB_CONSISTENCY_LEVEL
     */
    public const AZURE_COSMOSDB_CONSISTENCY_LEVEL_STRONG = 'Strong';

    /**
     * bounded_staleness
     *
     * @see TraceAttributes::AZURE_COSMOSDB_CONSISTENCY_LEVEL
     */
    public const AZURE_COSMOSDB_CONSISTENCY_LEVEL_BOUNDED_STALENESS = 'BoundedStaleness';

    /**
     * session
     *
     * @see TraceAttributes::AZURE_COSMOSDB_CONSISTENCY_LEVEL
     */
    public const AZURE_COSMOSDB_CONSISTENCY_LEVEL_SESSION = 'Session';

    /**
     * eventual
     *
     * @see TraceAttributes::AZURE_COSMOSDB_CONSISTENCY_LEVEL
     */
    public const AZURE_COSMOSDB_CONSISTENCY_LEVEL_EVENTUAL = 'Eventual';

    /**
     * consistent_prefix
     *
     * @see TraceAttributes::AZURE_COSMOSDB_CONSISTENCY_LEVEL
     */
    public const AZURE_COSMOSDB_CONSISTENCY_LEVEL_CONSISTENT_PREFIX = 'ConsistentPrefix';

    /**
     * all
     *
     * @see TraceAttributes::CASSANDRA_CONSISTENCY_LEVEL
     */
    public const CASSANDRA_CONSISTENCY_LEVEL_ALL = 'all';

    /**
     * each_quorum
     *
     * @see TraceAttributes::CASSANDRA_CONSISTENCY_LEVEL
     */
    public const CASSANDRA_CONSISTENCY_LEVEL_EACH_QUORUM = 'each_quorum';

    /**
     * quorum
     *
     * @see TraceAttributes::CASSANDRA_CONSISTENCY_LEVEL
     */
    public const CASSANDRA_CONSISTENCY_LEVEL_QUORUM = 'quorum';

    /**
     * local_quorum
     *
     * @see TraceAttributes::CASSANDRA_CONSISTENCY_LEVEL
     */
    public const CASSANDRA_CONSISTENCY_LEVEL_LOCAL_QUORUM = 'local_quorum';

    /**
     * one
     *
     * @see TraceAttributes::CASSANDRA_CONSISTENCY_LEVEL
     */
    public const CASSANDRA_CONSISTENCY_LEVEL_ONE = 'one';

    /**
     * two
     *
     * @see TraceAttributes::CASSANDRA_CONSISTENCY_LEVEL
     */
    public const CASSANDRA_CONSISTENCY_LEVEL_TWO = 'two';

    /**
     * three
     *
     * @see TraceAttributes::CASSANDRA_CONSISTENCY_LEVEL
     */
    public const CASSANDRA_CONSISTENCY_LEVEL_THREE = 'three';

    /**
     * local_one
     *
     * @see TraceAttributes::CASSANDRA_CONSISTENCY_LEVEL
     */
    public const CASSANDRA_CONSISTENCY_LEVEL_LOCAL_ONE = 'local_one';

    /**
     * any
     *
     * @see TraceAttributes::CASSANDRA_CONSISTENCY_LEVEL
     */
    public const CASSANDRA_CONSISTENCY_LEVEL_ANY = 'any';

    /**
     * serial
     *
     * @see TraceAttributes::CASSANDRA_CONSISTENCY_LEVEL
     */
    public const CASSANDRA_CONSISTENCY_LEVEL_SERIAL = 'serial';

    /**
     * local_serial
     *
     * @see TraceAttributes::CASSANDRA_CONSISTENCY_LEVEL
     */
    public const CASSANDRA_CONSISTENCY_LEVEL_LOCAL_SERIAL = 'local_serial';

    /**
     * The pipeline run finished successfully.
     *
     * @see TraceAttributes::CICD_PIPELINE_RESULT
     */
    public const CICD_PIPELINE_RESULT_SUCCESS = 'success';

    /**
     * The pipeline run did not finish successfully, eg. due to a compile error or a failing test. Such failures are usually detected by non-zero exit codes of the tools executed in the pipeline run.
     *
     * @see TraceAttributes::CICD_PIPELINE_RESULT
     */
    public const CICD_PIPELINE_RESULT_FAILURE = 'failure';

    /**
     * The pipeline run failed due to an error in the CICD system, eg. due to the worker being killed.
     *
     * @see TraceAttributes::CICD_PIPELINE_RESULT
     */
    public const CICD_PIPELINE_RESULT_ERROR = 'error';

    /**
     * A timeout caused the pipeline run to be interrupted.
     *
     * @see TraceAttributes::CICD_PIPELINE_RESULT
     */
    public const CICD_PIPELINE_RESULT_TIMEOUT = 'timeout';

    /**
     * The pipeline run was cancelled, eg. by a user manually cancelling the pipeline run.
     *
     * @see TraceAttributes::CICD_PIPELINE_RESULT
     */
    public const CICD_PIPELINE_RESULT_CANCELLATION = 'cancellation';

    /**
     * The pipeline run was skipped, eg. due to a precondition not being met.
     *
     * @see TraceAttributes::CICD_PIPELINE_RESULT
     */
    public const CICD_PIPELINE_RESULT_SKIP = 'skip';

    /**
     * The run pending state spans from the event triggering the pipeline run until the execution of the run starts (eg. time spent in a queue, provisioning agents, creating run resources).
     *
     * @see TraceAttributes::CICD_PIPELINE_RUN_STATE
     */
    public const CICD_PIPELINE_RUN_STATE_PENDING = 'pending';

    /**
     * The executing state spans the execution of any run tasks (eg. build, test).
     *
     * @see TraceAttributes::CICD_PIPELINE_RUN_STATE
     */
    public const CICD_PIPELINE_RUN_STATE_EXECUTING = 'executing';

    /**
     * The finalizing state spans from when the run has finished executing (eg. cleanup of run resources).
     *
     * @see TraceAttributes::CICD_PIPELINE_RUN_STATE
     */
    public const CICD_PIPELINE_RUN_STATE_FINALIZING = 'finalizing';

    /**
     * build
     *
     * @see TraceAttributes::CICD_PIPELINE_TASK_TYPE
     */
    public const CICD_PIPELINE_TASK_TYPE_BUILD = 'build';

    /**
     * test
     *
     * @see TraceAttributes::CICD_PIPELINE_TASK_TYPE
     */
    public const CICD_PIPELINE_TASK_TYPE_TEST = 'test';

    /**
     * deploy
     *
     * @see TraceAttributes::CICD_PIPELINE_TASK_TYPE
     */
    public const CICD_PIPELINE_TASK_TYPE_DEPLOY = 'deploy';

    /**
     * The worker is not performing work for the CICD system. It is available to the CICD system to perform work on (online / idle).
     *
     * @see TraceAttributes::CICD_WORKER_STATE
     */
    public const CICD_WORKER_STATE_AVAILABLE = 'available';

    /**
     * The worker is performing work for the CICD system.
     *
     * @see TraceAttributes::CICD_WORKER_STATE
     */
    public const CICD_WORKER_STATE_BUSY = 'busy';

    /**
     * The worker is not available to the CICD system (disconnected / down).
     *
     * @see TraceAttributes::CICD_WORKER_STATE
     */
    public const CICD_WORKER_STATE_OFFLINE = 'offline';

    /**
     * Alibaba Cloud Elastic Compute Service
     *
     * @see TraceAttributes::CLOUD_PLATFORM
     */
    public const CLOUD_PLATFORM_ALIBABA_CLOUD_ECS = 'alibaba_cloud_ecs';

    /**
     * Alibaba Cloud Function Compute
     *
     * @see TraceAttributes::CLOUD_PLATFORM
     */
    public const CLOUD_PLATFORM_ALIBABA_CLOUD_FC = 'alibaba_cloud_fc';

    /**
     * Red Hat OpenShift on Alibaba Cloud
     *
     * @see TraceAttributes::CLOUD_PLATFORM
     */
    public const CLOUD_PLATFORM_ALIBABA_CLOUD_OPENSHIFT = 'alibaba_cloud_openshift';

    /**
     * AWS Elastic Compute Cloud
     *
     * @see TraceAttributes::CLOUD_PLATFORM
     */
    public const CLOUD_PLATFORM_AWS_EC2 = 'aws_ec2';

    /**
     * AWS Elastic Container Service
     *
     * @see TraceAttributes::CLOUD_PLATFORM
     */
    public const CLOUD_PLATFORM_AWS_ECS = 'aws_ecs';

    /**
     * AWS Elastic Kubernetes Service
     *
     * @see TraceAttributes::CLOUD_PLATFORM
     */
    public const CLOUD_PLATFORM_AWS_EKS = 'aws_eks';

    /**
     * AWS Lambda
     *
     * @see TraceAttributes::CLOUD_PLATFORM
     */
    public const CLOUD_PLATFORM_AWS_LAMBDA = 'aws_lambda';

    /**
     * AWS Elastic Beanstalk
     *
     * @see TraceAttributes::CLOUD_PLATFORM
     */
    public const CLOUD_PLATFORM_AWS_ELASTIC_BEANSTALK = 'aws_elastic_beanstalk';

    /**
     * AWS App Runner
     *
     * @see TraceAttributes::CLOUD_PLATFORM
     */
    public const CLOUD_PLATFORM_AWS_APP_RUNNER = 'aws_app_runner';

    /**
     * Red Hat OpenShift on AWS (ROSA)
     *
     * @see TraceAttributes::CLOUD_PLATFORM
     */
    public const CLOUD_PLATFORM_AWS_OPENSHIFT = 'aws_openshift';

    /**
     * Azure Virtual Machines
     *
     * @see TraceAttributes::CLOUD_PLATFORM
     */
    public const CLOUD_PLATFORM_AZURE_VM = 'azure_vm';

    /**
     * Azure Container Apps
     *
     * @see TraceAttributes::CLOUD_PLATFORM
     */
    public const CLOUD_PLATFORM_AZURE_CONTAINER_APPS = 'azure_container_apps';

    /**
     * Azure Container Instances
     *
     * @see TraceAttributes::CLOUD_PLATFORM
     */
    public const CLOUD_PLATFORM_AZURE_CONTAINER_INSTANCES = 'azure_container_instances';

    /**
     * Azure Kubernetes Service
     *
     * @see TraceAttributes::CLOUD_PLATFORM
     */
    public const CLOUD_PLATFORM_AZURE_AKS = 'azure_aks';

    /**
     * Azure Functions
     *
     * @see TraceAttributes::CLOUD_PLATFORM
     */
    public const CLOUD_PLATFORM_AZURE_FUNCTIONS = 'azure_functions';

    /**
     * Azure App Service
     *
     * @see TraceAttributes::CLOUD_PLATFORM
     */
    public const CLOUD_PLATFORM_AZURE_APP_SERVICE = 'azure_app_service';

    /**
     * Azure Red Hat OpenShift
     *
     * @see TraceAttributes::CLOUD_PLATFORM
     */
    public const CLOUD_PLATFORM_AZURE_OPENSHIFT = 'azure_openshift';

    /**
     * Google Bare Metal Solution (BMS)
     *
     * @see TraceAttributes::CLOUD_PLATFORM
     */
    public const CLOUD_PLATFORM_GCP_BARE_METAL_SOLUTION = 'gcp_bare_metal_solution';

    /**
     * Google Cloud Compute Engine (GCE)
     *
     * @see TraceAttributes::CLOUD_PLATFORM
     */
    public const CLOUD_PLATFORM_GCP_COMPUTE_ENGINE = 'gcp_compute_engine';

    /**
     * Google Cloud Run
     *
     * @see TraceAttributes::CLOUD_PLATFORM
     */
    public const CLOUD_PLATFORM_GCP_CLOUD_RUN = 'gcp_cloud_run';

    /**
     * Google Cloud Kubernetes Engine (GKE)
     *
     * @see TraceAttributes::CLOUD_PLATFORM
     */
    public const CLOUD_PLATFORM_GCP_KUBERNETES_ENGINE = 'gcp_kubernetes_engine';

    /**
     * Google Cloud Functions (GCF)
     *
     * @see TraceAttributes::CLOUD_PLATFORM
     */
    public const CLOUD_PLATFORM_GCP_CLOUD_FUNCTIONS = 'gcp_cloud_functions';

    /**
     * Google Cloud App Engine (GAE)
     *
     * @see TraceAttributes::CLOUD_PLATFORM
     */
    public const CLOUD_PLATFORM_GCP_APP_ENGINE = 'gcp_app_engine';

    /**
     * Red Hat OpenShift on Google Cloud
     *
     * @see TraceAttributes::CLOUD_PLATFORM
     */
    public const CLOUD_PLATFORM_GCP_OPENSHIFT = 'gcp_openshift';

    /**
     * Red Hat OpenShift on IBM Cloud
     *
     * @see TraceAttributes::CLOUD_PLATFORM
     */
    public const CLOUD_PLATFORM_IBM_CLOUD_OPENSHIFT = 'ibm_cloud_openshift';

    /**
     * Compute on Oracle Cloud Infrastructure (OCI)
     *
     * @see TraceAttributes::CLOUD_PLATFORM
     */
    public const CLOUD_PLATFORM_ORACLE_CLOUD_COMPUTE = 'oracle_cloud_compute';

    /**
     * Kubernetes Engine (OKE) on Oracle Cloud Infrastructure (OCI)
     *
     * @see TraceAttributes::CLOUD_PLATFORM
     */
    public const CLOUD_PLATFORM_ORACLE_CLOUD_OKE = 'oracle_cloud_oke';

    /**
     * Tencent Cloud Cloud Virtual Machine (CVM)
     *
     * @see TraceAttributes::CLOUD_PLATFORM
     */
    public const CLOUD_PLATFORM_TENCENT_CLOUD_CVM = 'tencent_cloud_cvm';

    /**
     * Tencent Cloud Elastic Kubernetes Service (EKS)
     *
     * @see TraceAttributes::CLOUD_PLATFORM
     */
    public const CLOUD_PLATFORM_TENCENT_CLOUD_EKS = 'tencent_cloud_eks';

    /**
     * Tencent Cloud Serverless Cloud Function (SCF)
     *
     * @see TraceAttributes::CLOUD_PLATFORM
     */
    public const CLOUD_PLATFORM_TENCENT_CLOUD_SCF = 'tencent_cloud_scf';

    /**
     * Alibaba Cloud
     *
     * @see TraceAttributes::CLOUD_PROVIDER
     */
    public const CLOUD_PROVIDER_ALIBABA_CLOUD = 'alibaba_cloud';

    /**
     * Amazon Web Services
     *
     * @see TraceAttributes::CLOUD_PROVIDER
     */
    public const CLOUD_PROVIDER_AWS = 'aws';

    /**
     * Microsoft Azure
     *
     * @see TraceAttributes::CLOUD_PROVIDER
     */
    public const CLOUD_PROVIDER_AZURE = 'azure';

    /**
     * Google Cloud Platform
     *
     * @see TraceAttributes::CLOUD_PROVIDER
     */
    public const CLOUD_PROVIDER_GCP = 'gcp';

    /**
     * Heroku Platform as a Service
     *
     * @see TraceAttributes::CLOUD_PROVIDER
     */
    public const CLOUD_PROVIDER_HEROKU = 'heroku';

    /**
     * IBM Cloud
     *
     * @see TraceAttributes::CLOUD_PROVIDER
     */
    public const CLOUD_PROVIDER_IBM_CLOUD = 'ibm_cloud';

    /**
     * Oracle Cloud Infrastructure (OCI)
     *
     * @see TraceAttributes::CLOUD_PROVIDER
     */
    public const CLOUD_PROVIDER_ORACLE_CLOUD = 'oracle_cloud';

    /**
     * Tencent Cloud
     *
     * @see TraceAttributes::CLOUD_PROVIDER
     */
    public const CLOUD_PROVIDER_TENCENT_CLOUD = 'tencent_cloud';

    /**
     * When tasks of the cgroup are in user mode (Linux). When all container processes are in user mode (Windows).
     *
     * @see TraceAttributes::CONTAINER_CPU_STATE
     */
    public const CONTAINER_CPU_STATE_USER = 'user';

    /**
     * When CPU is used by the system (host OS)
     *
     * @see TraceAttributes::CONTAINER_CPU_STATE
     */
    public const CONTAINER_CPU_STATE_SYSTEM = 'system';

    /**
     * When tasks of the cgroup are in kernel mode (Linux). When all container processes are in kernel mode (Windows).
     *
     * @see TraceAttributes::CONTAINER_CPU_STATE
     */
    public const CONTAINER_CPU_STATE_KERNEL = 'kernel';

    /**
     * user
     *
     * @see TraceAttributes::CPU_MODE
     */
    public const CPU_MODE_USER = 'user';

    /**
     * system
     *
     * @see TraceAttributes::CPU_MODE
     */
    public const CPU_MODE_SYSTEM = 'system';

    /**
     * nice
     *
     * @see TraceAttributes::CPU_MODE
     */
    public const CPU_MODE_NICE = 'nice';

    /**
     * idle
     *
     * @see TraceAttributes::CPU_MODE
     */
    public const CPU_MODE_IDLE = 'idle';

    /**
     * iowait
     *
     * @see TraceAttributes::CPU_MODE
     */
    public const CPU_MODE_IOWAIT = 'iowait';

    /**
     * interrupt
     *
     * @see TraceAttributes::CPU_MODE
     */
    public const CPU_MODE_INTERRUPT = 'interrupt';

    /**
     * steal
     *
     * @see TraceAttributes::CPU_MODE
     */
    public const CPU_MODE_STEAL = 'steal';

    /**
     * kernel
     *
     * @see TraceAttributes::CPU_MODE
     */
    public const CPU_MODE_KERNEL = 'kernel';

    /**
     * all
     *
     * @see TraceAttributes::DB_CASSANDRA_CONSISTENCY_LEVEL
     */
    public const DB_CASSANDRA_CONSISTENCY_LEVEL_ALL = 'all';

    /**
     * each_quorum
     *
     * @see TraceAttributes::DB_CASSANDRA_CONSISTENCY_LEVEL
     */
    public const DB_CASSANDRA_CONSISTENCY_LEVEL_EACH_QUORUM = 'each_quorum';

    /**
     * quorum
     *
     * @see TraceAttributes::DB_CASSANDRA_CONSISTENCY_LEVEL
     */
    public const DB_CASSANDRA_CONSISTENCY_LEVEL_QUORUM = 'quorum';

    /**
     * local_quorum
     *
     * @see TraceAttributes::DB_CASSANDRA_CONSISTENCY_LEVEL
     */
    public const DB_CASSANDRA_CONSISTENCY_LEVEL_LOCAL_QUORUM = 'local_quorum';

    /**
     * one
     *
     * @see TraceAttributes::DB_CASSANDRA_CONSISTENCY_LEVEL
     */
    public const DB_CASSANDRA_CONSISTENCY_LEVEL_ONE = 'one';

    /**
     * two
     *
     * @see TraceAttributes::DB_CASSANDRA_CONSISTENCY_LEVEL
     */
    public const DB_CASSANDRA_CONSISTENCY_LEVEL_TWO = 'two';

    /**
     * three
     *
     * @see TraceAttributes::DB_CASSANDRA_CONSISTENCY_LEVEL
     */
    public const DB_CASSANDRA_CONSISTENCY_LEVEL_THREE = 'three';

    /**
     * local_one
     *
     * @see TraceAttributes::DB_CASSANDRA_CONSISTENCY_LEVEL
     */
    public const DB_CASSANDRA_CONSISTENCY_LEVEL_LOCAL_ONE = 'local_one';

    /**
     * any
     *
     * @see TraceAttributes::DB_CASSANDRA_CONSISTENCY_LEVEL
     */
    public const DB_CASSANDRA_CONSISTENCY_LEVEL_ANY = 'any';

    /**
     * serial
     *
     * @see TraceAttributes::DB_CASSANDRA_CONSISTENCY_LEVEL
     */
    public const DB_CASSANDRA_CONSISTENCY_LEVEL_SERIAL = 'serial';

    /**
     * local_serial
     *
     * @see TraceAttributes::DB_CASSANDRA_CONSISTENCY_LEVEL
     */
    public const DB_CASSANDRA_CONSISTENCY_LEVEL_LOCAL_SERIAL = 'local_serial';

    /**
     * idle
     *
     * @see TraceAttributes::DB_CLIENT_CONNECTION_STATE
     */
    public const DB_CLIENT_CONNECTION_STATE_IDLE = 'idle';

    /**
     * used
     *
     * @see TraceAttributes::DB_CLIENT_CONNECTION_STATE
     */
    public const DB_CLIENT_CONNECTION_STATE_USED = 'used';

    /**
     * idle
     *
     * @see TraceAttributes::DB_CLIENT_CONNECTIONS_STATE
     */
    public const DB_CLIENT_CONNECTIONS_STATE_IDLE = 'idle';

    /**
     * used
     *
     * @see TraceAttributes::DB_CLIENT_CONNECTIONS_STATE
     */
    public const DB_CLIENT_CONNECTIONS_STATE_USED = 'used';

    /**
     * Gateway (HTTP) connection.
     *
     * @see TraceAttributes::DB_COSMOSDB_CONNECTION_MODE
     */
    public const DB_COSMOSDB_CONNECTION_MODE_GATEWAY = 'gateway';

    /**
     * Direct connection.
     *
     * @see TraceAttributes::DB_COSMOSDB_CONNECTION_MODE
     */
    public const DB_COSMOSDB_CONNECTION_MODE_DIRECT = 'direct';

    /**
     * strong
     *
     * @see TraceAttributes::DB_COSMOSDB_CONSISTENCY_LEVEL
     */
    public const DB_COSMOSDB_CONSISTENCY_LEVEL_STRONG = 'Strong';

    /**
     * bounded_staleness
     *
     * @see TraceAttributes::DB_COSMOSDB_CONSISTENCY_LEVEL
     */
    public const DB_COSMOSDB_CONSISTENCY_LEVEL_BOUNDED_STALENESS = 'BoundedStaleness';

    /**
     * session
     *
     * @see TraceAttributes::DB_COSMOSDB_CONSISTENCY_LEVEL
     */
    public const DB_COSMOSDB_CONSISTENCY_LEVEL_SESSION = 'Session';

    /**
     * eventual
     *
     * @see TraceAttributes::DB_COSMOSDB_CONSISTENCY_LEVEL
     */
    public const DB_COSMOSDB_CONSISTENCY_LEVEL_EVENTUAL = 'Eventual';

    /**
     * consistent_prefix
     *
     * @see TraceAttributes::DB_COSMOSDB_CONSISTENCY_LEVEL
     */
    public const DB_COSMOSDB_CONSISTENCY_LEVEL_CONSISTENT_PREFIX = 'ConsistentPrefix';

    /**
     * batch
     *
     * @see TraceAttributes::DB_COSMOSDB_OPERATION_TYPE
     */
    public const DB_COSMOSDB_OPERATION_TYPE_BATCH = 'batch';

    /**
     * create
     *
     * @see TraceAttributes::DB_COSMOSDB_OPERATION_TYPE
     */
    public const DB_COSMOSDB_OPERATION_TYPE_CREATE = 'create';

    /**
     * delete
     *
     * @see TraceAttributes::DB_COSMOSDB_OPERATION_TYPE
     */
    public const DB_COSMOSDB_OPERATION_TYPE_DELETE = 'delete';

    /**
     * execute
     *
     * @see TraceAttributes::DB_COSMOSDB_OPERATION_TYPE
     */
    public const DB_COSMOSDB_OPERATION_TYPE_EXECUTE = 'execute';

    /**
     * execute_javascript
     *
     * @see TraceAttributes::DB_COSMOSDB_OPERATION_TYPE
     */
    public const DB_COSMOSDB_OPERATION_TYPE_EXECUTE_JAVASCRIPT = 'execute_javascript';

    /**
     * invalid
     *
     * @see TraceAttributes::DB_COSMOSDB_OPERATION_TYPE
     */
    public const DB_COSMOSDB_OPERATION_TYPE_INVALID = 'invalid';

    /**
     * head
     *
     * @see TraceAttributes::DB_COSMOSDB_OPERATION_TYPE
     */
    public const DB_COSMOSDB_OPERATION_TYPE_HEAD = 'head';

    /**
     * head_feed
     *
     * @see TraceAttributes::DB_COSMOSDB_OPERATION_TYPE
     */
    public const DB_COSMOSDB_OPERATION_TYPE_HEAD_FEED = 'head_feed';

    /**
     * patch
     *
     * @see TraceAttributes::DB_COSMOSDB_OPERATION_TYPE
     */
    public const DB_COSMOSDB_OPERATION_TYPE_PATCH = 'patch';

    /**
     * query
     *
     * @see TraceAttributes::DB_COSMOSDB_OPERATION_TYPE
     */
    public const DB_COSMOSDB_OPERATION_TYPE_QUERY = 'query';

    /**
     * query_plan
     *
     * @see TraceAttributes::DB_COSMOSDB_OPERATION_TYPE
     */
    public const DB_COSMOSDB_OPERATION_TYPE_QUERY_PLAN = 'query_plan';

    /**
     * read
     *
     * @see TraceAttributes::DB_COSMOSDB_OPERATION_TYPE
     */
    public const DB_COSMOSDB_OPERATION_TYPE_READ = 'read';

    /**
     * read_feed
     *
     * @see TraceAttributes::DB_COSMOSDB_OPERATION_TYPE
     */
    public const DB_COSMOSDB_OPERATION_TYPE_READ_FEED = 'read_feed';

    /**
     * replace
     *
     * @see TraceAttributes::DB_COSMOSDB_OPERATION_TYPE
     */
    public const DB_COSMOSDB_OPERATION_TYPE_REPLACE = 'replace';

    /**
     * upsert
     *
     * @see TraceAttributes::DB_COSMOSDB_OPERATION_TYPE
     */
    public const DB_COSMOSDB_OPERATION_TYPE_UPSERT = 'upsert';

    /**
     * Some other SQL database. Fallback only. See notes.
     *
     * @see TraceAttributes::DB_SYSTEM
     */
    public const DB_SYSTEM_OTHER_SQL = 'other_sql';

    /**
     * Adabas (Adaptable Database System)
     *
     * @see TraceAttributes::DB_SYSTEM
     */
    public const DB_SYSTEM_ADABAS = 'adabas';

    /**
     * Deprecated, use `intersystems_cache` instead.
     *
     * @see TraceAttributes::DB_SYSTEM
     * @deprecated Replaced by `intersystems_cache`.
     */
    public const DB_SYSTEM_CACHE = 'cache';

    /**
     * InterSystems Caché
     *
     * @see TraceAttributes::DB_SYSTEM
     */
    public const DB_SYSTEM_INTERSYSTEMS_CACHE = 'intersystems_cache';

    /**
     * Apache Cassandra
     *
     * @see TraceAttributes::DB_SYSTEM
     */
    public const DB_SYSTEM_CASSANDRA = 'cassandra';

    /**
     * ClickHouse
     *
     * @see TraceAttributes::DB_SYSTEM
     */
    public const DB_SYSTEM_CLICKHOUSE = 'clickhouse';

    /**
     * Deprecated, use `other_sql` instead.
     *
     * @see TraceAttributes::DB_SYSTEM
     * @deprecated Replaced by `other_sql`.
     */
    public const DB_SYSTEM_CLOUDSCAPE = 'cloudscape';

    /**
     * CockroachDB
     *
     * @see TraceAttributes::DB_SYSTEM
     */
    public const DB_SYSTEM_COCKROACHDB = 'cockroachdb';

    /**
     * Deprecated, no replacement at this time.
     *
     * @see TraceAttributes::DB_SYSTEM
     * @deprecated Removed.
     */
    public const DB_SYSTEM_COLDFUSION = 'coldfusion';

    /**
     * Microsoft Azure Cosmos DB
     *
     * @see TraceAttributes::DB_SYSTEM
     */
    public const DB_SYSTEM_COSMOSDB = 'cosmosdb';

    /**
     * Couchbase
     *
     * @see TraceAttributes::DB_SYSTEM
     */
    public const DB_SYSTEM_COUCHBASE = 'couchbase';

    /**
     * CouchDB
     *
     * @see TraceAttributes::DB_SYSTEM
     */
    public const DB_SYSTEM_COUCHDB = 'couchdb';

    /**
     * IBM Db2
     *
     * @see TraceAttributes::DB_SYSTEM
     */
    public const DB_SYSTEM_DB2 = 'db2';

    /**
     * Apache Derby
     *
     * @see TraceAttributes::DB_SYSTEM
     */
    public const DB_SYSTEM_DERBY = 'derby';

    /**
     * Amazon DynamoDB
     *
     * @see TraceAttributes::DB_SYSTEM
     */
    public const DB_SYSTEM_DYNAMODB = 'dynamodb';

    /**
     * EnterpriseDB
     *
     * @see TraceAttributes::DB_SYSTEM
     */
    public const DB_SYSTEM_EDB = 'edb';

    /**
     * Elasticsearch
     *
     * @see TraceAttributes::DB_SYSTEM
     */
    public const DB_SYSTEM_ELASTICSEARCH = 'elasticsearch';

    /**
     * FileMaker
     *
     * @see TraceAttributes::DB_SYSTEM
     */
    public const DB_SYSTEM_FILEMAKER = 'filemaker';

    /**
     * Firebird
     *
     * @see TraceAttributes::DB_SYSTEM
     */
    public const DB_SYSTEM_FIREBIRD = 'firebird';

    /**
     * Deprecated, use `other_sql` instead.
     *
     * @see TraceAttributes::DB_SYSTEM
     * @deprecated Replaced by `other_sql`.
     */
    public const DB_SYSTEM_FIRSTSQL = 'firstsql';

    /**
     * Apache Geode
     *
     * @see TraceAttributes::DB_SYSTEM
     */
    public const DB_SYSTEM_GEODE = 'geode';

    /**
     * H2
     *
     * @see TraceAttributes::DB_SYSTEM
     */
    public const DB_SYSTEM_H2 = 'h2';

    /**
     * SAP HANA
     *
     * @see TraceAttributes::DB_SYSTEM
     */
    public const DB_SYSTEM_HANADB = 'hanadb';

    /**
     * Apache HBase
     *
     * @see TraceAttributes::DB_SYSTEM
     */
    public const DB_SYSTEM_HBASE = 'hbase';

    /**
     * Apache Hive
     *
     * @see TraceAttributes::DB_SYSTEM
     */
    public const DB_SYSTEM_HIVE = 'hive';

    /**
     * HyperSQL DataBase
     *
     * @see TraceAttributes::DB_SYSTEM
     */
    public const DB_SYSTEM_HSQLDB = 'hsqldb';

    /**
     * InfluxDB
     *
     * @see TraceAttributes::DB_SYSTEM
     */
    public const DB_SYSTEM_INFLUXDB = 'influxdb';

    /**
     * Informix
     *
     * @see TraceAttributes::DB_SYSTEM
     */
    public const DB_SYSTEM_INFORMIX = 'informix';

    /**
     * Ingres
     *
     * @see TraceAttributes::DB_SYSTEM
     */
    public const DB_SYSTEM_INGRES = 'ingres';

    /**
     * InstantDB
     *
     * @see TraceAttributes::DB_SYSTEM
     */
    public const DB_SYSTEM_INSTANTDB = 'instantdb';

    /**
     * InterBase
     *
     * @see TraceAttributes::DB_SYSTEM
     */
    public const DB_SYSTEM_INTERBASE = 'interbase';

    /**
     * MariaDB (This value has stability level RELEASE CANDIDATE)
     *
     * @see TraceAttributes::DB_SYSTEM
     */
    public const DB_SYSTEM_MARIADB = 'mariadb';

    /**
     * SAP MaxDB
     *
     * @see TraceAttributes::DB_SYSTEM
     */
    public const DB_SYSTEM_MAXDB = 'maxdb';

    /**
     * Memcached
     *
     * @see TraceAttributes::DB_SYSTEM
     */
    public const DB_SYSTEM_MEMCACHED = 'memcached';

    /**
     * MongoDB
     *
     * @see TraceAttributes::DB_SYSTEM
     */
    public const DB_SYSTEM_MONGODB = 'mongodb';

    /**
     * Microsoft SQL Server (This value has stability level RELEASE CANDIDATE)
     *
     * @see TraceAttributes::DB_SYSTEM
     */
    public const DB_SYSTEM_MSSQL = 'mssql';

    /**
     * Deprecated, Microsoft SQL Server Compact is discontinued.
     *
     * @see TraceAttributes::DB_SYSTEM
     * @deprecated Removed, use `other_sql` instead.
     */
    public const DB_SYSTEM_MSSQLCOMPACT = 'mssqlcompact';

    /**
     * MySQL (This value has stability level RELEASE CANDIDATE)
     *
     * @see TraceAttributes::DB_SYSTEM
     */
    public const DB_SYSTEM_MYSQL = 'mysql';

    /**
     * Neo4j
     *
     * @see TraceAttributes::DB_SYSTEM
     */
    public const DB_SYSTEM_NEO4J = 'neo4j';

    /**
     * Netezza
     *
     * @see TraceAttributes::DB_SYSTEM
     */
    public const DB_SYSTEM_NETEZZA = 'netezza';

    /**
     * OpenSearch
     *
     * @see TraceAttributes::DB_SYSTEM
     */
    public const DB_SYSTEM_OPENSEARCH = 'opensearch';

    /**
     * Oracle Database
     *
     * @see TraceAttributes::DB_SYSTEM
     */
    public const DB_SYSTEM_ORACLE = 'oracle';

    /**
     * Pervasive PSQL
     *
     * @see TraceAttributes::DB_SYSTEM
     */
    public const DB_SYSTEM_PERVASIVE = 'pervasive';

    /**
     * PointBase
     *
     * @see TraceAttributes::DB_SYSTEM
     */
    public const DB_SYSTEM_POINTBASE = 'pointbase';

    /**
     * PostgreSQL (This value has stability level RELEASE CANDIDATE)
     *
     * @see TraceAttributes::DB_SYSTEM
     */
    public const DB_SYSTEM_POSTGRESQL = 'postgresql';

    /**
     * Progress Database
     *
     * @see TraceAttributes::DB_SYSTEM
     */
    public const DB_SYSTEM_PROGRESS = 'progress';

    /**
     * Redis
     *
     * @see TraceAttributes::DB_SYSTEM
     */
    public const DB_SYSTEM_REDIS = 'redis';

    /**
     * Amazon Redshift
     *
     * @see TraceAttributes::DB_SYSTEM
     */
    public const DB_SYSTEM_REDSHIFT = 'redshift';

    /**
     * Cloud Spanner
     *
     * @see TraceAttributes::DB_SYSTEM
     */
    public const DB_SYSTEM_SPANNER = 'spanner';

    /**
     * SQLite
     *
     * @see TraceAttributes::DB_SYSTEM
     */
    public const DB_SYSTEM_SQLITE = 'sqlite';

    /**
     * Sybase
     *
     * @see TraceAttributes::DB_SYSTEM
     */
    public const DB_SYSTEM_SYBASE = 'sybase';

    /**
     * Teradata
     *
     * @see TraceAttributes::DB_SYSTEM
     */
    public const DB_SYSTEM_TERADATA = 'teradata';

    /**
     * Trino
     *
     * @see TraceAttributes::DB_SYSTEM
     */
    public const DB_SYSTEM_TRINO = 'trino';

    /**
     * Vertica
     *
     * @see TraceAttributes::DB_SYSTEM
     */
    public const DB_SYSTEM_VERTICA = 'vertica';

    /**
     * Some other SQL database. Fallback only.
     *
     * @see TraceAttributes::DB_SYSTEM_NAME
     */
    public const DB_SYSTEM_NAME_OTHER_SQL = 'other_sql';

    /**
     * [Adabas (Adaptable Database System)](https://documentation.softwareag.com/?pf=adabas)
     *
     * @see TraceAttributes::DB_SYSTEM_NAME
     */
    public const DB_SYSTEM_NAME_SOFTWAREAG_ADABAS = 'softwareag.adabas';

    /**
     * [Actian Ingres](https://www.actian.com/databases/ingres/)
     *
     * @see TraceAttributes::DB_SYSTEM_NAME
     */
    public const DB_SYSTEM_NAME_ACTIAN_INGRES = 'actian.ingres';

    /**
     * [Amazon DynamoDB](https://aws.amazon.com/pm/dynamodb/)
     *
     * @see TraceAttributes::DB_SYSTEM_NAME
     */
    public const DB_SYSTEM_NAME_AWS_DYNAMODB = 'aws.dynamodb';

    /**
     * [Amazon Redshift](https://aws.amazon.com/redshift/)
     *
     * @see TraceAttributes::DB_SYSTEM_NAME
     */
    public const DB_SYSTEM_NAME_AWS_REDSHIFT = 'aws.redshift';

    /**
     * [Azure Cosmos DB](https://learn.microsoft.com/azure/cosmos-db)
     *
     * @see TraceAttributes::DB_SYSTEM_NAME
     */
    public const DB_SYSTEM_NAME_AZURE_COSMOSDB = 'azure.cosmosdb';

    /**
     * [InterSystems Caché](https://www.intersystems.com/products/cache/)
     *
     * @see TraceAttributes::DB_SYSTEM_NAME
     */
    public const DB_SYSTEM_NAME_INTERSYSTEMS_CACHE = 'intersystems.cache';

    /**
     * [Apache Cassandra](https://cassandra.apache.org/)
     *
     * @see TraceAttributes::DB_SYSTEM_NAME
     */
    public const DB_SYSTEM_NAME_CASSANDRA = 'cassandra';

    /**
     * [ClickHouse](https://clickhouse.com/)
     *
     * @see TraceAttributes::DB_SYSTEM_NAME
     */
    public const DB_SYSTEM_NAME_CLICKHOUSE = 'clickhouse';

    /**
     * [CockroachDB](https://www.cockroachlabs.com/)
     *
     * @see TraceAttributes::DB_SYSTEM_NAME
     */
    public const DB_SYSTEM_NAME_COCKROACHDB = 'cockroachdb';

    /**
     * [Couchbase](https://www.couchbase.com/)
     *
     * @see TraceAttributes::DB_SYSTEM_NAME
     */
    public const DB_SYSTEM_NAME_COUCHBASE = 'couchbase';

    /**
     * [Apache CouchDB](https://couchdb.apache.org/)
     *
     * @see TraceAttributes::DB_SYSTEM_NAME
     */
    public const DB_SYSTEM_NAME_COUCHDB = 'couchdb';

    /**
     * [Apache Derby](https://db.apache.org/derby/)
     *
     * @see TraceAttributes::DB_SYSTEM_NAME
     */
    public const DB_SYSTEM_NAME_DERBY = 'derby';

    /**
     * [Elasticsearch](https://www.elastic.co/elasticsearch)
     *
     * @see TraceAttributes::DB_SYSTEM_NAME
     */
    public const DB_SYSTEM_NAME_ELASTICSEARCH = 'elasticsearch';

    /**
     * [Firebird](https://www.firebirdsql.org/)
     *
     * @see TraceAttributes::DB_SYSTEM_NAME
     */
    public const DB_SYSTEM_NAME_FIREBIRDSQL = 'firebirdsql';

    /**
     * [Google Cloud Spanner](https://cloud.google.com/spanner)
     *
     * @see TraceAttributes::DB_SYSTEM_NAME
     */
    public const DB_SYSTEM_NAME_GCP_SPANNER = 'gcp.spanner';

    /**
     * [Apache Geode](https://geode.apache.org/)
     *
     * @see TraceAttributes::DB_SYSTEM_NAME
     */
    public const DB_SYSTEM_NAME_GEODE = 'geode';

    /**
     * [H2 Database](https://h2database.com/)
     *
     * @see TraceAttributes::DB_SYSTEM_NAME
     */
    public const DB_SYSTEM_NAME_H2DATABASE = 'h2database';

    /**
     * [Apache HBase](https://hbase.apache.org/)
     *
     * @see TraceAttributes::DB_SYSTEM_NAME
     */
    public const DB_SYSTEM_NAME_HBASE = 'hbase';

    /**
     * [Apache Hive](https://hive.apache.org/)
     *
     * @see TraceAttributes::DB_SYSTEM_NAME
     */
    public const DB_SYSTEM_NAME_HIVE = 'hive';

    /**
     * [HyperSQL Database](https://hsqldb.org/)
     *
     * @see TraceAttributes::DB_SYSTEM_NAME
     */
    public const DB_SYSTEM_NAME_HSQLDB = 'hsqldb';

    /**
     * [IBM Db2](https://www.ibm.com/db2)
     *
     * @see TraceAttributes::DB_SYSTEM_NAME
     */
    public const DB_SYSTEM_NAME_IBM_DB2 = 'ibm.db2';

    /**
     * [IBM Informix](https://www.ibm.com/products/informix)
     *
     * @see TraceAttributes::DB_SYSTEM_NAME
     */
    public const DB_SYSTEM_NAME_IBM_INFORMIX = 'ibm.informix';

    /**
     * [IBM Netezza](https://www.ibm.com/products/netezza)
     *
     * @see TraceAttributes::DB_SYSTEM_NAME
     */
    public const DB_SYSTEM_NAME_IBM_NETEZZA = 'ibm.netezza';

    /**
     * [InfluxDB](https://www.influxdata.com/)
     *
     * @see TraceAttributes::DB_SYSTEM_NAME
     */
    public const DB_SYSTEM_NAME_INFLUXDB = 'influxdb';

    /**
     * [Instant](https://www.instantdb.com/)
     *
     * @see TraceAttributes::DB_SYSTEM_NAME
     */
    public const DB_SYSTEM_NAME_INSTANTDB = 'instantdb';

    /**
     * [MariaDB](https://mariadb.org/)
     *
     * @see TraceAttributes::DB_SYSTEM_NAME
     */
    public const DB_SYSTEM_NAME_MARIADB = 'mariadb';

    /**
     * [Memcached](https://memcached.org/)
     *
     * @see TraceAttributes::DB_SYSTEM_NAME
     */
    public const DB_SYSTEM_NAME_MEMCACHED = 'memcached';

    /**
     * [MongoDB](https://www.mongodb.com/)
     *
     * @see TraceAttributes::DB_SYSTEM_NAME
     */
    public const DB_SYSTEM_NAME_MONGODB = 'mongodb';

    /**
     * [Microsoft SQL Server](https://www.microsoft.com/sql-server)
     *
     * @see TraceAttributes::DB_SYSTEM_NAME
     */
    public const DB_SYSTEM_NAME_MICROSOFT_SQL_SERVER = 'microsoft.sql_server';

    /**
     * [MySQL](https://www.mysql.com/)
     *
     * @see TraceAttributes::DB_SYSTEM_NAME
     */
    public const DB_SYSTEM_NAME_MYSQL = 'mysql';

    /**
     * [Neo4j](https://neo4j.com/)
     *
     * @see TraceAttributes::DB_SYSTEM_NAME
     */
    public const DB_SYSTEM_NAME_NEO4J = 'neo4j';

    /**
     * [OpenSearch](https://opensearch.org/)
     *
     * @see TraceAttributes::DB_SYSTEM_NAME
     */
    public const DB_SYSTEM_NAME_OPENSEARCH = 'opensearch';

    /**
     * [Oracle Database](https://www.oracle.com/database/)
     *
     * @see TraceAttributes::DB_SYSTEM_NAME
     */
    public const DB_SYSTEM_NAME_ORACLE_DB = 'oracle.db';

    /**
     * [PostgreSQL](https://www.postgresql.org/)
     *
     * @see TraceAttributes::DB_SYSTEM_NAME
     */
    public const DB_SYSTEM_NAME_POSTGRESQL = 'postgresql';

    /**
     * [Redis](https://redis.io/)
     *
     * @see TraceAttributes::DB_SYSTEM_NAME
     */
    public const DB_SYSTEM_NAME_REDIS = 'redis';

    /**
     * [SAP HANA](https://www.sap.com/products/technology-platform/hana/what-is-sap-hana.html)
     *
     * @see TraceAttributes::DB_SYSTEM_NAME
     */
    public const DB_SYSTEM_NAME_SAP_HANA = 'sap.hana';

    /**
     * [SAP MaxDB](https://maxdb.sap.com/)
     *
     * @see TraceAttributes::DB_SYSTEM_NAME
     */
    public const DB_SYSTEM_NAME_SAP_MAXDB = 'sap.maxdb';

    /**
     * [SQLite](https://www.sqlite.org/)
     *
     * @see TraceAttributes::DB_SYSTEM_NAME
     */
    public const DB_SYSTEM_NAME_SQLITE = 'sqlite';

    /**
     * [Teradata](https://www.teradata.com/)
     *
     * @see TraceAttributes::DB_SYSTEM_NAME
     */
    public const DB_SYSTEM_NAME_TERADATA = 'teradata';

    /**
     * [Trino](https://trino.io/)
     *
     * @see TraceAttributes::DB_SYSTEM_NAME
     */
    public const DB_SYSTEM_NAME_TRINO = 'trino';

    /**
     * failed
     *
     * @see TraceAttributes::DEPLOYMENT_STATUS
     */
    public const DEPLOYMENT_STATUS_FAILED = 'failed';

    /**
     * succeeded
     *
     * @see TraceAttributes::DEPLOYMENT_STATUS
     */
    public const DEPLOYMENT_STATUS_SUCCEEDED = 'succeeded';

    /**
     * read
     *
     * @see TraceAttributes::DISK_IO_DIRECTION
     */
    public const DISK_IO_DIRECTION_READ = 'read';

    /**
     * write
     *
     * @see TraceAttributes::DISK_IO_DIRECTION
     */
    public const DISK_IO_DIRECTION_WRITE = 'write';

    /**
     * A fallback error value to be used when the instrumentation doesn't define a custom value.
     *
     * @see TraceAttributes::ERROR_TYPE
     */
    public const ERROR_TYPE_OTHER = '_OTHER';

    /**
     * When a new object is created.
     *
     * @see TraceAttributes::FAAS_DOCUMENT_OPERATION
     */
    public const FAAS_DOCUMENT_OPERATION_INSERT = 'insert';

    /**
     * When an object is modified.
     *
     * @see TraceAttributes::FAAS_DOCUMENT_OPERATION
     */
    public const FAAS_DOCUMENT_OPERATION_EDIT = 'edit';

    /**
     * When an object is deleted.
     *
     * @see TraceAttributes::FAAS_DOCUMENT_OPERATION
     */
    public const FAAS_DOCUMENT_OPERATION_DELETE = 'delete';

    /**
     * Alibaba Cloud
     *
     * @see TraceAttributes::FAAS_INVOKED_PROVIDER
     */
    public const FAAS_INVOKED_PROVIDER_ALIBABA_CLOUD = 'alibaba_cloud';

    /**
     * Amazon Web Services
     *
     * @see TraceAttributes::FAAS_INVOKED_PROVIDER
     */
    public const FAAS_INVOKED_PROVIDER_AWS = 'aws';

    /**
     * Microsoft Azure
     *
     * @see TraceAttributes::FAAS_INVOKED_PROVIDER
     */
    public const FAAS_INVOKED_PROVIDER_AZURE = 'azure';

    /**
     * Google Cloud Platform
     *
     * @see TraceAttributes::FAAS_INVOKED_PROVIDER
     */
    public const FAAS_INVOKED_PROVIDER_GCP = 'gcp';

    /**
     * Tencent Cloud
     *
     * @see TraceAttributes::FAAS_INVOKED_PROVIDER
     */
    public const FAAS_INVOKED_PROVIDER_TENCENT_CLOUD = 'tencent_cloud';

    /**
     * A response to some data source operation such as a database or filesystem read/write
     *
     * @see TraceAttributes::FAAS_TRIGGER
     */
    public const FAAS_TRIGGER_DATASOURCE = 'datasource';

    /**
     * To provide an answer to an inbound HTTP request
     *
     * @see TraceAttributes::FAAS_TRIGGER
     */
    public const FAAS_TRIGGER_HTTP = 'http';

    /**
     * A function is set to be executed when messages are sent to a messaging system
     *
     * @see TraceAttributes::FAAS_TRIGGER
     */
    public const FAAS_TRIGGER_PUBSUB = 'pubsub';

    /**
     * A function is scheduled to be executed regularly
     *
     * @see TraceAttributes::FAAS_TRIGGER
     */
    public const FAAS_TRIGGER_TIMER = 'timer';

    /**
     * If none of the others apply
     *
     * @see TraceAttributes::FAAS_TRIGGER
     */
    public const FAAS_TRIGGER_OTHER = 'other';

    /**
     * The resolved value is static (no dynamic evaluation).
     *
     * @see TraceAttributes::FEATURE_FLAG_EVALUATION_REASON
     */
    public const FEATURE_FLAG_EVALUATION_REASON_STATIC = 'static';

    /**
     * The resolved value fell back to a pre-configured value (no dynamic evaluation occurred or dynamic evaluation yielded no result).
     *
     * @see TraceAttributes::FEATURE_FLAG_EVALUATION_REASON
     */
    public const FEATURE_FLAG_EVALUATION_REASON_DEFAULT = 'default';

    /**
     * The resolved value was the result of a dynamic evaluation, such as a rule or specific user-targeting.
     *
     * @see TraceAttributes::FEATURE_FLAG_EVALUATION_REASON
     */
    public const FEATURE_FLAG_EVALUATION_REASON_TARGETING_MATCH = 'targeting_match';

    /**
     * The resolved value was the result of pseudorandom assignment.
     *
     * @see TraceAttributes::FEATURE_FLAG_EVALUATION_REASON
     */
    public const FEATURE_FLAG_EVALUATION_REASON_SPLIT = 'split';

    /**
     * The resolved value was retrieved from cache.
     *
     * @see TraceAttributes::FEATURE_FLAG_EVALUATION_REASON
     */
    public const FEATURE_FLAG_EVALUATION_REASON_CACHED = 'cached';

    /**
     * The resolved value was the result of the flag being disabled in the management system.
     *
     * @see TraceAttributes::FEATURE_FLAG_EVALUATION_REASON
     */
    public const FEATURE_FLAG_EVALUATION_REASON_DISABLED = 'disabled';

    /**
     * The reason for the resolved value could not be determined.
     *
     * @see TraceAttributes::FEATURE_FLAG_EVALUATION_REASON
     */
    public const FEATURE_FLAG_EVALUATION_REASON_UNKNOWN = 'unknown';

    /**
     * The resolved value is non-authoritative or possibly out of date
     *
     * @see TraceAttributes::FEATURE_FLAG_EVALUATION_REASON
     */
    public const FEATURE_FLAG_EVALUATION_REASON_STALE = 'stale';

    /**
     * The resolved value was the result of an error.
     *
     * @see TraceAttributes::FEATURE_FLAG_EVALUATION_REASON
     */
    public const FEATURE_FLAG_EVALUATION_REASON_ERROR = 'error';

    /**
     * Text response format
     *
     * @see TraceAttributes::GEN_AI_OPENAI_REQUEST_RESPONSE_FORMAT
     */
    public const GEN_AI_OPENAI_REQUEST_RESPONSE_FORMAT_TEXT = 'text';

    /**
     * JSON object response format
     *
     * @see TraceAttributes::GEN_AI_OPENAI_REQUEST_RESPONSE_FORMAT
     */
    public const GEN_AI_OPENAI_REQUEST_RESPONSE_FORMAT_JSON_OBJECT = 'json_object';

    /**
     * JSON schema response format
     *
     * @see TraceAttributes::GEN_AI_OPENAI_REQUEST_RESPONSE_FORMAT
     */
    public const GEN_AI_OPENAI_REQUEST_RESPONSE_FORMAT_JSON_SCHEMA = 'json_schema';

    /**
     * The system will utilize scale tier credits until they are exhausted.
     *
     * @see TraceAttributes::GEN_AI_OPENAI_REQUEST_SERVICE_TIER
     */
    public const GEN_AI_OPENAI_REQUEST_SERVICE_TIER_AUTO = 'auto';

    /**
     * The system will utilize the default scale tier.
     *
     * @see TraceAttributes::GEN_AI_OPENAI_REQUEST_SERVICE_TIER
     */
    public const GEN_AI_OPENAI_REQUEST_SERVICE_TIER_DEFAULT = 'default';

    /**
     * Chat completion operation such as [OpenAI Chat API](https://platform.openai.com/docs/api-reference/chat)
     *
     * @see TraceAttributes::GEN_AI_OPERATION_NAME
     */
    public const GEN_AI_OPERATION_NAME_CHAT = 'chat';

    /**
     * Text completions operation such as [OpenAI Completions API (Legacy)](https://platform.openai.com/docs/api-reference/completions)
     *
     * @see TraceAttributes::GEN_AI_OPERATION_NAME
     */
    public const GEN_AI_OPERATION_NAME_TEXT_COMPLETION = 'text_completion';

    /**
     * Embeddings operation such as [OpenAI Create embeddings API](https://platform.openai.com/docs/api-reference/embeddings/create)
     *
     * @see TraceAttributes::GEN_AI_OPERATION_NAME
     */
    public const GEN_AI_OPERATION_NAME_EMBEDDINGS = 'embeddings';

    /**
     * OpenAI
     *
     * @see TraceAttributes::GEN_AI_SYSTEM
     */
    public const GEN_AI_SYSTEM_OPENAI = 'openai';

    /**
     * Vertex AI
     *
     * @see TraceAttributes::GEN_AI_SYSTEM
     */
    public const GEN_AI_SYSTEM_VERTEX_AI = 'vertex_ai';

    /**
     * Gemini
     *
     * @see TraceAttributes::GEN_AI_SYSTEM
     */
    public const GEN_AI_SYSTEM_GEMINI = 'gemini';

    /**
     * Anthropic
     *
     * @see TraceAttributes::GEN_AI_SYSTEM
     */
    public const GEN_AI_SYSTEM_ANTHROPIC = 'anthropic';

    /**
     * Cohere
     *
     * @see TraceAttributes::GEN_AI_SYSTEM
     */
    public const GEN_AI_SYSTEM_COHERE = 'cohere';

    /**
     * Azure AI Inference
     *
     * @see TraceAttributes::GEN_AI_SYSTEM
     */
    public const GEN_AI_SYSTEM_AZ_AI_INFERENCE = 'az.ai.inference';

    /**
     * Azure OpenAI
     *
     * @see TraceAttributes::GEN_AI_SYSTEM
     */
    public const GEN_AI_SYSTEM_AZ_AI_OPENAI = 'az.ai.openai';

    /**
     * IBM Watsonx AI
     *
     * @see TraceAttributes::GEN_AI_SYSTEM
     */
    public const GEN_AI_SYSTEM_IBM_WATSONX_AI = 'ibm.watsonx.ai';

    /**
     * AWS Bedrock
     *
     * @see TraceAttributes::GEN_AI_SYSTEM
     */
    public const GEN_AI_SYSTEM_AWS_BEDROCK = 'aws.bedrock';

    /**
     * Perplexity
     *
     * @see TraceAttributes::GEN_AI_SYSTEM
     */
    public const GEN_AI_SYSTEM_PERPLEXITY = 'perplexity';

    /**
     * xAI
     *
     * @see TraceAttributes::GEN_AI_SYSTEM
     */
    public const GEN_AI_SYSTEM_XAI = 'xai';

    /**
     * DeepSeek
     *
     * @see TraceAttributes::GEN_AI_SYSTEM
     */
    public const GEN_AI_SYSTEM_DEEPSEEK = 'deepseek';

    /**
     * Groq
     *
     * @see TraceAttributes::GEN_AI_SYSTEM
     */
    public const GEN_AI_SYSTEM_GROQ = 'groq';

    /**
     * Mistral AI
     *
     * @see TraceAttributes::GEN_AI_SYSTEM
     */
    public const GEN_AI_SYSTEM_MISTRAL_AI = 'mistral_ai';

    /**
     * Input tokens (prompt, input, etc.)
     *
     * @see TraceAttributes::GEN_AI_TOKEN_TYPE
     */
    public const GEN_AI_TOKEN_TYPE_INPUT = 'input';

    /**
     * Output tokens (completion, response, etc.)
     *
     * @see TraceAttributes::GEN_AI_TOKEN_TYPE
     */
    public const GEN_AI_TOKEN_TYPE_COMPLETION = 'output';

    /**
     * Africa
     *
     * @see TraceAttributes::GEO_CONTINENT_CODE
     */
    public const GEO_CONTINENT_CODE_AF = 'AF';

    /**
     * Antarctica
     *
     * @see TraceAttributes::GEO_CONTINENT_CODE
     */
    public const GEO_CONTINENT_CODE_AN = 'AN';

    /**
     * Asia
     *
     * @see TraceAttributes::GEO_CONTINENT_CODE
     */
    public const GEO_CONTINENT_CODE_AS = 'AS';

    /**
     * Europe
     *
     * @see TraceAttributes::GEO_CONTINENT_CODE
     */
    public const GEO_CONTINENT_CODE_EU = 'EU';

    /**
     * North America
     *
     * @see TraceAttributes::GEO_CONTINENT_CODE
     */
    public const GEO_CONTINENT_CODE_NA = 'NA';

    /**
     * Oceania
     *
     * @see TraceAttributes::GEO_CONTINENT_CODE
     */
    public const GEO_CONTINENT_CODE_OC = 'OC';

    /**
     * South America
     *
     * @see TraceAttributes::GEO_CONTINENT_CODE
     */
    public const GEO_CONTINENT_CODE_SA = 'SA';

    /**
     * Memory allocated from the heap that is reserved for stack space, whether or not it is currently in-use.
     *
     * @see TraceAttributes::GO_MEMORY_TYPE
     */
    public const GO_MEMORY_TYPE_STACK = 'stack';

    /**
     * Memory used by the Go runtime, excluding other categories of memory usage described in this enumeration.
     *
     * @see TraceAttributes::GO_MEMORY_TYPE
     */
    public const GO_MEMORY_TYPE_OTHER = 'other';

    /**
     * GraphQL query
     *
     * @see TraceAttributes::GRAPHQL_OPERATION_TYPE
     */
    public const GRAPHQL_OPERATION_TYPE_QUERY = 'query';

    /**
     * GraphQL mutation
     *
     * @see TraceAttributes::GRAPHQL_OPERATION_TYPE
     */
    public const GRAPHQL_OPERATION_TYPE_MUTATION = 'mutation';

    /**
     * GraphQL subscription
     *
     * @see TraceAttributes::GRAPHQL_OPERATION_TYPE
     */
    public const GRAPHQL_OPERATION_TYPE_SUBSCRIPTION = 'subscription';

    /**
     * AMD64
     *
     * @see TraceAttributes::HOST_ARCH
     */
    public const HOST_ARCH_AMD64 = 'amd64';

    /**
     * ARM32
     *
     * @see TraceAttributes::HOST_ARCH
     */
    public const HOST_ARCH_ARM32 = 'arm32';

    /**
     * ARM64
     *
     * @see TraceAttributes::HOST_ARCH
     */
    public const HOST_ARCH_ARM64 = 'arm64';

    /**
     * Itanium
     *
     * @see TraceAttributes::HOST_ARCH
     */
    public const HOST_ARCH_IA64 = 'ia64';

    /**
     * 32-bit PowerPC
     *
     * @see TraceAttributes::HOST_ARCH
     */
    public const HOST_ARCH_PPC32 = 'ppc32';

    /**
     * 64-bit PowerPC
     *
     * @see TraceAttributes::HOST_ARCH
     */
    public const HOST_ARCH_PPC64 = 'ppc64';

    /**
     * IBM z/Architecture
     *
     * @see TraceAttributes::HOST_ARCH
     */
    public const HOST_ARCH_S390X = 's390x';

    /**
     * 32-bit x86
     *
     * @see TraceAttributes::HOST_ARCH
     */
    public const HOST_ARCH_X86 = 'x86';

    /**
     * active state.
     *
     * @see TraceAttributes::HTTP_CONNECTION_STATE
     */
    public const HTTP_CONNECTION_STATE_ACTIVE = 'active';

    /**
     * idle state.
     *
     * @see TraceAttributes::HTTP_CONNECTION_STATE
     */
    public const HTTP_CONNECTION_STATE_IDLE = 'idle';

    /**
     * HTTP/1.0
     *
     * @see TraceAttributes::HTTP_FLAVOR
     */
    public const HTTP_FLAVOR_HTTP_1_0 = '1.0';

    /**
     * HTTP/1.1
     *
     * @see TraceAttributes::HTTP_FLAVOR
     */
    public const HTTP_FLAVOR_HTTP_1_1 = '1.1';

    /**
     * HTTP/2
     *
     * @see TraceAttributes::HTTP_FLAVOR
     */
    public const HTTP_FLAVOR_HTTP_2_0 = '2.0';

    /**
     * HTTP/3
     *
     * @see TraceAttributes::HTTP_FLAVOR
     */
    public const HTTP_FLAVOR_HTTP_3_0 = '3.0';

    /**
     * SPDY protocol.
     *
     * @see TraceAttributes::HTTP_FLAVOR
     */
    public const HTTP_FLAVOR_SPDY = 'SPDY';

    /**
     * QUIC protocol.
     *
     * @see TraceAttributes::HTTP_FLAVOR
     */
    public const HTTP_FLAVOR_QUIC = 'QUIC';

    /**
     * CONNECT method.
     *
     * @see TraceAttributes::HTTP_REQUEST_METHOD
     */
    public const HTTP_REQUEST_METHOD_CONNECT = 'CONNECT';

    /**
     * DELETE method.
     *
     * @see TraceAttributes::HTTP_REQUEST_METHOD
     */
    public const HTTP_REQUEST_METHOD_DELETE = 'DELETE';

    /**
     * GET method.
     *
     * @see TraceAttributes::HTTP_REQUEST_METHOD
     */
    public const HTTP_REQUEST_METHOD_GET = 'GET';

    /**
     * HEAD method.
     *
     * @see TraceAttributes::HTTP_REQUEST_METHOD
     */
    public const HTTP_REQUEST_METHOD_HEAD = 'HEAD';

    /**
     * OPTIONS method.
     *
     * @see TraceAttributes::HTTP_REQUEST_METHOD
     */
    public const HTTP_REQUEST_METHOD_OPTIONS = 'OPTIONS';

    /**
     * PATCH method.
     *
     * @see TraceAttributes::HTTP_REQUEST_METHOD
     */
    public const HTTP_REQUEST_METHOD_PATCH = 'PATCH';

    /**
     * POST method.
     *
     * @see TraceAttributes::HTTP_REQUEST_METHOD
     */
    public const HTTP_REQUEST_METHOD_POST = 'POST';

    /**
     * PUT method.
     *
     * @see TraceAttributes::HTTP_REQUEST_METHOD
     */
    public const HTTP_REQUEST_METHOD_PUT = 'PUT';

    /**
     * TRACE method.
     *
     * @see TraceAttributes::HTTP_REQUEST_METHOD
     */
    public const HTTP_REQUEST_METHOD_TRACE = 'TRACE';

    /**
     * Any HTTP method that the instrumentation has no prior knowledge of.
     *
     * @see TraceAttributes::HTTP_REQUEST_METHOD
     */
    public const HTTP_REQUEST_METHOD_OTHER = '_OTHER';

    /**
     * The app has become `active`. Associated with UIKit notification `applicationDidBecomeActive`.
     *
     * @see TraceAttributes::IOS_STATE
     */
    public const IOS_STATE_ACTIVE = 'active';

    /**
     * The app is now `inactive`. Associated with UIKit notification `applicationWillResignActive`.
     *
     * @see TraceAttributes::IOS_STATE
     */
    public const IOS_STATE_INACTIVE = 'inactive';

    /**
     * The app is now in the background. This value is associated with UIKit notification `applicationDidEnterBackground`.
     *
     * @see TraceAttributes::IOS_STATE
     */
    public const IOS_STATE_BACKGROUND = 'background';

    /**
     * The app is now in the foreground. This value is associated with UIKit notification `applicationWillEnterForeground`.
     *
     * @see TraceAttributes::IOS_STATE
     */
    public const IOS_STATE_FOREGROUND = 'foreground';

    /**
     * The app is about to terminate. Associated with UIKit notification `applicationWillTerminate`.
     *
     * @see TraceAttributes::IOS_STATE
     */
    public const IOS_STATE_TERMINATE = 'terminate';

    /**
     * Heap memory.
     *
     * @see TraceAttributes::JVM_MEMORY_TYPE
     */
    public const JVM_MEMORY_TYPE_HEAP = 'heap';

    /**
     * Non-heap memory
     *
     * @see TraceAttributes::JVM_MEMORY_TYPE
     */
    public const JVM_MEMORY_TYPE_NON_HEAP = 'non_heap';

    /**
     * A thread that has not yet started is in this state.
     *
     * @see TraceAttributes::JVM_THREAD_STATE
     */
    public const JVM_THREAD_STATE_NEW = 'new';

    /**
     * A thread executing in the Java virtual machine is in this state.
     *
     * @see TraceAttributes::JVM_THREAD_STATE
     */
    public const JVM_THREAD_STATE_RUNNABLE = 'runnable';

    /**
     * A thread that is blocked waiting for a monitor lock is in this state.
     *
     * @see TraceAttributes::JVM_THREAD_STATE
     */
    public const JVM_THREAD_STATE_BLOCKED = 'blocked';

    /**
     * A thread that is waiting indefinitely for another thread to perform a particular action is in this state.
     *
     * @see TraceAttributes::JVM_THREAD_STATE
     */
    public const JVM_THREAD_STATE_WAITING = 'waiting';

    /**
     * A thread that is waiting for another thread to perform an action for up to a specified waiting time is in this state.
     *
     * @see TraceAttributes::JVM_THREAD_STATE
     */
    public const JVM_THREAD_STATE_TIMED_WAITING = 'timed_waiting';

    /**
     * A thread that has exited is in this state.
     *
     * @see TraceAttributes::JVM_THREAD_STATE
     */
    public const JVM_THREAD_STATE_TERMINATED = 'terminated';

    /**
     * Active namespace phase as described by [K8s API](https://pkg.go.dev/k8s.io/api@v0.31.3/core/v1#NamespacePhase)
     *
     * @see TraceAttributes::K8S_NAMESPACE_PHASE
     */
    public const K8S_NAMESPACE_PHASE_ACTIVE = 'active';

    /**
     * Terminating namespace phase as described by [K8s API](https://pkg.go.dev/k8s.io/api@v0.31.3/core/v1#NamespacePhase)
     *
     * @see TraceAttributes::K8S_NAMESPACE_PHASE
     */
    public const K8S_NAMESPACE_PHASE_TERMINATING = 'terminating';

    /**
     * A [persistentVolumeClaim](https://v1-30.docs.kubernetes.io/docs/concepts/storage/volumes/#persistentvolumeclaim) volume
     *
     * @see TraceAttributes::K8S_VOLUME_TYPE
     */
    public const K8S_VOLUME_TYPE_PERSISTENT_VOLUME_CLAIM = 'persistentVolumeClaim';

    /**
     * A [configMap](https://v1-30.docs.kubernetes.io/docs/concepts/storage/volumes/#configmap) volume
     *
     * @see TraceAttributes::K8S_VOLUME_TYPE
     */
    public const K8S_VOLUME_TYPE_CONFIG_MAP = 'configMap';

    /**
     * A [downwardAPI](https://v1-30.docs.kubernetes.io/docs/concepts/storage/volumes/#downwardapi) volume
     *
     * @see TraceAttributes::K8S_VOLUME_TYPE
     */
    public const K8S_VOLUME_TYPE_DOWNWARD_API = 'downwardAPI';

    /**
     * An [emptyDir](https://v1-30.docs.kubernetes.io/docs/concepts/storage/volumes/#emptydir) volume
     *
     * @see TraceAttributes::K8S_VOLUME_TYPE
     */
    public const K8S_VOLUME_TYPE_EMPTY_DIR = 'emptyDir';

    /**
     * A [secret](https://v1-30.docs.kubernetes.io/docs/concepts/storage/volumes/#secret) volume
     *
     * @see TraceAttributes::K8S_VOLUME_TYPE
     */
    public const K8S_VOLUME_TYPE_SECRET = 'secret';

    /**
     * A [local](https://v1-30.docs.kubernetes.io/docs/concepts/storage/volumes/#local) volume
     *
     * @see TraceAttributes::K8S_VOLUME_TYPE
     */
    public const K8S_VOLUME_TYPE_LOCAL = 'local';

    /**
     * reclaimable
     *
     * @see TraceAttributes::LINUX_MEMORY_SLAB_STATE
     */
    public const LINUX_MEMORY_SLAB_STATE_RECLAIMABLE = 'reclaimable';

    /**
     * unreclaimable
     *
     * @see TraceAttributes::LINUX_MEMORY_SLAB_STATE
     */
    public const LINUX_MEMORY_SLAB_STATE_UNRECLAIMABLE = 'unreclaimable';

    /**
     * Logs from stdout stream
     *
     * @see TraceAttributes::LOG_IOSTREAM
     */
    public const LOG_IOSTREAM_STDOUT = 'stdout';

    /**
     * Events from stderr stream
     *
     * @see TraceAttributes::LOG_IOSTREAM
     */
    public const LOG_IOSTREAM_STDERR = 'stderr';

    /**
     * sent
     *
     * @see TraceAttributes::MESSAGE_TYPE
     */
    public const MESSAGE_TYPE_SENT = 'SENT';

    /**
     * received
     *
     * @see TraceAttributes::MESSAGE_TYPE
     */
    public const MESSAGE_TYPE_RECEIVED = 'RECEIVED';

    /**
     * A message is created. "Create" spans always refer to a single message and are used to provide a unique creation context for messages in batch sending scenarios.
     *
     * @see TraceAttributes::MESSAGING_OPERATION_TYPE
     */
    public const MESSAGING_OPERATION_TYPE_CREATE = 'create';

    /**
     * One or more messages are provided for sending to an intermediary. If a single message is sent, the context of the "Send" span can be used as the creation context and no "Create" span needs to be created.
     *
     * @see TraceAttributes::MESSAGING_OPERATION_TYPE
     */
    public const MESSAGING_OPERATION_TYPE_SEND = 'send';

    /**
     * One or more messages are requested by a consumer. This operation refers to pull-based scenarios, where consumers explicitly call methods of messaging SDKs to receive messages.
     *
     * @see TraceAttributes::MESSAGING_OPERATION_TYPE
     */
    public const MESSAGING_OPERATION_TYPE_RECEIVE = 'receive';

    /**
     * One or more messages are processed by a consumer.
     *
     * @see TraceAttributes::MESSAGING_OPERATION_TYPE
     */
    public const MESSAGING_OPERATION_TYPE_PROCESS = 'process';

    /**
     * One or more messages are settled.
     *
     * @see TraceAttributes::MESSAGING_OPERATION_TYPE
     */
    public const MESSAGING_OPERATION_TYPE_SETTLE = 'settle';

    /**
     * Deprecated. Use `process` instead.
     *
     * @see TraceAttributes::MESSAGING_OPERATION_TYPE
     * @deprecated Replaced by `process`.
     */
    public const MESSAGING_OPERATION_TYPE_DELIVER = 'deliver';

    /**
     * Deprecated. Use `send` instead.
     *
     * @see TraceAttributes::MESSAGING_OPERATION_TYPE
     * @deprecated Replaced by `send`.
     */
    public const MESSAGING_OPERATION_TYPE_PUBLISH = 'publish';

    /**
     * Clustering consumption model
     *
     * @see TraceAttributes::MESSAGING_ROCKETMQ_CONSUMPTION_MODEL
     */
    public const MESSAGING_ROCKETMQ_CONSUMPTION_MODEL_CLUSTERING = 'clustering';

    /**
     * Broadcasting consumption model
     *
     * @see TraceAttributes::MESSAGING_ROCKETMQ_CONSUMPTION_MODEL
     */
    public const MESSAGING_ROCKETMQ_CONSUMPTION_MODEL_BROADCASTING = 'broadcasting';

    /**
     * Normal message
     *
     * @see TraceAttributes::MESSAGING_ROCKETMQ_MESSAGE_TYPE
     */
    public const MESSAGING_ROCKETMQ_MESSAGE_TYPE_NORMAL = 'normal';

    /**
     * FIFO message
     *
     * @see TraceAttributes::MESSAGING_ROCKETMQ_MESSAGE_TYPE
     */
    public const MESSAGING_ROCKETMQ_MESSAGE_TYPE_FIFO = 'fifo';

    /**
     * Delay message
     *
     * @see TraceAttributes::MESSAGING_ROCKETMQ_MESSAGE_TYPE
     */
    public const MESSAGING_ROCKETMQ_MESSAGE_TYPE_DELAY = 'delay';

    /**
     * Transaction message
     *
     * @see TraceAttributes::MESSAGING_ROCKETMQ_MESSAGE_TYPE
     */
    public const MESSAGING_ROCKETMQ_MESSAGE_TYPE_TRANSACTION = 'transaction';

    /**
     * Message is completed
     *
     * @see TraceAttributes::MESSAGING_SERVICEBUS_DISPOSITION_STATUS
     */
    public const MESSAGING_SERVICEBUS_DISPOSITION_STATUS_COMPLETE = 'complete';

    /**
     * Message is abandoned
     *
     * @see TraceAttributes::MESSAGING_SERVICEBUS_DISPOSITION_STATUS
     */
    public const MESSAGING_SERVICEBUS_DISPOSITION_STATUS_ABANDON = 'abandon';

    /**
     * Message is sent to dead letter queue
     *
     * @see TraceAttributes::MESSAGING_SERVICEBUS_DISPOSITION_STATUS
     */
    public const MESSAGING_SERVICEBUS_DISPOSITION_STATUS_DEAD_LETTER = 'dead_letter';

    /**
     * Message is deferred
     *
     * @see TraceAttributes::MESSAGING_SERVICEBUS_DISPOSITION_STATUS
     */
    public const MESSAGING_SERVICEBUS_DISPOSITION_STATUS_DEFER = 'defer';

    /**
     * Apache ActiveMQ
     *
     * @see TraceAttributes::MESSAGING_SYSTEM
     */
    public const MESSAGING_SYSTEM_ACTIVEMQ = 'activemq';

    /**
     * Amazon Simple Queue Service (SQS)
     *
     * @see TraceAttributes::MESSAGING_SYSTEM
     */
    public const MESSAGING_SYSTEM_AWS_SQS = 'aws_sqs';

    /**
     * Azure Event Grid
     *
     * @see TraceAttributes::MESSAGING_SYSTEM
     */
    public const MESSAGING_SYSTEM_EVENTGRID = 'eventgrid';

    /**
     * Azure Event Hubs
     *
     * @see TraceAttributes::MESSAGING_SYSTEM
     */
    public const MESSAGING_SYSTEM_EVENTHUBS = 'eventhubs';

    /**
     * Azure Service Bus
     *
     * @see TraceAttributes::MESSAGING_SYSTEM
     */
    public const MESSAGING_SYSTEM_SERVICEBUS = 'servicebus';

    /**
     * Google Cloud Pub/Sub
     *
     * @see TraceAttributes::MESSAGING_SYSTEM
     */
    public const MESSAGING_SYSTEM_GCP_PUBSUB = 'gcp_pubsub';

    /**
     * Java Message Service
     *
     * @see TraceAttributes::MESSAGING_SYSTEM
     */
    public const MESSAGING_SYSTEM_JMS = 'jms';

    /**
     * Apache Kafka
     *
     * @see TraceAttributes::MESSAGING_SYSTEM
     */
    public const MESSAGING_SYSTEM_KAFKA = 'kafka';

    /**
     * RabbitMQ
     *
     * @see TraceAttributes::MESSAGING_SYSTEM
     */
    public const MESSAGING_SYSTEM_RABBITMQ = 'rabbitmq';

    /**
     * Apache RocketMQ
     *
     * @see TraceAttributes::MESSAGING_SYSTEM
     */
    public const MESSAGING_SYSTEM_ROCKETMQ = 'rocketmq';

    /**
     * Apache Pulsar
     *
     * @see TraceAttributes::MESSAGING_SYSTEM
     */
    public const MESSAGING_SYSTEM_PULSAR = 'pulsar';

    /**
     * IPv4 address
     *
     * @see TraceAttributes::NET_SOCK_FAMILY
     */
    public const NET_SOCK_FAMILY_INET = 'inet';

    /**
     * IPv6 address
     *
     * @see TraceAttributes::NET_SOCK_FAMILY
     */
    public const NET_SOCK_FAMILY_INET6 = 'inet6';

    /**
     * Unix domain socket path
     *
     * @see TraceAttributes::NET_SOCK_FAMILY
     */
    public const NET_SOCK_FAMILY_UNIX = 'unix';

    /**
     * ip_tcp
     *
     * @see TraceAttributes::NET_TRANSPORT
     */
    public const NET_TRANSPORT_IP_TCP = 'ip_tcp';

    /**
     * ip_udp
     *
     * @see TraceAttributes::NET_TRANSPORT
     */
    public const NET_TRANSPORT_IP_UDP = 'ip_udp';

    /**
     * Named or anonymous pipe.
     *
     * @see TraceAttributes::NET_TRANSPORT
     */
    public const NET_TRANSPORT_PIPE = 'pipe';

    /**
     * In-process communication.
     *
     * @see TraceAttributes::NET_TRANSPORT
     */
    public const NET_TRANSPORT_INPROC = 'inproc';

    /**
     * Something else (non IP-based).
     *
     * @see TraceAttributes::NET_TRANSPORT
     */
    public const NET_TRANSPORT_OTHER = 'other';

    /**
     * closed
     *
     * @see TraceAttributes::NETWORK_CONNECTION_STATE
     */
    public const NETWORK_CONNECTION_STATE_CLOSED = 'closed';

    /**
     * close_wait
     *
     * @see TraceAttributes::NETWORK_CONNECTION_STATE
     */
    public const NETWORK_CONNECTION_STATE_CLOSE_WAIT = 'close_wait';

    /**
     * closing
     *
     * @see TraceAttributes::NETWORK_CONNECTION_STATE
     */
    public const NETWORK_CONNECTION_STATE_CLOSING = 'closing';

    /**
     * established
     *
     * @see TraceAttributes::NETWORK_CONNECTION_STATE
     */
    public const NETWORK_CONNECTION_STATE_ESTABLISHED = 'established';

    /**
     * fin_wait_1
     *
     * @see TraceAttributes::NETWORK_CONNECTION_STATE
     */
    public const NETWORK_CONNECTION_STATE_FIN_WAIT_1 = 'fin_wait_1';

    /**
     * fin_wait_2
     *
     * @see TraceAttributes::NETWORK_CONNECTION_STATE
     */
    public const NETWORK_CONNECTION_STATE_FIN_WAIT_2 = 'fin_wait_2';

    /**
     * last_ack
     *
     * @see TraceAttributes::NETWORK_CONNECTION_STATE
     */
    public const NETWORK_CONNECTION_STATE_LAST_ACK = 'last_ack';

    /**
     * listen
     *
     * @see TraceAttributes::NETWORK_CONNECTION_STATE
     */
    public const NETWORK_CONNECTION_STATE_LISTEN = 'listen';

    /**
     * syn_received
     *
     * @see TraceAttributes::NETWORK_CONNECTION_STATE
     */
    public const NETWORK_CONNECTION_STATE_SYN_RECEIVED = 'syn_received';

    /**
     * syn_sent
     *
     * @see TraceAttributes::NETWORK_CONNECTION_STATE
     */
    public const NETWORK_CONNECTION_STATE_SYN_SENT = 'syn_sent';

    /**
     * time_wait
     *
     * @see TraceAttributes::NETWORK_CONNECTION_STATE
     */
    public const NETWORK_CONNECTION_STATE_TIME_WAIT = 'time_wait';

    /**
     * GPRS
     *
     * @see TraceAttributes::NETWORK_CONNECTION_SUBTYPE
     */
    public const NETWORK_CONNECTION_SUBTYPE_GPRS = 'gprs';

    /**
     * EDGE
     *
     * @see TraceAttributes::NETWORK_CONNECTION_SUBTYPE
     */
    public const NETWORK_CONNECTION_SUBTYPE_EDGE = 'edge';

    /**
     * UMTS
     *
     * @see TraceAttributes::NETWORK_CONNECTION_SUBTYPE
     */
    public const NETWORK_CONNECTION_SUBTYPE_UMTS = 'umts';

    /**
     * CDMA
     *
     * @see TraceAttributes::NETWORK_CONNECTION_SUBTYPE
     */
    public const NETWORK_CONNECTION_SUBTYPE_CDMA = 'cdma';

    /**
     * EVDO Rel. 0
     *
     * @see TraceAttributes::NETWORK_CONNECTION_SUBTYPE
     */
    public const NETWORK_CONNECTION_SUBTYPE_EVDO_0 = 'evdo_0';

    /**
     * EVDO Rev. A
     *
     * @see TraceAttributes::NETWORK_CONNECTION_SUBTYPE
     */
    public const NETWORK_CONNECTION_SUBTYPE_EVDO_A = 'evdo_a';

    /**
     * CDMA2000 1XRTT
     *
     * @see TraceAttributes::NETWORK_CONNECTION_SUBTYPE
     */
    public const NETWORK_CONNECTION_SUBTYPE_CDMA2000_1XRTT = 'cdma2000_1xrtt';

    /**
     * HSDPA
     *
     * @see TraceAttributes::NETWORK_CONNECTION_SUBTYPE
     */
    public const NETWORK_CONNECTION_SUBTYPE_HSDPA = 'hsdpa';

    /**
     * HSUPA
     *
     * @see TraceAttributes::NETWORK_CONNECTION_SUBTYPE
     */
    public const NETWORK_CONNECTION_SUBTYPE_HSUPA = 'hsupa';

    /**
     * HSPA
     *
     * @see TraceAttributes::NETWORK_CONNECTION_SUBTYPE
     */
    public const NETWORK_CONNECTION_SUBTYPE_HSPA = 'hspa';

    /**
     * IDEN
     *
     * @see TraceAttributes::NETWORK_CONNECTION_SUBTYPE
     */
    public const NETWORK_CONNECTION_SUBTYPE_IDEN = 'iden';

    /**
     * EVDO Rev. B
     *
     * @see TraceAttributes::NETWORK_CONNECTION_SUBTYPE
     */
    public const NETWORK_CONNECTION_SUBTYPE_EVDO_B = 'evdo_b';

    /**
     * LTE
     *
     * @see TraceAttributes::NETWORK_CONNECTION_SUBTYPE
     */
    public const NETWORK_CONNECTION_SUBTYPE_LTE = 'lte';

    /**
     * EHRPD
     *
     * @see TraceAttributes::NETWORK_CONNECTION_SUBTYPE
     */
    public const NETWORK_CONNECTION_SUBTYPE_EHRPD = 'ehrpd';

    /**
     * HSPAP
     *
     * @see TraceAttributes::NETWORK_CONNECTION_SUBTYPE
     */
    public const NETWORK_CONNECTION_SUBTYPE_HSPAP = 'hspap';

    /**
     * GSM
     *
     * @see TraceAttributes::NETWORK_CONNECTION_SUBTYPE
     */
    public const NETWORK_CONNECTION_SUBTYPE_GSM = 'gsm';

    /**
     * TD-SCDMA
     *
     * @see TraceAttributes::NETWORK_CONNECTION_SUBTYPE
     */
    public const NETWORK_CONNECTION_SUBTYPE_TD_SCDMA = 'td_scdma';

    /**
     * IWLAN
     *
     * @see TraceAttributes::NETWORK_CONNECTION_SUBTYPE
     */
    public const NETWORK_CONNECTION_SUBTYPE_IWLAN = 'iwlan';

    /**
     * 5G NR (New Radio)
     *
     * @see TraceAttributes::NETWORK_CONNECTION_SUBTYPE
     */
    public const NETWORK_CONNECTION_SUBTYPE_NR = 'nr';

    /**
     * 5G NRNSA (New Radio Non-Standalone)
     *
     * @see TraceAttributes::NETWORK_CONNECTION_SUBTYPE
     */
    public const NETWORK_CONNECTION_SUBTYPE_NRNSA = 'nrnsa';

    /**
     * LTE CA
     *
     * @see TraceAttributes::NETWORK_CONNECTION_SUBTYPE
     */
    public const NETWORK_CONNECTION_SUBTYPE_LTE_CA = 'lte_ca';

    /**
     * wifi
     *
     * @see TraceAttributes::NETWORK_CONNECTION_TYPE
     */
    public const NETWORK_CONNECTION_TYPE_WIFI = 'wifi';

    /**
     * wired
     *
     * @see TraceAttributes::NETWORK_CONNECTION_TYPE
     */
    public const NETWORK_CONNECTION_TYPE_WIRED = 'wired';

    /**
     * cell
     *
     * @see TraceAttributes::NETWORK_CONNECTION_TYPE
     */
    public const NETWORK_CONNECTION_TYPE_CELL = 'cell';

    /**
     * unavailable
     *
     * @see TraceAttributes::NETWORK_CONNECTION_TYPE
     */
    public const NETWORK_CONNECTION_TYPE_UNAVAILABLE = 'unavailable';

    /**
     * unknown
     *
     * @see TraceAttributes::NETWORK_CONNECTION_TYPE
     */
    public const NETWORK_CONNECTION_TYPE_UNKNOWN = 'unknown';

    /**
     * transmit
     *
     * @see TraceAttributes::NETWORK_IO_DIRECTION
     */
    public const NETWORK_IO_DIRECTION_TRANSMIT = 'transmit';

    /**
     * receive
     *
     * @see TraceAttributes::NETWORK_IO_DIRECTION
     */
    public const NETWORK_IO_DIRECTION_RECEIVE = 'receive';

    /**
     * TCP
     *
     * @see TraceAttributes::NETWORK_TRANSPORT
     */
    public const NETWORK_TRANSPORT_TCP = 'tcp';

    /**
     * UDP
     *
     * @see TraceAttributes::NETWORK_TRANSPORT
     */
    public const NETWORK_TRANSPORT_UDP = 'udp';

    /**
     * Named or anonymous pipe.
     *
     * @see TraceAttributes::NETWORK_TRANSPORT
     */
    public const NETWORK_TRANSPORT_PIPE = 'pipe';

    /**
     * Unix domain socket
     *
     * @see TraceAttributes::NETWORK_TRANSPORT
     */
    public const NETWORK_TRANSPORT_UNIX = 'unix';

    /**
     * QUIC
     *
     * @see TraceAttributes::NETWORK_TRANSPORT
     */
    public const NETWORK_TRANSPORT_QUIC = 'quic';

    /**
     * IPv4
     *
     * @see TraceAttributes::NETWORK_TYPE
     */
    public const NETWORK_TYPE_IPV4 = 'ipv4';

    /**
     * IPv6
     *
     * @see TraceAttributes::NETWORK_TYPE
     */
    public const NETWORK_TYPE_IPV6 = 'ipv6';

    /**
     * The parent Span depends on the child Span in some capacity
     *
     * @see TraceAttributes::OPENTRACING_REF_TYPE
     */
    public const OPENTRACING_REF_TYPE_CHILD_OF = 'child_of';

    /**
     * The parent Span doesn't depend in any way on the result of the child Span
     *
     * @see TraceAttributes::OPENTRACING_REF_TYPE
     */
    public const OPENTRACING_REF_TYPE_FOLLOWS_FROM = 'follows_from';

    /**
     * Microsoft Windows
     *
     * @see TraceAttributes::OS_TYPE
     */
    public const OS_TYPE_WINDOWS = 'windows';

    /**
     * Linux
     *
     * @see TraceAttributes::OS_TYPE
     */
    public const OS_TYPE_LINUX = 'linux';

    /**
     * Apple Darwin
     *
     * @see TraceAttributes::OS_TYPE
     */
    public const OS_TYPE_DARWIN = 'darwin';

    /**
     * FreeBSD
     *
     * @see TraceAttributes::OS_TYPE
     */
    public const OS_TYPE_FREEBSD = 'freebsd';

    /**
     * NetBSD
     *
     * @see TraceAttributes::OS_TYPE
     */
    public const OS_TYPE_NETBSD = 'netbsd';

    /**
     * OpenBSD
     *
     * @see TraceAttributes::OS_TYPE
     */
    public const OS_TYPE_OPENBSD = 'openbsd';

    /**
     * DragonFly BSD
     *
     * @see TraceAttributes::OS_TYPE
     */
    public const OS_TYPE_DRAGONFLYBSD = 'dragonflybsd';

    /**
     * HP-UX (Hewlett Packard Unix)
     *
     * @see TraceAttributes::OS_TYPE
     */
    public const OS_TYPE_HPUX = 'hpux';

    /**
     * AIX (Advanced Interactive eXecutive)
     *
     * @see TraceAttributes::OS_TYPE
     */
    public const OS_TYPE_AIX = 'aix';

    /**
     * SunOS, Oracle Solaris
     *
     * @see TraceAttributes::OS_TYPE
     */
    public const OS_TYPE_SOLARIS = 'solaris';

    /**
     * IBM z/OS
     *
     * @see TraceAttributes::OS_TYPE
     */
    public const OS_TYPE_Z_OS = 'z_os';

    /**
     * The operation has been validated by an Application developer or Operator to have completed successfully.
     *
     * @see TraceAttributes::OTEL_STATUS_CODE
     */
    public const OTEL_STATUS_CODE_OK = 'OK';

    /**
     * The operation contains an error.
     *
     * @see TraceAttributes::OTEL_STATUS_CODE
     */
    public const OTEL_STATUS_CODE_ERROR = 'ERROR';

    /**
     * idle
     *
     * @see TraceAttributes::STATE
     */
    public const STATE_IDLE = 'idle';

    /**
     * used
     *
     * @see TraceAttributes::STATE
     */
    public const STATE_USED = 'used';

    /**
     * voluntary
     *
     * @see TraceAttributes::PROCESS_CONTEXT_SWITCH_TYPE
     */
    public const PROCESS_CONTEXT_SWITCH_TYPE_VOLUNTARY = 'voluntary';

    /**
     * involuntary
     *
     * @see TraceAttributes::PROCESS_CONTEXT_SWITCH_TYPE
     */
    public const PROCESS_CONTEXT_SWITCH_TYPE_INVOLUNTARY = 'involuntary';

    /**
     * system
     *
     * @see TraceAttributes::PROCESS_CPU_STATE
     */
    public const PROCESS_CPU_STATE_SYSTEM = 'system';

    /**
     * user
     *
     * @see TraceAttributes::PROCESS_CPU_STATE
     */
    public const PROCESS_CPU_STATE_USER = 'user';

    /**
     * wait
     *
     * @see TraceAttributes::PROCESS_CPU_STATE
     */
    public const PROCESS_CPU_STATE_WAIT = 'wait';

    /**
     * major
     *
     * @see TraceAttributes::PROCESS_PAGING_FAULT_TYPE
     */
    public const PROCESS_PAGING_FAULT_TYPE_MAJOR = 'major';

    /**
     * minor
     *
     * @see TraceAttributes::PROCESS_PAGING_FAULT_TYPE
     */
    public const PROCESS_PAGING_FAULT_TYPE_MINOR = 'minor';

    /**
     * cancelled
     *
     * @see TraceAttributes::RPC_CONNECT_RPC_ERROR_CODE
     */
    public const RPC_CONNECT_RPC_ERROR_CODE_CANCELLED = 'cancelled';

    /**
     * unknown
     *
     * @see TraceAttributes::RPC_CONNECT_RPC_ERROR_CODE
     */
    public const RPC_CONNECT_RPC_ERROR_CODE_UNKNOWN = 'unknown';

    /**
     * invalid_argument
     *
     * @see TraceAttributes::RPC_CONNECT_RPC_ERROR_CODE
     */
    public const RPC_CONNECT_RPC_ERROR_CODE_INVALID_ARGUMENT = 'invalid_argument';

    /**
     * deadline_exceeded
     *
     * @see TraceAttributes::RPC_CONNECT_RPC_ERROR_CODE
     */
    public const RPC_CONNECT_RPC_ERROR_CODE_DEADLINE_EXCEEDED = 'deadline_exceeded';

    /**
     * not_found
     *
     * @see TraceAttributes::RPC_CONNECT_RPC_ERROR_CODE
     */
    public const RPC_CONNECT_RPC_ERROR_CODE_NOT_FOUND = 'not_found';

    /**
     * already_exists
     *
     * @see TraceAttributes::RPC_CONNECT_RPC_ERROR_CODE
     */
    public const RPC_CONNECT_RPC_ERROR_CODE_ALREADY_EXISTS = 'already_exists';

    /**
     * permission_denied
     *
     * @see TraceAttributes::RPC_CONNECT_RPC_ERROR_CODE
     */
    public const RPC_CONNECT_RPC_ERROR_CODE_PERMISSION_DENIED = 'permission_denied';

    /**
     * resource_exhausted
     *
     * @see TraceAttributes::RPC_CONNECT_RPC_ERROR_CODE
     */
    public const RPC_CONNECT_RPC_ERROR_CODE_RESOURCE_EXHAUSTED = 'resource_exhausted';

    /**
     * failed_precondition
     *
     * @see TraceAttributes::RPC_CONNECT_RPC_ERROR_CODE
     */
    public const RPC_CONNECT_RPC_ERROR_CODE_FAILED_PRECONDITION = 'failed_precondition';

    /**
     * aborted
     *
     * @see TraceAttributes::RPC_CONNECT_RPC_ERROR_CODE
     */
    public const RPC_CONNECT_RPC_ERROR_CODE_ABORTED = 'aborted';

    /**
     * out_of_range
     *
     * @see TraceAttributes::RPC_CONNECT_RPC_ERROR_CODE
     */
    public const RPC_CONNECT_RPC_ERROR_CODE_OUT_OF_RANGE = 'out_of_range';

    /**
     * unimplemented
     *
     * @see TraceAttributes::RPC_CONNECT_RPC_ERROR_CODE
     */
    public const RPC_CONNECT_RPC_ERROR_CODE_UNIMPLEMENTED = 'unimplemented';

    /**
     * internal
     *
     * @see TraceAttributes::RPC_CONNECT_RPC_ERROR_CODE
     */
    public const RPC_CONNECT_RPC_ERROR_CODE_INTERNAL = 'internal';

    /**
     * unavailable
     *
     * @see TraceAttributes::RPC_CONNECT_RPC_ERROR_CODE
     */
    public const RPC_CONNECT_RPC_ERROR_CODE_UNAVAILABLE = 'unavailable';

    /**
     * data_loss
     *
     * @see TraceAttributes::RPC_CONNECT_RPC_ERROR_CODE
     */
    public const RPC_CONNECT_RPC_ERROR_CODE_DATA_LOSS = 'data_loss';

    /**
     * unauthenticated
     *
     * @see TraceAttributes::RPC_CONNECT_RPC_ERROR_CODE
     */
    public const RPC_CONNECT_RPC_ERROR_CODE_UNAUTHENTICATED = 'unauthenticated';

    /**
     * OK
     *
     * @see TraceAttributes::RPC_GRPC_STATUS_CODE
     */
    public const RPC_GRPC_STATUS_CODE_OK = '0';

    /**
     * CANCELLED
     *
     * @see TraceAttributes::RPC_GRPC_STATUS_CODE
     */
    public const RPC_GRPC_STATUS_CODE_CANCELLED = '1';

    /**
     * UNKNOWN
     *
     * @see TraceAttributes::RPC_GRPC_STATUS_CODE
     */
    public const RPC_GRPC_STATUS_CODE_UNKNOWN = '2';

    /**
     * INVALID_ARGUMENT
     *
     * @see TraceAttributes::RPC_GRPC_STATUS_CODE
     */
    public const RPC_GRPC_STATUS_CODE_INVALID_ARGUMENT = '3';

    /**
     * DEADLINE_EXCEEDED
     *
     * @see TraceAttributes::RPC_GRPC_STATUS_CODE
     */
    public const RPC_GRPC_STATUS_CODE_DEADLINE_EXCEEDED = '4';

    /**
     * NOT_FOUND
     *
     * @see TraceAttributes::RPC_GRPC_STATUS_CODE
     */
    public const RPC_GRPC_STATUS_CODE_NOT_FOUND = '5';

    /**
     * ALREADY_EXISTS
     *
     * @see TraceAttributes::RPC_GRPC_STATUS_CODE
     */
    public const RPC_GRPC_STATUS_CODE_ALREADY_EXISTS = '6';

    /**
     * PERMISSION_DENIED
     *
     * @see TraceAttributes::RPC_GRPC_STATUS_CODE
     */
    public const RPC_GRPC_STATUS_CODE_PERMISSION_DENIED = '7';

    /**
     * RESOURCE_EXHAUSTED
     *
     * @see TraceAttributes::RPC_GRPC_STATUS_CODE
     */
    public const RPC_GRPC_STATUS_CODE_RESOURCE_EXHAUSTED = '8';

    /**
     * FAILED_PRECONDITION
     *
     * @see TraceAttributes::RPC_GRPC_STATUS_CODE
     */
    public const RPC_GRPC_STATUS_CODE_FAILED_PRECONDITION = '9';

    /**
     * ABORTED
     *
     * @see TraceAttributes::RPC_GRPC_STATUS_CODE
     */
    public const RPC_GRPC_STATUS_CODE_ABORTED = '10';

    /**
     * OUT_OF_RANGE
     *
     * @see TraceAttributes::RPC_GRPC_STATUS_CODE
     */
    public const RPC_GRPC_STATUS_CODE_OUT_OF_RANGE = '11';

    /**
     * UNIMPLEMENTED
     *
     * @see TraceAttributes::RPC_GRPC_STATUS_CODE
     */
    public const RPC_GRPC_STATUS_CODE_UNIMPLEMENTED = '12';

    /**
     * INTERNAL
     *
     * @see TraceAttributes::RPC_GRPC_STATUS_CODE
     */
    public const RPC_GRPC_STATUS_CODE_INTERNAL = '13';

    /**
     * UNAVAILABLE
     *
     * @see TraceAttributes::RPC_GRPC_STATUS_CODE
     */
    public const RPC_GRPC_STATUS_CODE_UNAVAILABLE = '14';

    /**
     * DATA_LOSS
     *
     * @see TraceAttributes::RPC_GRPC_STATUS_CODE
     */
    public const RPC_GRPC_STATUS_CODE_DATA_LOSS = '15';

    /**
     * UNAUTHENTICATED
     *
     * @see TraceAttributes::RPC_GRPC_STATUS_CODE
     */
    public const RPC_GRPC_STATUS_CODE_UNAUTHENTICATED = '16';

    /**
     * sent
     *
     * @see TraceAttributes::RPC_MESSAGE_TYPE
     */
    public const RPC_MESSAGE_TYPE_SENT = 'SENT';

    /**
     * received
     *
     * @see TraceAttributes::RPC_MESSAGE_TYPE
     */
    public const RPC_MESSAGE_TYPE_RECEIVED = 'RECEIVED';

    /**
     * gRPC
     *
     * @see TraceAttributes::RPC_SYSTEM
     */
    public const RPC_SYSTEM_GRPC = 'grpc';

    /**
     * Java RMI
     *
     * @see TraceAttributes::RPC_SYSTEM
     */
    public const RPC_SYSTEM_JAVA_RMI = 'java_rmi';

    /**
     * .NET WCF
     *
     * @see TraceAttributes::RPC_SYSTEM
     */
    public const RPC_SYSTEM_DOTNET_WCF = 'dotnet_wcf';

    /**
     * Apache Dubbo
     *
     * @see TraceAttributes::RPC_SYSTEM
     */
    public const RPC_SYSTEM_APACHE_DUBBO = 'apache_dubbo';

    /**
     * Connect RPC
     *
     * @see TraceAttributes::RPC_SYSTEM
     */
    public const RPC_SYSTEM_CONNECT_RPC = 'connect_rpc';

    /**
     * The connection was closed normally.
     *
     * @see TraceAttributes::SIGNALR_CONNECTION_STATUS
     */
    public const SIGNALR_CONNECTION_STATUS_NORMAL_CLOSURE = 'normal_closure';

    /**
     * The connection was closed due to a timeout.
     *
     * @see TraceAttributes::SIGNALR_CONNECTION_STATUS
     */
    public const SIGNALR_CONNECTION_STATUS_TIMEOUT = 'timeout';

    /**
     * The connection was closed because the app is shutting down.
     *
     * @see TraceAttributes::SIGNALR_CONNECTION_STATUS
     */
    public const SIGNALR_CONNECTION_STATUS_APP_SHUTDOWN = 'app_shutdown';

    /**
     * ServerSentEvents protocol
     *
     * @see TraceAttributes::SIGNALR_TRANSPORT
     */
    public const SIGNALR_TRANSPORT_SERVER_SENT_EVENTS = 'server_sent_events';

    /**
     * LongPolling protocol
     *
     * @see TraceAttributes::SIGNALR_TRANSPORT
     */
    public const SIGNALR_TRANSPORT_LONG_POLLING = 'long_polling';

    /**
     * WebSockets protocol
     *
     * @see TraceAttributes::SIGNALR_TRANSPORT
     */
    public const SIGNALR_TRANSPORT_WEB_SOCKETS = 'web_sockets';

    /**
     * user
     *
     * @see TraceAttributes::SYSTEM_CPU_STATE
     */
    public const SYSTEM_CPU_STATE_USER = 'user';

    /**
     * system
     *
     * @see TraceAttributes::SYSTEM_CPU_STATE
     */
    public const SYSTEM_CPU_STATE_SYSTEM = 'system';

    /**
     * nice
     *
     * @see TraceAttributes::SYSTEM_CPU_STATE
     */
    public const SYSTEM_CPU_STATE_NICE = 'nice';

    /**
     * idle
     *
     * @see TraceAttributes::SYSTEM_CPU_STATE
     */
    public const SYSTEM_CPU_STATE_IDLE = 'idle';

    /**
     * iowait
     *
     * @see TraceAttributes::SYSTEM_CPU_STATE
     */
    public const SYSTEM_CPU_STATE_IOWAIT = 'iowait';

    /**
     * interrupt
     *
     * @see TraceAttributes::SYSTEM_CPU_STATE
     */
    public const SYSTEM_CPU_STATE_INTERRUPT = 'interrupt';

    /**
     * steal
     *
     * @see TraceAttributes::SYSTEM_CPU_STATE
     */
    public const SYSTEM_CPU_STATE_STEAL = 'steal';

    /**
     * used
     *
     * @see TraceAttributes::SYSTEM_FILESYSTEM_STATE
     */
    public const SYSTEM_FILESYSTEM_STATE_USED = 'used';

    /**
     * free
     *
     * @see TraceAttributes::SYSTEM_FILESYSTEM_STATE
     */
    public const SYSTEM_FILESYSTEM_STATE_FREE = 'free';

    /**
     * reserved
     *
     * @see TraceAttributes::SYSTEM_FILESYSTEM_STATE
     */
    public const SYSTEM_FILESYSTEM_STATE_RESERVED = 'reserved';

    /**
     * fat32
     *
     * @see TraceAttributes::SYSTEM_FILESYSTEM_TYPE
     */
    public const SYSTEM_FILESYSTEM_TYPE_FAT32 = 'fat32';

    /**
     * exfat
     *
     * @see TraceAttributes::SYSTEM_FILESYSTEM_TYPE
     */
    public const SYSTEM_FILESYSTEM_TYPE_EXFAT = 'exfat';

    /**
     * ntfs
     *
     * @see TraceAttributes::SYSTEM_FILESYSTEM_TYPE
     */
    public const SYSTEM_FILESYSTEM_TYPE_NTFS = 'ntfs';

    /**
     * refs
     *
     * @see TraceAttributes::SYSTEM_FILESYSTEM_TYPE
     */
    public const SYSTEM_FILESYSTEM_TYPE_REFS = 'refs';

    /**
     * hfsplus
     *
     * @see TraceAttributes::SYSTEM_FILESYSTEM_TYPE
     */
    public const SYSTEM_FILESYSTEM_TYPE_HFSPLUS = 'hfsplus';

    /**
     * ext4
     *
     * @see TraceAttributes::SYSTEM_FILESYSTEM_TYPE
     */
    public const SYSTEM_FILESYSTEM_TYPE_EXT4 = 'ext4';

    /**
     * used
     *
     * @see TraceAttributes::SYSTEM_MEMORY_STATE
     */
    public const SYSTEM_MEMORY_STATE_USED = 'used';

    /**
     * free
     *
     * @see TraceAttributes::SYSTEM_MEMORY_STATE
     */
    public const SYSTEM_MEMORY_STATE_FREE = 'free';

    /**
     * shared
     *
     * @see TraceAttributes::SYSTEM_MEMORY_STATE
     * @deprecated Removed, report shared memory usage with `metric.system.memory.shared` metric
     */
    public const SYSTEM_MEMORY_STATE_SHARED = 'shared';

    /**
     * buffers
     *
     * @see TraceAttributes::SYSTEM_MEMORY_STATE
     */
    public const SYSTEM_MEMORY_STATE_BUFFERS = 'buffers';

    /**
     * cached
     *
     * @see TraceAttributes::SYSTEM_MEMORY_STATE
     */
    public const SYSTEM_MEMORY_STATE_CACHED = 'cached';

    /**
     * close
     *
     * @see TraceAttributes::SYSTEM_NETWORK_STATE
     */
    public const SYSTEM_NETWORK_STATE_CLOSE = 'close';

    /**
     * close_wait
     *
     * @see TraceAttributes::SYSTEM_NETWORK_STATE
     */
    public const SYSTEM_NETWORK_STATE_CLOSE_WAIT = 'close_wait';

    /**
     * closing
     *
     * @see TraceAttributes::SYSTEM_NETWORK_STATE
     */
    public const SYSTEM_NETWORK_STATE_CLOSING = 'closing';

    /**
     * delete
     *
     * @see TraceAttributes::SYSTEM_NETWORK_STATE
     */
    public const SYSTEM_NETWORK_STATE_DELETE = 'delete';

    /**
     * established
     *
     * @see TraceAttributes::SYSTEM_NETWORK_STATE
     */
    public const SYSTEM_NETWORK_STATE_ESTABLISHED = 'established';

    /**
     * fin_wait_1
     *
     * @see TraceAttributes::SYSTEM_NETWORK_STATE
     */
    public const SYSTEM_NETWORK_STATE_FIN_WAIT_1 = 'fin_wait_1';

    /**
     * fin_wait_2
     *
     * @see TraceAttributes::SYSTEM_NETWORK_STATE
     */
    public const SYSTEM_NETWORK_STATE_FIN_WAIT_2 = 'fin_wait_2';

    /**
     * last_ack
     *
     * @see TraceAttributes::SYSTEM_NETWORK_STATE
     */
    public const SYSTEM_NETWORK_STATE_LAST_ACK = 'last_ack';

    /**
     * listen
     *
     * @see TraceAttributes::SYSTEM_NETWORK_STATE
     */
    public const SYSTEM_NETWORK_STATE_LISTEN = 'listen';

    /**
     * syn_recv
     *
     * @see TraceAttributes::SYSTEM_NETWORK_STATE
     */
    public const SYSTEM_NETWORK_STATE_SYN_RECV = 'syn_recv';

    /**
     * syn_sent
     *
     * @see TraceAttributes::SYSTEM_NETWORK_STATE
     */
    public const SYSTEM_NETWORK_STATE_SYN_SENT = 'syn_sent';

    /**
     * time_wait
     *
     * @see TraceAttributes::SYSTEM_NETWORK_STATE
     */
    public const SYSTEM_NETWORK_STATE_TIME_WAIT = 'time_wait';

    /**
     * in
     *
     * @see TraceAttributes::SYSTEM_PAGING_DIRECTION
     */
    public const SYSTEM_PAGING_DIRECTION_IN = 'in';

    /**
     * out
     *
     * @see TraceAttributes::SYSTEM_PAGING_DIRECTION
     */
    public const SYSTEM_PAGING_DIRECTION_OUT = 'out';

    /**
     * used
     *
     * @see TraceAttributes::SYSTEM_PAGING_STATE
     */
    public const SYSTEM_PAGING_STATE_USED = 'used';

    /**
     * free
     *
     * @see TraceAttributes::SYSTEM_PAGING_STATE
     */
    public const SYSTEM_PAGING_STATE_FREE = 'free';

    /**
     * major
     *
     * @see TraceAttributes::SYSTEM_PAGING_TYPE
     */
    public const SYSTEM_PAGING_TYPE_MAJOR = 'major';

    /**
     * minor
     *
     * @see TraceAttributes::SYSTEM_PAGING_TYPE
     */
    public const SYSTEM_PAGING_TYPE_MINOR = 'minor';

    /**
     * running
     *
     * @see TraceAttributes::SYSTEM_PROCESS_STATUS
     */
    public const SYSTEM_PROCESS_STATUS_RUNNING = 'running';

    /**
     * sleeping
     *
     * @see TraceAttributes::SYSTEM_PROCESS_STATUS
     */
    public const SYSTEM_PROCESS_STATUS_SLEEPING = 'sleeping';

    /**
     * stopped
     *
     * @see TraceAttributes::SYSTEM_PROCESS_STATUS
     */
    public const SYSTEM_PROCESS_STATUS_STOPPED = 'stopped';

    /**
     * defunct
     *
     * @see TraceAttributes::SYSTEM_PROCESS_STATUS
     */
    public const SYSTEM_PROCESS_STATUS_DEFUNCT = 'defunct';

    /**
     * running
     *
     * @see TraceAttributes::SYSTEM_PROCESSES_STATUS
     */
    public const SYSTEM_PROCESSES_STATUS_RUNNING = 'running';

    /**
     * sleeping
     *
     * @see TraceAttributes::SYSTEM_PROCESSES_STATUS
     */
    public const SYSTEM_PROCESSES_STATUS_SLEEPING = 'sleeping';

    /**
     * stopped
     *
     * @see TraceAttributes::SYSTEM_PROCESSES_STATUS
     */
    public const SYSTEM_PROCESSES_STATUS_STOPPED = 'stopped';

    /**
     * defunct
     *
     * @see TraceAttributes::SYSTEM_PROCESSES_STATUS
     */
    public const SYSTEM_PROCESSES_STATUS_DEFUNCT = 'defunct';

    /**
     * cpp
     *
     * @see TraceAttributes::TELEMETRY_SDK_LANGUAGE
     */
    public const TELEMETRY_SDK_LANGUAGE_CPP = 'cpp';

    /**
     * dotnet
     *
     * @see TraceAttributes::TELEMETRY_SDK_LANGUAGE
     */
    public const TELEMETRY_SDK_LANGUAGE_DOTNET = 'dotnet';

    /**
     * erlang
     *
     * @see TraceAttributes::TELEMETRY_SDK_LANGUAGE
     */
    public const TELEMETRY_SDK_LANGUAGE_ERLANG = 'erlang';

    /**
     * go
     *
     * @see TraceAttributes::TELEMETRY_SDK_LANGUAGE
     */
    public const TELEMETRY_SDK_LANGUAGE_GO = 'go';

    /**
     * java
     *
     * @see TraceAttributes::TELEMETRY_SDK_LANGUAGE
     */
    public const TELEMETRY_SDK_LANGUAGE_JAVA = 'java';

    /**
     * nodejs
     *
     * @see TraceAttributes::TELEMETRY_SDK_LANGUAGE
     */
    public const TELEMETRY_SDK_LANGUAGE_NODEJS = 'nodejs';

    /**
     * php
     *
     * @see TraceAttributes::TELEMETRY_SDK_LANGUAGE
     */
    public const TELEMETRY_SDK_LANGUAGE_PHP = 'php';

    /**
     * python
     *
     * @see TraceAttributes::TELEMETRY_SDK_LANGUAGE
     */
    public const TELEMETRY_SDK_LANGUAGE_PYTHON = 'python';

    /**
     * ruby
     *
     * @see TraceAttributes::TELEMETRY_SDK_LANGUAGE
     */
    public const TELEMETRY_SDK_LANGUAGE_RUBY = 'ruby';

    /**
     * rust
     *
     * @see TraceAttributes::TELEMETRY_SDK_LANGUAGE
     */
    public const TELEMETRY_SDK_LANGUAGE_RUST = 'rust';

    /**
     * swift
     *
     * @see TraceAttributes::TELEMETRY_SDK_LANGUAGE
     */
    public const TELEMETRY_SDK_LANGUAGE_SWIFT = 'swift';

    /**
     * webjs
     *
     * @see TraceAttributes::TELEMETRY_SDK_LANGUAGE
     */
    public const TELEMETRY_SDK_LANGUAGE_WEBJS = 'webjs';

    /**
     * pass
     *
     * @see TraceAttributes::TEST_CASE_RESULT_STATUS
     */
    public const TEST_CASE_RESULT_STATUS_PASS = 'pass';

    /**
     * fail
     *
     * @see TraceAttributes::TEST_CASE_RESULT_STATUS
     */
    public const TEST_CASE_RESULT_STATUS_FAIL = 'fail';

    /**
     * success
     *
     * @see TraceAttributes::TEST_SUITE_RUN_STATUS
     */
    public const TEST_SUITE_RUN_STATUS_SUCCESS = 'success';

    /**
     * failure
     *
     * @see TraceAttributes::TEST_SUITE_RUN_STATUS
     */
    public const TEST_SUITE_RUN_STATUS_FAILURE = 'failure';

    /**
     * skipped
     *
     * @see TraceAttributes::TEST_SUITE_RUN_STATUS
     */
    public const TEST_SUITE_RUN_STATUS_SKIPPED = 'skipped';

    /**
     * aborted
     *
     * @see TraceAttributes::TEST_SUITE_RUN_STATUS
     */
    public const TEST_SUITE_RUN_STATUS_ABORTED = 'aborted';

    /**
     * timed_out
     *
     * @see TraceAttributes::TEST_SUITE_RUN_STATUS
     */
    public const TEST_SUITE_RUN_STATUS_TIMED_OUT = 'timed_out';

    /**
     * in_progress
     *
     * @see TraceAttributes::TEST_SUITE_RUN_STATUS
     */
    public const TEST_SUITE_RUN_STATUS_IN_PROGRESS = 'in_progress';

    /**
     * ssl
     *
     * @see TraceAttributes::TLS_PROTOCOL_NAME
     */
    public const TLS_PROTOCOL_NAME_SSL = 'ssl';

    /**
     * tls
     *
     * @see TraceAttributes::TLS_PROTOCOL_NAME
     */
    public const TLS_PROTOCOL_NAME_TLS = 'tls';

    /**
     * Bot source.
     *
     * @see TraceAttributes::USER_AGENT_SYNTHETIC_TYPE
     */
    public const USER_AGENT_SYNTHETIC_TYPE_BOT = 'bot';

    /**
     * Synthetic test source.
     *
     * @see TraceAttributes::USER_AGENT_SYNTHETIC_TYPE
     */
    public const USER_AGENT_SYNTHETIC_TYPE_TEST = 'test';

    /**
     * Major (Mark Sweep Compact).
     *
     * @see TraceAttributes::V8JS_GC_TYPE
     */
    public const V8JS_GC_TYPE_MAJOR = 'major';

    /**
     * Minor (Scavenge).
     *
     * @see TraceAttributes::V8JS_GC_TYPE
     */
    public const V8JS_GC_TYPE_MINOR = 'minor';

    /**
     * Incremental (Incremental Marking).
     *
     * @see TraceAttributes::V8JS_GC_TYPE
     */
    public const V8JS_GC_TYPE_INCREMENTAL = 'incremental';

    /**
     * Weak Callbacks (Process Weak Callbacks).
     *
     * @see TraceAttributes::V8JS_GC_TYPE
     */
    public const V8JS_GC_TYPE_WEAKCB = 'weakcb';

    /**
     * New memory space.
     *
     * @see TraceAttributes::V8JS_HEAP_SPACE_NAME
     */
    public const V8JS_HEAP_SPACE_NAME_NEW_SPACE = 'new_space';

    /**
     * Old memory space.
     *
     * @see TraceAttributes::V8JS_HEAP_SPACE_NAME
     */
    public const V8JS_HEAP_SPACE_NAME_OLD_SPACE = 'old_space';

    /**
     * Code memory space.
     *
     * @see TraceAttributes::V8JS_HEAP_SPACE_NAME
     */
    public const V8JS_HEAP_SPACE_NAME_CODE_SPACE = 'code_space';

    /**
     * Map memory space.
     *
     * @see TraceAttributes::V8JS_HEAP_SPACE_NAME
     */
    public const V8JS_HEAP_SPACE_NAME_MAP_SPACE = 'map_space';

    /**
     * Large object memory space.
     *
     * @see TraceAttributes::V8JS_HEAP_SPACE_NAME
     */
    public const V8JS_HEAP_SPACE_NAME_LARGE_OBJECT_SPACE = 'large_object_space';

    /**
     * Open means the change is currently active and under review. It hasn't been merged into the target branch yet, and it's still possible to make changes or add comments.
     *
     * @see TraceAttributes::VCS_CHANGE_STATE
     */
    public const VCS_CHANGE_STATE_OPEN = 'open';

    /**
     * WIP (work-in-progress, draft) means the change is still in progress and not yet ready for a full review. It might still undergo significant changes.
     *
     * @see TraceAttributes::VCS_CHANGE_STATE
     */
    public const VCS_CHANGE_STATE_WIP = 'wip';

    /**
     * Closed means the merge request has been closed without merging. This can happen for various reasons, such as the changes being deemed unnecessary, the issue being resolved in another way, or the author deciding to withdraw the request.
     *
     * @see TraceAttributes::VCS_CHANGE_STATE
     */
    public const VCS_CHANGE_STATE_CLOSED = 'closed';

    /**
     * Merged indicates that the change has been successfully integrated into the target codebase.
     *
     * @see TraceAttributes::VCS_CHANGE_STATE
     */
    public const VCS_CHANGE_STATE_MERGED = 'merged';

    /**
     * How many lines were added.
     *
     * @see TraceAttributes::VCS_LINE_CHANGE_TYPE
     */
    public const VCS_LINE_CHANGE_TYPE_ADDED = 'added';

    /**
     * How many lines were removed.
     *
     * @see TraceAttributes::VCS_LINE_CHANGE_TYPE
     */
    public const VCS_LINE_CHANGE_TYPE_REMOVED = 'removed';

    /**
     * [branch](https://git-scm.com/docs/gitglossary#Documentation/gitglossary.txt-aiddefbranchabranch)
     *
     * @see TraceAttributes::VCS_REF_BASE_TYPE
     */
    public const VCS_REF_BASE_TYPE_BRANCH = 'branch';

    /**
     * [tag](https://git-scm.com/docs/gitglossary#Documentation/gitglossary.txt-aiddeftagatag)
     *
     * @see TraceAttributes::VCS_REF_BASE_TYPE
     */
    public const VCS_REF_BASE_TYPE_TAG = 'tag';

    /**
     * [branch](https://git-scm.com/docs/gitglossary#Documentation/gitglossary.txt-aiddefbranchabranch)
     *
     * @see TraceAttributes::VCS_REF_HEAD_TYPE
     */
    public const VCS_REF_HEAD_TYPE_BRANCH = 'branch';

    /**
     * [tag](https://git-scm.com/docs/gitglossary#Documentation/gitglossary.txt-aiddeftagatag)
     *
     * @see TraceAttributes::VCS_REF_HEAD_TYPE
     */
    public const VCS_REF_HEAD_TYPE_TAG = 'tag';

    /**
     * [branch](https://git-scm.com/docs/gitglossary#Documentation/gitglossary.txt-aiddefbranchabranch)
     *
     * @see TraceAttributes::VCS_REF_TYPE
     */
    public const VCS_REF_TYPE_BRANCH = 'branch';

    /**
     * [tag](https://git-scm.com/docs/gitglossary#Documentation/gitglossary.txt-aiddeftagatag)
     *
     * @see TraceAttributes::VCS_REF_TYPE
     */
    public const VCS_REF_TYPE_TAG = 'tag';

    /**
     * [branch](https://git-scm.com/docs/gitglossary#Documentation/gitglossary.txt-aiddefbranchabranch)
     *
     * @see TraceAttributes::VCS_REPOSITORY_REF_TYPE
     */
    public const VCS_REPOSITORY_REF_TYPE_BRANCH = 'branch';

    /**
     * [tag](https://git-scm.com/docs/gitglossary#Documentation/gitglossary.txt-aiddeftagatag)
     *
     * @see TraceAttributes::VCS_REPOSITORY_REF_TYPE
     */
    public const VCS_REPOSITORY_REF_TYPE_TAG = 'tag';

    /**
     * How many revisions the change is behind the target ref.
     *
     * @see TraceAttributes::VCS_REVISION_DELTA_DIRECTION
     */
    public const VCS_REVISION_DELTA_DIRECTION_BEHIND = 'behind';

    /**
     * How many revisions the change is ahead of the target ref.
     *
     * @see TraceAttributes::VCS_REVISION_DELTA_DIRECTION
     */
    public const VCS_REVISION_DELTA_DIRECTION_AHEAD = 'ahead';

}
