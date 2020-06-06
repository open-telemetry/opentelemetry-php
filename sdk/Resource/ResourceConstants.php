<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Resource;

/**
 * For certain attribute groups if any attribute from the group is present in the Resource then all attributes
 * that are marked as Required MUST be also present in the Resource. However it is also valid if the entire attribute
 * group is omitted (i.e. none of the attributes from the particular group are present even though some of them are
 * marked as Required in this document).
 *
 * @link https://github.com/open-telemetry/opentelemetry-specification/tree/master/specification/resource/semantic_conventions
 */
class ResourceConstants
{
    /**
     * Service
     */
    public const SERVICE_NAME = 'service.name'; // required
    public const SERVICE_NAMESPACE = 'service.namespace';
    public const SERVICE_INSTANCE_ID = 'service.instance.id'; // required
    public const SERVICE_VERSION = 'service.version';

    /**
     * Telemetry SDK
     */
    public const TELEMETRY_SDK_NAME = 'telemetry.sdk.name';
    public const TELEMETRY_SDK_LANGUAGE = 'telemetry.sdk.language';
    public const TELEMETRY_SDK_VERSION = 'telemetry.sdk.version';

    /**
     * Container
     */
    public const CONTAINER_NAME = 'container.name';
    public const CONTAINER_IMAGE_NAME = 'container.image.name';
    public const CONTAINER_IMAGE_TAG = 'container.image.tag';

    /**
     * Function as a Service
     */
    public const FAAS_NAME = 'faas.name'; // required
    public const FAAS_ID = 'faas.id'; // required
    public const FAAS_VERSION = 'faas.version';
    public const FAAS_INSTANCE = 'faas.instance';

    /**
     * Kubernetes
     */
    public const K8S_CLUSTER_NAME = 'k8s.cluster.name';
    public const K8S_NAMESPACE_NAME = 'k8s.namespace.name';
    public const K8S_POD_NAME = 'k8s.pod.name';
    public const K8S_DEPLOYMENT_NAME = 'k8s.deployment.name';

    /**
     * Host
     */
    public const HOST_HOSTNAME = 'host.hostname';
    public const HOST_ID = 'host.id';
    public const HOST_NAME = 'host.name';
    public const HOST_TYPE = 'host.type';
    public const HOST_IMAGE_NAME = 'host.image.name';
    public const HOST_IMAGE_ID = 'host.image.id';
    public const HOST_IMAGE_VERSION = 'host.image.version';

    /**
     * Cloud
     */
    public const CLOUD_PROVIDER = 'cloud.provider';
    public const CLOUD_ACCOUNT_ID = 'cloud.account.id';
    public const CLOUD_REGION = 'cloud.region';
    public const CLOUD_ZONE = 'cloud.zone';
}
