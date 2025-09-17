<?php

// DO NOT EDIT, this is archived and left for backward compatibility.

declare(strict_types=1);

namespace OpenTelemetry\SemConv;

/**
 * @deprecated Use {@see OpenTelemetry\SemConv\Attributes}\* or {@see OpenTelemetry\SemConv\Unstable\Attributes}\* instead.
 */
interface ResourceAttributes
{
    /**
     * The URL of the OpenTelemetry schema for these keys and values.
     */
    public const SCHEMA_URL = 'https://opentelemetry.io/schemas/1.32.0';

    /**
     * Uniquely identifies the framework API revision offered by a version (`os.version`) of the android operating system. More information can be found [here](https://developer.android.com/guide/topics/manifest/uses-sdk-element#ApiLevels).
     */
    public const ANDROID_OS_API_LEVEL = 'android.os.api_level';

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
     * The human readable name of the pipeline within a CI/CD system.
     */
    public const CICD_PIPELINE_NAME = 'cicd.pipeline.name';

    /**
     * The unique identifier of a pipeline run within a CI/CD system.
     */
    public const CICD_PIPELINE_RUN_ID = 'cicd.pipeline.run.id';

    /**
     * The [URL](https://wikipedia.org/wiki/URL) of the pipeline run, providing the complete address in order to locate and identify the pipeline run.
     */
    public const CICD_PIPELINE_RUN_URL_FULL = 'cicd.pipeline.run.url.full';

    /**
     * The unique identifier of a worker within a CICD system.
     */
    public const CICD_WORKER_ID = 'cicd.worker.id';

    /**
     * The name of a worker within a CICD system.
     */
    public const CICD_WORKER_NAME = 'cicd.worker.name';

    /**
     * The [URL](https://wikipedia.org/wiki/URL) of the worker, providing the complete address in order to locate and identify the worker.
     */
    public const CICD_WORKER_URL_FULL = 'cicd.worker.url.full';

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
     * The geographical region within a cloud provider. When associated with a resource, this attribute specifies the region where the resource operates. When calling services or APIs deployed on a cloud, this attribute identifies the region where the called destination is deployed.
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
     * The guid of the application.
     *
     * Application instrumentation should use the value from environment
     * variable `VCAP_APPLICATION.application_id`. This is the same value as
     * reported by `cf app <app-name> --guid`.
     */
    public const CLOUDFOUNDRY_APP_ID = 'cloudfoundry.app.id';

    /**
     * The name of the application.
     *
     * Application instrumentation should use the value from environment
     * variable `VCAP_APPLICATION.application_name`. This is the same value
     * as reported by `cf apps`.
     */
    public const CLOUDFOUNDRY_APP_NAME = 'cloudfoundry.app.name';

    /**
     * The guid of the CloudFoundry org the application is running in.
     *
     * Application instrumentation should use the value from environment
     * variable `VCAP_APPLICATION.org_id`. This is the same value as
     * reported by `cf org <org-name> --guid`.
     */
    public const CLOUDFOUNDRY_ORG_ID = 'cloudfoundry.org.id';

    /**
     * The name of the CloudFoundry organization the app is running in.
     *
     * Application instrumentation should use the value from environment
     * variable `VCAP_APPLICATION.org_name`. This is the same value as
     * reported by `cf orgs`.
     */
    public const CLOUDFOUNDRY_ORG_NAME = 'cloudfoundry.org.name';

    /**
     * The UID identifying the process.
     *
     * Application instrumentation should use the value from environment
     * variable `VCAP_APPLICATION.process_id`. It is supposed to be equal to
     * `VCAP_APPLICATION.app_id` for applications deployed to the runtime.
     * For system components, this could be the actual PID.
     */
    public const CLOUDFOUNDRY_PROCESS_ID = 'cloudfoundry.process.id';

    /**
     * The type of process.
     *
     * CloudFoundry applications can consist of multiple jobs. Usually the
     * main process will be of type `web`. There can be additional background
     * tasks or side-cars with different process types.
     */
    public const CLOUDFOUNDRY_PROCESS_TYPE = 'cloudfoundry.process.type';

    /**
     * The guid of the CloudFoundry space the application is running in.
     *
     * Application instrumentation should use the value from environment
     * variable `VCAP_APPLICATION.space_id`. This is the same value as
     * reported by `cf space <space-name> --guid`.
     */
    public const CLOUDFOUNDRY_SPACE_ID = 'cloudfoundry.space.id';

    /**
     * The name of the CloudFoundry space the application is running in.
     *
     * Application instrumentation should use the value from environment
     * variable `VCAP_APPLICATION.space_name`. This is the same value as
     * reported by `cf spaces`.
     */
    public const CLOUDFOUNDRY_SPACE_NAME = 'cloudfoundry.space.name';

    /**
     * A guid or another name describing the event source.
     *
     * CloudFoundry defines the `source_id` in the [Loggregator v2 envelope](https://github.com/cloudfoundry/loggregator-api#v2-envelope).
     * It is used for logs and metrics emitted by CloudFoundry. It is
     * supposed to contain the component name, e.g. "gorouter", for
     * CloudFoundry components.
     *
     * When system components are instrumented, values from the
     * [Bosh spec](https://bosh.io/docs/jobs/#properties-spec)
     * should be used. The `system.id` should be set to
     * `spec.deployment/spec.name`.
     */
    public const CLOUDFOUNDRY_SYSTEM_ID = 'cloudfoundry.system.id';

    /**
     * A guid describing the concrete instance of the event source.
     *
     * CloudFoundry defines the `instance_id` in the [Loggregator v2 envelope](https://github.com/cloudfoundry/loggregator-api#v2-envelope).
     * It is used for logs and metrics emitted by CloudFoundry. It is
     * supposed to contain the vm id for CloudFoundry components.
     *
     * When system components are instrumented, values from the
     * [Bosh spec](https://bosh.io/docs/jobs/#properties-spec)
     * should be used. The `system.instance.id` should be set to `spec.id`.
     */
    public const CLOUDFOUNDRY_SYSTEM_INSTANCE_ID = 'cloudfoundry.system.instance.id';

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
     * Container name used by container runtime.
     */
    public const CONTAINER_NAME = 'container.name';

    /**
     * The container runtime managing this container.
     */
    public const CONTAINER_RUNTIME = 'container.runtime';

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
     * > Any instrumentation providing this identifier MUST implement it as an opt-in feature.> See [`app.installation.id`](/docs/registry/attributes/app.md#app-installation-id)>  for a more privacy-preserving alternative.
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
     * The execution environment ID as a string, that will be potentially reused for other invocations to the same function/function version.
     *
     * - **AWS Lambda:** Use the (full) log stream name.
     */
    public const FAAS_INSTANCE = 'faas.instance';

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
     *
     * Collecting `host.id` from non-containerized systems
     *
     * **Non-privileged Machine ID Lookup**
     *
     * When collecting `host.id` for non-containerized systems non-privileged lookups
     * of the machine id are preferred. SDK detector implementations MUST use the
     * sources listed below to obtain the machine id.
     *
     * | OS | Primary | Fallback |
     * |---------|---------|---------|
     * | Linux   | contents of `/etc/machine-id` | contents of `/var/lib/dbus/machine-id` |
     * | BSD     | contents of `/etc/hostid` | output of `kenv -q smbios.system.uuid` |
     * | MacOS   | `IOPlatformUUID` line from the output of `ioreg -rd1 -c "IOPlatformExpertDevice"` | - |
     * | Windows | `MachineGuid` from registry `HKEY_LOCAL_MACHINE\SOFTWARE\Microsoft\Cryptography`  | - |
     *
     * **Privileged Machine ID Lookup**
     *
     * The `host.id` can be looked up using privileged sources. For example, Linux
     * systems can use the output of `dmidecode -t system`, `dmidecode -t baseboard`,
     * `dmidecode -t chassis`, or read the corresponding data from the filesystem
     * (e.g. `cat /sys/devices/virtual/dmi/id/product_id`,
     * `cat /sys/devices/virtual/dmi/id/product_uuid`, etc), however, SDK resource
     * detector implementations MUST not collect `host.id` from privileged sources. If
     * privileged lookup of `host.id` is required, the value should be injected via the
     * `OTEL_RESOURCE_ATTRIBUTES` environment variable.
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
     * The API version of the target resource to scale for the HorizontalPodAutoscaler.
     *
     * This maps to the `apiVersion` field in the `scaleTargetRef` of the HPA spec.
     */
    public const K8S_HPA_SCALETARGETREF_API_VERSION = 'k8s.hpa.scaletargetref.api_version';

    /**
     * The kind of the target resource to scale for the HorizontalPodAutoscaler.
     *
     * This maps to the `kind` field in the `scaleTargetRef` of the HPA spec.
     */
    public const K8S_HPA_SCALETARGETREF_KIND = 'k8s.hpa.scaletargetref.kind';

    /**
     * The name of the target resource to scale for the HorizontalPodAutoscaler.
     *
     * This maps to the `name` field in the `scaleTargetRef` of the HPA spec.
     */
    public const K8S_HPA_SCALETARGETREF_NAME = 'k8s.hpa.scaletargetref.name';

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
     * Name of the logical partition that hosts a systems with a mainframe operating system.
     */
    public const MAINFRAME_LPAR_NAME = 'mainframe.lpar.name';

    /**
     * The digest of the OCI image manifest. For container images specifically is the digest by which the container image is known.
     *
     * Follows [OCI Image Manifest Specification](https://github.com/opencontainers/image-spec/blob/main/manifest.md), and specifically the [Digest property](https://github.com/opencontainers/image-spec/blob/main/descriptor.md#digests).
     * An example can be found in [Example Image Manifest](https://github.com/opencontainers/image-spec/blob/main/manifest.md#example-image-manifest).
     */
    public const OCI_MANIFEST_DIGEST = 'oci.manifest.digest';

    /**
     * Unique identifier for a particular build or compilation of the operating system.
     * `build_id` values SHOULD be obtained from the following sources:
     *
     * | OS | Primary | Fallback |
     * | ------- | ------- | ------- |
     * | Windows | `CurrentBuildNumber` from registry `HKEY_LOCAL_MACHINE\SOFTWARE\Microsoft\Windows NT\CurrentVersion` | - |
     * | MacOS | `ProductBuildVersion` from `/System/Library/CoreServices/SystemVersion.plist` | `ProductBuildVersion` from `/System/Library/CoreServices/ServerVersion.plist` |
     * | Linux | `BUILD_ID` from `/etc/os-release` | `BUILD_ID` from `/usr/lib/os-release`; <br> contents of `/proc/sys/kernel/osrelease`|
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
     * The name of the instrumentation scope - (`InstrumentationScope.Name` in OTLP).
     */
    public const OTEL_SCOPE_NAME = 'otel.scope.name';

    /**
     * The version of the instrumentation scope - (`InstrumentationScope.Version` in OTLP).
     */
    public const OTEL_SCOPE_VERSION = 'otel.scope.version';

    /**
     * The command used to launch the process (i.e. the command name). On Linux based systems, can be set to the zeroth string in `proc/[pid]/cmdline`. On Windows, can be set to the first parameter extracted from `GetCommandLineW`.
     */
    public const PROCESS_COMMAND = 'process.command';

    /**
     * All the command arguments (including the command/executable itself) as received by the process. On Linux-based systems (and some other Unixoid systems supporting procfs), can be set according to the list of null-delimited strings extracted from `proc/[pid]/cmdline`. For libc-based executables, this would be the full argv vector passed to `main`. SHOULD NOT be collected by default unless there is sanitization that excludes sensitive data.
     */
    public const PROCESS_COMMAND_ARGS = 'process.command_args';

    /**
     * The full command used to launch the process as a single string representing the full command. On Windows, can be set to the result of `GetCommandLineW`. Do not set this if you have to assemble it just for monitoring; use `process.command_args` instead. SHOULD NOT be collected by default unless there is sanitization that excludes sensitive data.
     */
    public const PROCESS_COMMAND_LINE = 'process.command_line';

    /**
     * The name of the process executable. On Linux based systems, this SHOULD be set to the base name of the target of `/proc/[pid]/exe`. On Windows, this SHOULD be set to the base name of `GetProcessImageFileNameW`.
     */
    public const PROCESS_EXECUTABLE_NAME = 'process.executable.name';

    /**
     * The full path to the process executable. On Linux based systems, can be set to the target of `proc/[pid]/exe`. On Windows, can be set to the result of `GetProcessImageFileNameW`.
     */
    public const PROCESS_EXECUTABLE_PATH = 'process.executable.path';

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
     * Parent Process identifier (PPID).
     */
    public const PROCESS_PARENT_PID = 'process.parent_pid';

    /**
     * Process identifier (PID).
     */
    public const PROCESS_PID = 'process.pid';

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
     * Full user-agent string provided by the browser
     * The user-agent value SHOULD be provided only from browsers that do not have a mechanism to retrieve brands and platform individually from the User-Agent Client Hints API. To retrieve the value, the legacy `navigator.userAgent` API can be used.
     */
    public const USER_AGENT_ORIGINAL = 'user_agent.original';

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
     */
    public const VCS_REF_TYPE = 'vcs.ref.type';

    /**
     * The human readable name of the repository. It SHOULD NOT include any additional identifier like Group/SubGroup in GitLab or organization in GitHub.
     *
     * Due to it only being the name, it can clash with forks of the same
     * repository if collecting telemetry across multiple orgs or groups in
     * the same backends.
     */
    public const VCS_REPOSITORY_NAME = 'vcs.repository.name';

    /**
     * The [canonical URL](https://support.google.com/webmasters/answer/10347851?hl=en#:~:text=A%20canonical%20URL%20is%20the,Google%20chooses%20one%20as%20canonical.) of the repository providing the complete HTTP(S) address in order to locate and identify the repository through a browser.
     *
     * In Git Version Control Systems, the canonical URL SHOULD NOT include
     * the `.git` extension.
     */
    public const VCS_REPOSITORY_URL_FULL = 'vcs.repository.url.full';

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

    /**
     * The System Management Facility (SMF) Identifier uniquely identified a z/OS system within a SYSPLEX or mainframe environment and is used for system and performance analysis.
     */
    public const ZOS_SMF_ID = 'zos.smf.id';

    /**
     * The name of the SYSPLEX to which the z/OS system belongs too.
     */
    public const ZOS_SYSPLEX_NAME = 'zos.sysplex.name';

}
