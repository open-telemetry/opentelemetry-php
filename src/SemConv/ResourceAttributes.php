<?php

// DO NOT EDIT, this is an Auto-generated file from script/semantic-convention/templates/Attributes.php.j2

declare(strict_types=1);

namespace OpenTelemetry\SemConv;

interface ResourceAttributes
{
    /**
     * The URL of the OpenTelemetry schema for these keys and values.
     */
    public const SCHEMA_URL = 'https://opentelemetry.io/schemas/1.27.0';

    /**
     * Uniquely identifies the framework API revision offered by a version (`os.version`) of the android operating system. More information can be found here.
     *
     * @example 33
     * @example 32
     */
    public const ANDROID_OS_API_LEVEL = 'android.os.api_level';

    /**
     * The ARN of an ECS cluster.
     *
     * @example arn:aws:ecs:us-west-2:123456789123:cluster/my-cluster
     */
    public const AWS_ECS_CLUSTER_ARN = 'aws.ecs.cluster.arn';

    /**
     * The Amazon Resource Name (ARN) of an ECS container instance.
     *
     * @example arn:aws:ecs:us-west-1:123456789123:container/32624152-9086-4f0e-acae-1a75b14fe4d9
     */
    public const AWS_ECS_CONTAINER_ARN = 'aws.ecs.container.arn';

    /**
     * The launch type for an ECS task.
     */
    public const AWS_ECS_LAUNCHTYPE = 'aws.ecs.launchtype';

    /**
     * The ARN of a running ECS task.
     *
     * @example arn:aws:ecs:us-west-1:123456789123:task/10838bed-421f-43ef-870a-f43feacbbb5b
     * @example arn:aws:ecs:us-west-1:123456789123:task/my-cluster/task-id/23ebb8ac-c18f-46c6-8bbe-d55d0e37cfbd
     */
    public const AWS_ECS_TASK_ARN = 'aws.ecs.task.arn';

    /**
     * The family name of the ECS task definition used to create the ECS task.
     *
     * @example opentelemetry-family
     */
    public const AWS_ECS_TASK_FAMILY = 'aws.ecs.task.family';

    /**
     * The ID of a running ECS task. The ID MUST be extracted from `task.arn`.
     *
     * @example 10838bed-421f-43ef-870a-f43feacbbb5b
     * @example 23ebb8ac-c18f-46c6-8bbe-d55d0e37cfbd
     */
    public const AWS_ECS_TASK_ID = 'aws.ecs.task.id';

    /**
     * The revision for the task definition used to create the ECS task.
     *
     * @example 8
     * @example 26
     */
    public const AWS_ECS_TASK_REVISION = 'aws.ecs.task.revision';

    /**
     * The ARN of an EKS cluster.
     *
     * @example arn:aws:ecs:us-west-2:123456789123:cluster/my-cluster
     */
    public const AWS_EKS_CLUSTER_ARN = 'aws.eks.cluster.arn';

    /**
     * The Amazon Resource Name(s) (ARN) of the AWS log group(s).
     *
     * See the log group ARN format documentation.
     *
     * @example arn:aws:logs:us-west-1:123456789012:log-group:/aws/my/group:*
     */
    public const AWS_LOG_GROUP_ARNS = 'aws.log.group.arns';

    /**
     * The name(s) of the AWS log group(s) an application is writing to.
     *
     * Multiple log groups must be supported for cases like multi-container applications, where a single application has sidecar containers, and each write to their own log group.
     *
     * @example /aws/lambda/my-function
     * @example opentelemetry-service
     */
    public const AWS_LOG_GROUP_NAMES = 'aws.log.group.names';

    /**
     * The ARN(s) of the AWS log stream(s).
     *
     * See the log stream ARN format documentation. One log group can contain several log streams, so these ARNs necessarily identify both a log group and a log stream.
     *
     * @example arn:aws:logs:us-west-1:123456789012:log-group:/aws/my/group:log-stream:logs/main/10838bed-421f-43ef-870a-f43feacbbb5b
     */
    public const AWS_LOG_STREAM_ARNS = 'aws.log.stream.arns';

    /**
     * The name(s) of the AWS log stream(s) an application is writing to.
     *
     * @example logs/main/10838bed-421f-43ef-870a-f43feacbbb5b
     */
    public const AWS_LOG_STREAM_NAMES = 'aws.log.stream.names';

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
     * with the resolved function version, as the same runtime instance may be invocable with
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
     * The ID is assigned by the container runtime and can vary in different environments. Consider using `oci.manifest.digest` if it is important to identify the same image in different environments/runtimes.
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
     * Name of the deployment environment (aka deployment tier).
     *
     * `deployment.environment.name` does not affect the uniqueness constraints defined through
     * the `service.namespace`, `service.name` and `service.instance.id` resource attributes.
     * This implies that resources carrying the following attribute combinations MUST be
     * considered to be identifying the same service:<ul>
     * <li>`service.name=frontend`, `deployment.environment.name=production`</li>
     * <li>`service.name=frontend`, `deployment.environment.name=staging`.</li>
     * </ul>
     *
     * @example staging
     * @example production
     */
    public const DEPLOYMENT_ENVIRONMENT_NAME = 'deployment.environment.name';

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
     * Unique identifier for the application.
     *
     * @example 2daa2797-e42b-4624-9322-ec3f968df4da
     */
    public const HEROKU_APP_ID = 'heroku.app.id';

    /**
     * Commit hash for the current release.
     *
     * @example e6134959463efd8966b20e75b913cafe3f5ec
     */
    public const HEROKU_RELEASE_COMMIT = 'heroku.release.commit';

    /**
     * Time and date the release was created.
     *
     * @example 2022-10-23T18:00:42Z
     */
    public const HEROKU_RELEASE_CREATION_TIMESTAMP = 'heroku.release.creation_timestamp';

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
     */
    public const K8S_CONTAINER_RESTART_COUNT = 'k8s.container.restart_count';

    /**
     * Last terminated reason of the Container.
     *
     * @example Evicted
     * @example Error
     */
    public const K8S_CONTAINER_STATUS_LAST_TERMINATED_REASON = 'k8s.container.status.last_terminated_reason';

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
     * The digest of the OCI image manifest. For container images specifically is the digest by which the container image is known.
     *
     * Follows OCI Image Manifest Specification, and specifically the Digest property.
     * An example can be found in Example Image Manifest.
     *
     * @example sha256:e4ca62c0d62f3e886e684806dfe9d4e0cda60d54986898173c1083856cfda0f4
     */
    public const OCI_MANIFEST_DIGEST = 'oci.manifest.digest';

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
     * The name of the instrumentation scope - (`InstrumentationScope.Name` in OTLP).
     *
     * @example io.opentelemetry.contrib.mongodb
     */
    public const OTEL_SCOPE_NAME = 'otel.scope.name';

    /**
     * The version of the instrumentation scope - (`InstrumentationScope.Version` in OTLP).
     *
     * @example 1.0.0
     */
    public const OTEL_SCOPE_VERSION = 'otel.scope.version';

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
     * The name of the runtime of this process.
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
     * Full user-agent string provided by the browser.
     *
     * The user-agent value SHOULD be provided only from browsers that do not have a mechanism to retrieve brands and platform individually from the User-Agent Client Hints API. To retrieve the value, the legacy `navigator.userAgent` API can be used.
     *
     * @example Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/95.0.4638.54 Safari/537.36
     */
    public const USER_AGENT_ORIGINAL = 'user_agent.original';

    /**
     * Additional description of the web engine (e.g. detailed version and edition information).
     *
     * @example WildFly Full 21.0.0.Final (WildFly Core 13.0.1.Final) - 2.2.2.Final
     */
    public const WEBENGINE_DESCRIPTION = 'webengine.description';

    /**
     * The name of the web engine.
     *
     * @example WildFly
     */
    public const WEBENGINE_NAME = 'webengine.name';

    /**
     * The version of the web engine.
     *
     * @example 21.0.0
     */
    public const WEBENGINE_VERSION = 'webengine.version';

    /**
     * @deprecated Use USER_AGENT_ORIGINAL
     */
    public const BROWSER_USER_AGENT = 'browser.user_agent';

    /**
     * @deprecated Use CLOUD_RESOURCE_ID
     */
    public const FAAS_ID = 'faas.id';

    /**
     * @deprecated Use TELEMETRY_DISTRO_VERSION
     */
    public const TELEMETRY_AUTO_VERSION = 'telemetry.auto.version';

    /**
     * @deprecated Use CONTAINER_IMAGE_TAGS
     */
    public const CONTAINER_IMAGE_TAG = 'container.image.tag';

    /**
     * @deprecated Use `otel.scope.name`
     */
    public const OTEL_LIBRARY_NAME = 'otel.library.name';

    /**
     * @deprecated Use `otel.scope.version`
     */
    public const OTEL_LIBRARY_VERSION = 'otel.library.version';

    /**
     * @deprecated Use `deployment.environment.name`
     */
    public const DEPLOYMENT_ENVIRONMENT = 'deployment.environment';
}
