<?php

// DO NOT EDIT, this is an Auto-generated file from script/semantic-conventions

declare(strict_types=1);

namespace OpenTelemetry\SemConv\Incubating\Attributes;

/**
 * Semantic attributes and corresponding values for container.
 * @see https://opentelemetry.io/docs/specs/semconv/registry/attributes/container/
 * May contain @experimental Semantic Conventions which may change or be removed in the future.
 */
interface ContainerIncubatingAttributes
{
    /**
     * The command used to run the container (i.e. the command name).
     *
     * If using embedded credentials or sensitive data, it is recommended to remove them to prevent potential leakage.
     *
     * @experimental
     */
    public const CONTAINER_COMMAND = 'container.command';

    /**
     * All the command arguments (including the command/executable itself) run by the container.
     *
     * @experimental
     */
    public const CONTAINER_COMMAND_ARGS = 'container.command_args';

    /**
     * The full command run by the container as a single string representing the full command.
     *
     * @experimental
     */
    public const CONTAINER_COMMAND_LINE = 'container.command_line';

    /**
     * The name of the CSI ([Container Storage Interface](https://github.com/container-storage-interface/spec)) plugin used by the volume.
     *
     * This can sometimes be referred to as a "driver" in CSI implementations. This should represent the `name` field of the GetPluginInfo RPC.
     *
     * @experimental
     */
    public const CONTAINER_CSI_PLUGIN_NAME = 'container.csi.plugin.name';

    /**
     * The unique volume ID returned by the CSI ([Container Storage Interface](https://github.com/container-storage-interface/spec)) plugin.
     *
     * This can sometimes be referred to as a "volume handle" in CSI implementations. This should represent the `Volume.volume_id` field in CSI spec.
     *
     * @experimental
     */
    public const CONTAINER_CSI_VOLUME_ID = 'container.csi.volume.id';

    /**
     * Container ID. Usually a UUID, as for example used to [identify Docker containers](https://docs.docker.com/engine/containers/run/#container-identification). The UUID might be abbreviated.
     *
     * @experimental
     */
    public const CONTAINER_ID = 'container.id';

    /**
     * Runtime specific image identifier. Usually a hash algorithm followed by a UUID.
     *
     * Docker defines a sha256 of the image id; `container.image.id` corresponds to the `Image` field from the Docker container inspect [API](https://docs.docker.com/engine/api/v1.43/#tag/Container/operation/ContainerInspect) endpoint.
     * K8s defines a link to the container registry repository with digest `"imageID": "registry.azurecr.io /namespace/service/dockerfile@sha256:bdeabd40c3a8a492eaf9e8e44d0ebbb84bac7ee25ac0cf8a7159d25f62555625"`.
     * The ID is assigned by the container runtime and can vary in different environments. Consider using `oci.manifest.digest` if it is important to identify the same image in different environments/runtimes.
     *
     * @experimental
     */
    public const CONTAINER_IMAGE_ID = 'container.image.id';

    /**
     * Name of the image the container was built on.
     *
     * @experimental
     */
    public const CONTAINER_IMAGE_NAME = 'container.image.name';

    /**
     * Repo digests of the container image as provided by the container runtime.
     *
     * [Docker](https://docs.docker.com/engine/api/v1.43/#tag/Image/operation/ImageInspect) and [CRI](https://github.com/kubernetes/cri-api/blob/c75ef5b473bbe2d0a4fc92f82235efd665ea8e9f/pkg/apis/runtime/v1/api.proto#L1237-L1238) report those under the `RepoDigests` field.
     *
     * @experimental
     */
    public const CONTAINER_IMAGE_REPO_DIGESTS = 'container.image.repo_digests';

    /**
     * Container image tags. An example can be found in [Docker Image Inspect](https://docs.docker.com/engine/api/v1.43/#tag/Image/operation/ImageInspect). Should be only the `<tag>` section of the full name for example from `registry.example.com/my-org/my-image:<tag>`.
     *
     * @experimental
     */
    public const CONTAINER_IMAGE_TAGS = 'container.image.tags';

    /**
     * Container labels, `<key>` being the label name, the value being the label value.
     *
     * For example, a docker container label `app` with value `nginx` SHOULD be recorded as the `container.label.app` attribute with value `"nginx"`.
     *
     * @experimental
     */
    public const CONTAINER_LABEL = 'container.label';

    /**
     * Container name used by container runtime.
     *
     * @experimental
     */
    public const CONTAINER_NAME = 'container.name';

    /**
     * A description about the runtime which could include, for example details about the CRI/API version being used or other customisations.
     *
     * @experimental
     */
    public const CONTAINER_RUNTIME_DESCRIPTION = 'container.runtime.description';

    /**
     * The container runtime managing this container.
     *
     * @experimental
     */
    public const CONTAINER_RUNTIME_NAME = 'container.runtime.name';

    /**
     * The version of the runtime of this process, as returned by the runtime without modification.
     *
     * @experimental
     */
    public const CONTAINER_RUNTIME_VERSION = 'container.runtime.version';

}
