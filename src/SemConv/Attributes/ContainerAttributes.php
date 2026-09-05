<?php

// DO NOT EDIT, this is an Auto-generated file from script/semantic-conventions

declare(strict_types=1);

namespace OpenTelemetry\SemConv\Attributes;

/**
 * Semantic attributes and corresponding values for container.
 * @see https://opentelemetry.io/docs/specs/semconv/registry/attributes/container/
 */
interface ContainerAttributes
{
    /**
     * Container ID. Usually a UUID, as for example used to [identify Docker containers](https://docs.docker.com/engine/containers/run/#container-identification). The UUID might be abbreviated.
     *
     * @stable
     */
    public const CONTAINER_ID = 'container.id';

    /**
     * Name of the image the container was built on.
     *
     * @stable
     */
    public const CONTAINER_IMAGE_NAME = 'container.image.name';

    /**
     * Repository digests of the container image as provided by the container runtime.
     *
     * [Docker](https://docs.docker.com/reference/api/engine/version/v1.52/#tag/Image/operation/ImageInspect) and [CRI](https://github.com/kubernetes/cri-api/blob/c75ef5b473bbe2d0a4fc92f82235efd665ea8e9f/pkg/apis/runtime/v1/api.proto#L1237-L1238) report those under the `RepoDigests` field.
     *
     * @stable
     */
    public const CONTAINER_IMAGE_REPO_DIGESTS = 'container.image.repo_digests';

    /**
     * Container image tags. An example can be found in [Docker Image Inspect](https://docs.docker.com/reference/api/engine/version/v1.52/#tag/Image/operation/ImageInspect). Should be only the `<tag>` section of the full name for example from `registry.example.com/my-org/my-image:<tag>`.
     *
     * @stable
     */
    public const CONTAINER_IMAGE_TAGS = 'container.image.tags';

}
