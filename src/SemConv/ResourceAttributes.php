<?php

// DO NOT EDIT, this is an Auto-generated file from script/semantic-convention/templates/Attributes.php.j2

declare(strict_types=1);

namespace OpenTelemetry\SemConv;

interface ResourceAttributes
{
    /**
     * The URL of the OpenTelemetry schema for these keys and values.
     */
    public const SCHEMA_URL = 'https://opentelemetry.io/schemas/1.12.0';

    /**
     * Array of brand name and version separated by a space.
     *
     * This value is intended to be taken from the UA client hints API (navigator.userAgentData.brands).
     *
     * @example  Not A;Brand 99
     * @example Chromium 99
     * @example Chrome 99
     */
    public const BROWSER_BRANDS = 'browser.brands';

    /**
     * The platform on which the browser is running.
     *
     * This value is intended to be taken from the UA client hints API (navigator.userAgentData.platform). If unavailable, the legacy `navigator.platform` API SHOULD NOT be used instead and this attribute SHOULD be left unset in order for the values to be consistent.
     * The list of possible values is defined in the W3C User-Agent Client Hints specification. Note that some (but not all) of these values can overlap with values in the os.type and os.name attributes. However, for consistency, the values in the `browser.platform` attribute should capture the exact value that the user agent provides.
     *
     * @example Windows
     * @example macOS
     * @example Android
     */
    public const BROWSER_PLATFORM = 'browser.platform';

    /**
     * Full user-agent string provided by the browser.
     *
     * The user-agent value SHOULD be provided only from browsers that do not have a mechanism to retrieve brands and platform individually from the User-Agent Client Hints API. To retrieve the value, the legacy `navigator.userAgent` API can be used.
     *
     * @example Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/95.0.4638.54 Safari/537.36
     */
    public const BROWSER_USER_AGENT = 'browser.user_agent';

    /**
     * Name of the cloud provider.
     */
    public const CLOUD_PROVIDER = 'cloud.provider';

    /**
     * The cloud account ID the resource is assigned to.
     *
     * @example 111111111111
     * @example opentelemetry
     */
    public const CLOUD_ACCOUNT_ID = 'cloud.account.id';

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
     * The Amazon Resource Name (ARN) of an ECS container instance.
     *
     * @example arn:aws:ecs:us-west-1:123456789123:container/32624152-9086-4f0e-acae-1a75b14fe4d9
     */
    public const AWS_ECS_CONTAINER_ARN = 'aws.ecs.container.arn';

    /**
     * The ARN of an ECS cluster.
     *
     * @example arn:aws:ecs:us-west-2:123456789123:cluster/my-cluster
     */
    public const AWS_ECS_CLUSTER_ARN = 'aws.ecs.cluster.arn';

    /**
     * The launch type for an ECS task.
     */
    public const AWS_ECS_LAUNCHTYPE = 'aws.ecs.launchtype';

    /**
     * The ARN of an ECS task definition.
     *
     * @example arn:aws:ecs:us-west-1:123456789123:task/10838bed-421f-43ef-870a-f43feacbbb5b
     */
    public const AWS_ECS_TASK_ARN = 'aws.ecs.task.arn';

    /**
     * The task definition family this task definition is a member of.
     *
     * @example opentelemetry-family
     */
    public const AWS_ECS_TASK_FAMILY = 'aws.ecs.task.family';

    /**
     * The revision for this task definition.
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
     * The name(s) of the AWS log group(s) an application is writing to.
     *
     * Multiple log groups must be supported for cases like multi-container applications, where a single application has sidecar containers, and each write to their own log group.
     *
     * @example /aws/lambda/my-function
     * @example opentelemetry-service
     */
    public const AWS_LOG_GROUP_NAMES = 'aws.log.group.names';

    /**
     * The Amazon Resource Name(s) (ARN) of the AWS log group(s).
     *
     * See the log group ARN format documentation.
     *
     * @example arn:aws:logs:us-west-1:123456789012:log-group:/aws/my/group:*
     */
    public const AWS_LOG_GROUP_ARNS = 'aws.log.group.arns';

    /**
     * The name(s) of the AWS log stream(s) an application is writing to.
     *
     * @example logs/main/10838bed-421f-43ef-870a-f43feacbbb5b
     */
    public const AWS_LOG_STREAM_NAMES = 'aws.log.stream.names';

    /**
     * The ARN(s) of the AWS log stream(s).
     *
     * See the log stream ARN format documentation. One log group can contain several log streams, so these ARNs necessarily identify both a log group and a log stream.
     *
     * @example arn:aws:logs:us-west-1:123456789012:log-group:/aws/my/group:log-stream:logs/main/10838bed-421f-43ef-870a-f43feacbbb5b
     */
    public const AWS_LOG_STREAM_ARNS = 'aws.log.stream.arns';

    /**
     * Container name used by container runtime.
     *
     * @example opentelemetry-autoconf
     */
    public const CONTAINER_NAME = 'container.name';

    /**
     * Container ID. Usually a UUID, as for example used to identify Docker containers. The UUID might be abbreviated.
     *
     * @example a3bf90e006b2
     */
    public const CONTAINER_ID = 'container.id';

    /**
     * The container runtime managing this container.
     *
     * @example docker
     * @example containerd
     * @example rkt
     */
    public const CONTAINER_RUNTIME = 'container.runtime';

    /**
     * Name of the image the container was built on.
     *
     * @example gcr.io/opentelemetry/operator
     */
    public const CONTAINER_IMAGE_NAME = 'container.image.name';

    /**
     * Container image tag.
     *
     * @example 0.1
     */
    public const CONTAINER_IMAGE_TAG = 'container.image.tag';

    /**
     * Name of the deployment environment (aka deployment tier).
     *
     * @example staging
     * @example production
     */
    public const DEPLOYMENT_ENVIRONMENT = 'deployment.environment';

    /**
     * A unique identifier representing the device.
     *
     * The device identifier MUST only be defined using the values outlined below. This value is not an advertising identifier and MUST NOT be used as such. On iOS (Swift or Objective-C), this value MUST be equal to the vendor identifier. On Android (Java or Kotlin), this value MUST be equal to the Firebase Installation ID or a globally unique UUID which is persisted across sessions in your application. More information can be found here on best practices and exact implementation details. Caution should be taken when storing personal data or anything which can identify a user. GDPR and data protection laws may apply, ensure you do your own due diligence.
     *
     * @example 2ab2916d-a51f-4ac8-80ee-45ac31a28092
     */
    public const DEVICE_ID = 'device.id';

    /**
     * The model identifier for the device.
     *
     * It's recommended this value represents a machine readable version of the model identifier rather than the market or consumer-friendly name of the device.
     *
     * @example iPhone3,4
     * @example SM-G920F
     */
    public const DEVICE_MODEL_IDENTIFIER = 'device.model.identifier';

    /**
     * The marketing name for the device model.
     *
     * It's recommended this value represents a human readable version of the device model rather than a machine readable alternative.
     *
     * @example iPhone 6s Plus
     * @example Samsung Galaxy S6
     */
    public const DEVICE_MODEL_NAME = 'device.model.name';

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
     * a TracerProvider (see also the `faas.id` attribute).</li>
     * </ul>
     *
     * @example my-function
     * @example myazurefunctionapp/some-function-name
     */
    public const FAAS_NAME = 'faas.name';

    /**
     * The unique ID of the single function that this runtime instance executes.
     *
     * On some cloud providers, it may not be possible to determine the full ID at startup,
     * so consider setting `faas.id` as a span attribute instead.The exact value to use for `faas.id` depends on the cloud provider:<ul>
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
     * @example arn:aws:lambda:us-west-2:123456789012:function:my-function
     */
    public const FAAS_ID = 'faas.id';

    /**
     * The immutable version of the function being executed.
     *
     * Depending on the cloud provider and platform, use:<ul>
     * <li><strong>AWS Lambda:</strong> The function version
     * (an integer represented as a decimal string).</li>
     * <li><strong>Google Cloud Run:</strong> The revision
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
     * The amount of memory available to the serverless function in MiB.
     *
     * It's recommended to set this attribute since e.g. too little memory can easily stop a Java AWS Lambda function from working correctly. On AWS Lambda, the environment variable `AWS_LAMBDA_FUNCTION_MEMORY_SIZE` provides this information.
     *
     * @example 128
     */
    public const FAAS_MAX_MEMORY = 'faas.max_memory';

    /**
     * Unique host ID. For Cloud, this must be the instance_id assigned by the cloud provider.
     *
     * @example opentelemetry-test
     */
    public const HOST_ID = 'host.id';

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
     * The CPU architecture the host system is running on.
     */
    public const HOST_ARCH = 'host.arch';

    /**
     * Name of the VM image or OS install the host was instantiated from.
     *
     * @example infra-ami-eks-worker-node-7d4ec78312
     * @example CentOS-8-x86_64-1905
     */
    public const HOST_IMAGE_NAME = 'host.image.name';

    /**
     * VM image ID. For Cloud, this value is from the provider.
     *
     * @example ami-07b06b442921831e5
     */
    public const HOST_IMAGE_ID = 'host.image.id';

    /**
     * The version string of the VM image as defined in Version Attributes.
     *
     * @example 0.1
     */
    public const HOST_IMAGE_VERSION = 'host.image.version';

    /**
     * The name of the cluster.
     *
     * @example opentelemetry-cluster
     */
    public const K8S_CLUSTER_NAME = 'k8s.cluster.name';

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
     * The name of the namespace that the pod is running in.
     *
     * @example default
     */
    public const K8S_NAMESPACE_NAME = 'k8s.namespace.name';

    /**
     * The UID of the Pod.
     *
     * @example 275ecb36-5aa8-4c2a-9c47-d8bb681b9aff
     */
    public const K8S_POD_UID = 'k8s.pod.uid';

    /**
     * The name of the Pod.
     *
     * @example opentelemetry-pod-autoconf
     */
    public const K8S_POD_NAME = 'k8s.pod.name';

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
     * The UID of the ReplicaSet.
     *
     * @example 275ecb36-5aa8-4c2a-9c47-d8bb681b9aff
     */
    public const K8S_REPLICASET_UID = 'k8s.replicaset.uid';

    /**
     * The name of the ReplicaSet.
     *
     * @example opentelemetry
     */
    public const K8S_REPLICASET_NAME = 'k8s.replicaset.name';

    /**
     * The UID of the Deployment.
     *
     * @example 275ecb36-5aa8-4c2a-9c47-d8bb681b9aff
     */
    public const K8S_DEPLOYMENT_UID = 'k8s.deployment.uid';

    /**
     * The name of the Deployment.
     *
     * @example opentelemetry
     */
    public const K8S_DEPLOYMENT_NAME = 'k8s.deployment.name';

    /**
     * The UID of the StatefulSet.
     *
     * @example 275ecb36-5aa8-4c2a-9c47-d8bb681b9aff
     */
    public const K8S_STATEFULSET_UID = 'k8s.statefulset.uid';

    /**
     * The name of the StatefulSet.
     *
     * @example opentelemetry
     */
    public const K8S_STATEFULSET_NAME = 'k8s.statefulset.name';

    /**
     * The UID of the DaemonSet.
     *
     * @example 275ecb36-5aa8-4c2a-9c47-d8bb681b9aff
     */
    public const K8S_DAEMONSET_UID = 'k8s.daemonset.uid';

    /**
     * The name of the DaemonSet.
     *
     * @example opentelemetry
     */
    public const K8S_DAEMONSET_NAME = 'k8s.daemonset.name';

    /**
     * The UID of the Job.
     *
     * @example 275ecb36-5aa8-4c2a-9c47-d8bb681b9aff
     */
    public const K8S_JOB_UID = 'k8s.job.uid';

    /**
     * The name of the Job.
     *
     * @example opentelemetry
     */
    public const K8S_JOB_NAME = 'k8s.job.name';

    /**
     * The UID of the CronJob.
     *
     * @example 275ecb36-5aa8-4c2a-9c47-d8bb681b9aff
     */
    public const K8S_CRONJOB_UID = 'k8s.cronjob.uid';

    /**
     * The name of the CronJob.
     *
     * @example opentelemetry
     */
    public const K8S_CRONJOB_NAME = 'k8s.cronjob.name';

    /**
     * The operating system type.
     */
    public const OS_TYPE = 'os.type';

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
     * The version string of the operating system as defined in Version Attributes.
     *
     * @example 14.2.1
     * @example 18.04.1
     */
    public const OS_VERSION = 'os.version';

    /**
     * Process identifier (PID).
     *
     * @example 1234
     */
    public const PROCESS_PID = 'process.pid';

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
     * The command used to launch the process (i.e. the command name). On Linux based systems, can be set to the zeroth string in `proc/[pid]/cmdline`. On Windows, can be set to the first parameter extracted from `GetCommandLineW`.
     *
     * @example cmd/otelcol
     */
    public const PROCESS_COMMAND = 'process.command';

    /**
     * The full command used to launch the process as a single string representing the full command. On Windows, can be set to the result of `GetCommandLineW`. Do not set this if you have to assemble it just for monitoring; use `process.command_args` instead.
     *
     * @example C:\cmd\otecol --config="my directory\config.yaml"
     */
    public const PROCESS_COMMAND_LINE = 'process.command_line';

    /**
     * All the command arguments (including the command/executable itself) as received by the process. On Linux-based systems (and some other Unixoid systems supporting procfs), can be set according to the list of null-delimited strings extracted from `proc/[pid]/cmdline`. For libc-based executables, this would be the full argv vector passed to `main`.
     *
     * @example cmd/otecol
     * @example --config=config.yaml
     */
    public const PROCESS_COMMAND_ARGS = 'process.command_args';

    /**
     * The username of the user that owns the process.
     *
     * @example root
     */
    public const PROCESS_OWNER = 'process.owner';

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
     * An additional description about the runtime of the process, for example a specific vendor customization of the runtime environment.
     *
     * @example Eclipse OpenJ9 Eclipse OpenJ9 VM openj9-0.21.0
     */
    public const PROCESS_RUNTIME_DESCRIPTION = 'process.runtime.description';

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
     * The string ID of the service instance.
     *
     * MUST be unique for each instance of the same `service.namespace,service.name` pair (in other words `service.namespace,service.name,service.instance.id` triplet MUST be globally unique). The ID helps to distinguish instances of the same service that exist at the same time (e.g. instances of a horizontally scaled service). It is preferable for the ID to be persistent and stay the same for the lifetime of the service instance, however it is acceptable that the ID is ephemeral and changes during important lifetime events for the service (e.g. service restarts). If the service has no inherent unique ID that can be used as the value of this attribute it is recommended to generate a random Version 1 or Version 4 RFC 4122 UUID (services aiming for reproducible UUIDs may also use Version 5, see RFC 4122 for more recommendations).
     *
     * @example 627cc493-f310-47de-96bd-71410b7dec09
     */
    public const SERVICE_INSTANCE_ID = 'service.instance.id';

    /**
     * The version string of the service API or implementation.
     *
     * @example 2.0.0
     */
    public const SERVICE_VERSION = 'service.version';

    /**
     * The name of the telemetry SDK as defined above.
     *
     * @example opentelemetry
     */
    public const TELEMETRY_SDK_NAME = 'telemetry.sdk.name';

    /**
     * The language of the telemetry SDK.
     */
    public const TELEMETRY_SDK_LANGUAGE = 'telemetry.sdk.language';

    /**
     * The version string of the telemetry SDK.
     *
     * @example 1.2.3
     */
    public const TELEMETRY_SDK_VERSION = 'telemetry.sdk.version';

    /**
     * The version string of the auto instrumentation agent, if used.
     *
     * @example 1.2.3
     */
    public const TELEMETRY_AUTO_VERSION = 'telemetry.auto.version';

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
     * Additional description of the web engine (e.g. detailed version and edition information).
     *
     * @example WildFly Full 21.0.0.Final (WildFly Core 13.0.1.Final) - 2.2.2.Final
     */
    public const WEBENGINE_DESCRIPTION = 'webengine.description';
}
