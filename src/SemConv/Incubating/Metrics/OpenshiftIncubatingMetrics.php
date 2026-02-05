<?php

// DO NOT EDIT, this is an Auto-generated file from script/semantic-conventions

declare(strict_types=1);

namespace OpenTelemetry\SemConv\Incubating\Metrics;

/**
 * Metrics for openshift.
 * May contain @experimental Semantic Conventions which may change or be removed in the future.
 */
interface OpenshiftIncubatingMetrics
{
    /**
     * The enforced hard limit of the resource across all projects.
     *
     * This metric is retrieved from the `Status.Total.Hard` field of the
     * [K8s ResourceQuotaStatus](https://kubernetes.io/docs/reference/generated/kubernetes-api/v1.32/#resourcequotastatus-v1-core)
     * of the
     * [ClusterResourceQuota](https://docs.redhat.com/en/documentation/openshift_container_platform/4.19/html/schedule_and_quota_apis/clusterresourcequota-quota-openshift-io-v1#status-total).
     *
     * Instrument: updowncounter
     * Unit: {cpu}
     * @experimental
     */
    public const OPENSHIFT_CLUSTERQUOTA_CPU_LIMIT_HARD = 'openshift.clusterquota.cpu.limit.hard';

    /**
     * The current observed total usage of the resource across all projects.
     *
     * This metric is retrieved from the `Status.Total.Used` field of the
     * [K8s ResourceQuotaStatus](https://kubernetes.io/docs/reference/generated/kubernetes-api/v1.32/#resourcequotastatus-v1-core)
     * of the
     * [ClusterResourceQuota](https://docs.redhat.com/en/documentation/openshift_container_platform/4.19/html/schedule_and_quota_apis/clusterresourcequota-quota-openshift-io-v1#status-total).
     *
     * Instrument: updowncounter
     * Unit: {cpu}
     * @experimental
     */
    public const OPENSHIFT_CLUSTERQUOTA_CPU_LIMIT_USED = 'openshift.clusterquota.cpu.limit.used';

    /**
     * The enforced hard limit of the resource across all projects.
     *
     * This metric is retrieved from the `Status.Total.Hard` field of the
     * [K8s ResourceQuotaStatus](https://kubernetes.io/docs/reference/generated/kubernetes-api/v1.32/#resourcequotastatus-v1-core)
     * of the
     * [ClusterResourceQuota](https://docs.redhat.com/en/documentation/openshift_container_platform/4.19/html/schedule_and_quota_apis/clusterresourcequota-quota-openshift-io-v1#status-total).
     *
     * Instrument: updowncounter
     * Unit: {cpu}
     * @experimental
     */
    public const OPENSHIFT_CLUSTERQUOTA_CPU_REQUEST_HARD = 'openshift.clusterquota.cpu.request.hard';

    /**
     * The current observed total usage of the resource across all projects.
     *
     * This metric is retrieved from the `Status.Total.Used` field of the
     * [K8s ResourceQuotaStatus](https://kubernetes.io/docs/reference/generated/kubernetes-api/v1.32/#resourcequotastatus-v1-core)
     * of the
     * [ClusterResourceQuota](https://docs.redhat.com/en/documentation/openshift_container_platform/4.19/html/schedule_and_quota_apis/clusterresourcequota-quota-openshift-io-v1#status-total).
     *
     * Instrument: updowncounter
     * Unit: {cpu}
     * @experimental
     */
    public const OPENSHIFT_CLUSTERQUOTA_CPU_REQUEST_USED = 'openshift.clusterquota.cpu.request.used';

    /**
     * The enforced hard limit of the resource across all projects.
     *
     * This metric is retrieved from the `Status.Total.Hard` field of the
     * [K8s ResourceQuotaStatus](https://kubernetes.io/docs/reference/generated/kubernetes-api/v1.32/#resourcequotastatus-v1-core)
     * of the
     * [ClusterResourceQuota](https://docs.redhat.com/en/documentation/openshift_container_platform/4.19/html/schedule_and_quota_apis/clusterresourcequota-quota-openshift-io-v1#status-total).
     *
     * Instrument: updowncounter
     * Unit: By
     * @experimental
     */
    public const OPENSHIFT_CLUSTERQUOTA_EPHEMERAL_STORAGE_LIMIT_HARD = 'openshift.clusterquota.ephemeral_storage.limit.hard';

    /**
     * The current observed total usage of the resource across all projects.
     *
     * This metric is retrieved from the `Status.Total.Used` field of the
     * [K8s ResourceQuotaStatus](https://kubernetes.io/docs/reference/generated/kubernetes-api/v1.32/#resourcequotastatus-v1-core)
     * of the
     * [ClusterResourceQuota](https://docs.redhat.com/en/documentation/openshift_container_platform/4.19/html/schedule_and_quota_apis/clusterresourcequota-quota-openshift-io-v1#status-total).
     *
     * Instrument: updowncounter
     * Unit: By
     * @experimental
     */
    public const OPENSHIFT_CLUSTERQUOTA_EPHEMERAL_STORAGE_LIMIT_USED = 'openshift.clusterquota.ephemeral_storage.limit.used';

    /**
     * The enforced hard limit of the resource across all projects.
     *
     * This metric is retrieved from the `Status.Total.Hard` field of the
     * [K8s ResourceQuotaStatus](https://kubernetes.io/docs/reference/generated/kubernetes-api/v1.32/#resourcequotastatus-v1-core)
     * of the
     * [ClusterResourceQuota](https://docs.redhat.com/en/documentation/openshift_container_platform/4.19/html/schedule_and_quota_apis/clusterresourcequota-quota-openshift-io-v1#status-total).
     *
     * Instrument: updowncounter
     * Unit: By
     * @experimental
     */
    public const OPENSHIFT_CLUSTERQUOTA_EPHEMERAL_STORAGE_REQUEST_HARD = 'openshift.clusterquota.ephemeral_storage.request.hard';

    /**
     * The current observed total usage of the resource across all projects.
     *
     * This metric is retrieved from the `Status.Total.Used` field of the
     * [K8s ResourceQuotaStatus](https://kubernetes.io/docs/reference/generated/kubernetes-api/v1.32/#resourcequotastatus-v1-core)
     * of the
     * [ClusterResourceQuota](https://docs.redhat.com/en/documentation/openshift_container_platform/4.19/html/schedule_and_quota_apis/clusterresourcequota-quota-openshift-io-v1#status-total).
     *
     * Instrument: updowncounter
     * Unit: By
     * @experimental
     */
    public const OPENSHIFT_CLUSTERQUOTA_EPHEMERAL_STORAGE_REQUEST_USED = 'openshift.clusterquota.ephemeral_storage.request.used';

    /**
     * The enforced hard limit of the resource across all projects.
     *
     * This metric is retrieved from the `Status.Total.Hard` field of the
     * [K8s ResourceQuotaStatus](https://kubernetes.io/docs/reference/generated/kubernetes-api/v1.32/#resourcequotastatus-v1-core)
     * of the
     * [ClusterResourceQuota](https://docs.redhat.com/en/documentation/openshift_container_platform/4.19/html/schedule_and_quota_apis/clusterresourcequota-quota-openshift-io-v1#status-total).
     *
     * Instrument: updowncounter
     * Unit: {hugepage}
     * @experimental
     */
    public const OPENSHIFT_CLUSTERQUOTA_HUGEPAGE_COUNT_REQUEST_HARD = 'openshift.clusterquota.hugepage_count.request.hard';

    /**
     * The current observed total usage of the resource across all projects.
     *
     * This metric is retrieved from the `Status.Total.Used` field of the
     * [K8s ResourceQuotaStatus](https://kubernetes.io/docs/reference/generated/kubernetes-api/v1.32/#resourcequotastatus-v1-core)
     * of the
     * [ClusterResourceQuota](https://docs.redhat.com/en/documentation/openshift_container_platform/4.19/html/schedule_and_quota_apis/clusterresourcequota-quota-openshift-io-v1#status-total).
     *
     * Instrument: updowncounter
     * Unit: {hugepage}
     * @experimental
     */
    public const OPENSHIFT_CLUSTERQUOTA_HUGEPAGE_COUNT_REQUEST_USED = 'openshift.clusterquota.hugepage_count.request.used';

    /**
     * The enforced hard limit of the resource across all projects.
     *
     * This metric is retrieved from the `Status.Total.Hard` field of the
     * [K8s ResourceQuotaStatus](https://kubernetes.io/docs/reference/generated/kubernetes-api/v1.32/#resourcequotastatus-v1-core)
     * of the
     * [ClusterResourceQuota](https://docs.redhat.com/en/documentation/openshift_container_platform/4.19/html/schedule_and_quota_apis/clusterresourcequota-quota-openshift-io-v1#status-total).
     *
     * Instrument: updowncounter
     * Unit: By
     * @experimental
     */
    public const OPENSHIFT_CLUSTERQUOTA_MEMORY_LIMIT_HARD = 'openshift.clusterquota.memory.limit.hard';

    /**
     * The current observed total usage of the resource across all projects.
     *
     * This metric is retrieved from the `Status.Total.Used` field of the
     * [K8s ResourceQuotaStatus](https://kubernetes.io/docs/reference/generated/kubernetes-api/v1.32/#resourcequotastatus-v1-core)
     * of the
     * [ClusterResourceQuota](https://docs.redhat.com/en/documentation/openshift_container_platform/4.19/html/schedule_and_quota_apis/clusterresourcequota-quota-openshift-io-v1#status-total).
     *
     * Instrument: updowncounter
     * Unit: By
     * @experimental
     */
    public const OPENSHIFT_CLUSTERQUOTA_MEMORY_LIMIT_USED = 'openshift.clusterquota.memory.limit.used';

    /**
     * The enforced hard limit of the resource across all projects.
     *
     * This metric is retrieved from the `Status.Total.Hard` field of the
     * [K8s ResourceQuotaStatus](https://kubernetes.io/docs/reference/generated/kubernetes-api/v1.32/#resourcequotastatus-v1-core)
     * of the
     * [ClusterResourceQuota](https://docs.redhat.com/en/documentation/openshift_container_platform/4.19/html/schedule_and_quota_apis/clusterresourcequota-quota-openshift-io-v1#status-total).
     *
     * Instrument: updowncounter
     * Unit: By
     * @experimental
     */
    public const OPENSHIFT_CLUSTERQUOTA_MEMORY_REQUEST_HARD = 'openshift.clusterquota.memory.request.hard';

    /**
     * The current observed total usage of the resource across all projects.
     *
     * This metric is retrieved from the `Status.Total.Used` field of the
     * [K8s ResourceQuotaStatus](https://kubernetes.io/docs/reference/generated/kubernetes-api/v1.32/#resourcequotastatus-v1-core)
     * of the
     * [ClusterResourceQuota](https://docs.redhat.com/en/documentation/openshift_container_platform/4.19/html/schedule_and_quota_apis/clusterresourcequota-quota-openshift-io-v1#status-total).
     *
     * Instrument: updowncounter
     * Unit: By
     * @experimental
     */
    public const OPENSHIFT_CLUSTERQUOTA_MEMORY_REQUEST_USED = 'openshift.clusterquota.memory.request.used';

    /**
     * The enforced hard limit of the resource across all projects.
     *
     * This metric is retrieved from the `Status.Total.Hard` field of the
     * [K8s ResourceQuotaStatus](https://kubernetes.io/docs/reference/generated/kubernetes-api/v1.32/#resourcequotastatus-v1-core)
     * of the
     * [ClusterResourceQuota](https://docs.redhat.com/en/documentation/openshift_container_platform/4.19/html/schedule_and_quota_apis/clusterresourcequota-quota-openshift-io-v1#status-total).
     *
     * Instrument: updowncounter
     * Unit: {object}
     * @experimental
     */
    public const OPENSHIFT_CLUSTERQUOTA_OBJECT_COUNT_HARD = 'openshift.clusterquota.object_count.hard';

    /**
     * The current observed total usage of the resource across all projects.
     *
     * This metric is retrieved from the `Status.Total.Used` field of the
     * [K8s ResourceQuotaStatus](https://kubernetes.io/docs/reference/generated/kubernetes-api/v1.32/#resourcequotastatus-v1-core)
     * of the
     * [ClusterResourceQuota](https://docs.redhat.com/en/documentation/openshift_container_platform/4.19/html/schedule_and_quota_apis/clusterresourcequota-quota-openshift-io-v1#status-total).
     *
     * Instrument: updowncounter
     * Unit: {object}
     * @experimental
     */
    public const OPENSHIFT_CLUSTERQUOTA_OBJECT_COUNT_USED = 'openshift.clusterquota.object_count.used';

    /**
     * The enforced hard limit of the resource across all projects.
     *
     * This metric is retrieved from the `Status.Total.Hard` field of the
     * [K8s ResourceQuotaStatus](https://kubernetes.io/docs/reference/generated/kubernetes-api/v1.32/#resourcequotastatus-v1-core)
     * of the
     * [ClusterResourceQuota](https://docs.redhat.com/en/documentation/openshift_container_platform/4.19/html/schedule_and_quota_apis/clusterresourcequota-quota-openshift-io-v1#status-total).
     *
     * The `k8s.storageclass.name` should be required when a resource quota is defined for a specific
     * storage class.
     *
     * Instrument: updowncounter
     * Unit: {persistentvolumeclaim}
     * @experimental
     */
    public const OPENSHIFT_CLUSTERQUOTA_PERSISTENTVOLUMECLAIM_COUNT_HARD = 'openshift.clusterquota.persistentvolumeclaim_count.hard';

    /**
     * The current observed total usage of the resource across all projects.
     *
     * This metric is retrieved from the `Status.Total.Used` field of the
     * [K8s ResourceQuotaStatus](https://kubernetes.io/docs/reference/generated/kubernetes-api/v1.32/#resourcequotastatus-v1-core)
     * of the
     * [ClusterResourceQuota](https://docs.redhat.com/en/documentation/openshift_container_platform/4.19/html/schedule_and_quota_apis/clusterresourcequota-quota-openshift-io-v1#status-total).
     *
     * The `k8s.storageclass.name` should be required when a resource quota is defined for a specific
     * storage class.
     *
     * Instrument: updowncounter
     * Unit: {persistentvolumeclaim}
     * @experimental
     */
    public const OPENSHIFT_CLUSTERQUOTA_PERSISTENTVOLUMECLAIM_COUNT_USED = 'openshift.clusterquota.persistentvolumeclaim_count.used';

    /**
     * The enforced hard limit of the resource across all projects.
     *
     * This metric is retrieved from the `Status.Total.Hard` field of the
     * [K8s ResourceQuotaStatus](https://kubernetes.io/docs/reference/generated/kubernetes-api/v1.32/#resourcequotastatus-v1-core)
     * of the
     * [ClusterResourceQuota](https://docs.redhat.com/en/documentation/openshift_container_platform/4.19/html/schedule_and_quota_apis/clusterresourcequota-quota-openshift-io-v1#status-total).
     *
     * The `k8s.storageclass.name` should be required when a resource quota is defined for a specific
     * storage class.
     *
     * Instrument: updowncounter
     * Unit: By
     * @experimental
     */
    public const OPENSHIFT_CLUSTERQUOTA_STORAGE_REQUEST_HARD = 'openshift.clusterquota.storage.request.hard';

    /**
     * The current observed total usage of the resource across all projects.
     *
     * This metric is retrieved from the `Status.Total.Used` field of the
     * [K8s ResourceQuotaStatus](https://kubernetes.io/docs/reference/generated/kubernetes-api/v1.32/#resourcequotastatus-v1-core)
     * of the
     * [ClusterResourceQuota](https://docs.redhat.com/en/documentation/openshift_container_platform/4.19/html/schedule_and_quota_apis/clusterresourcequota-quota-openshift-io-v1#status-total).
     *
     * The `k8s.storageclass.name` should be required when a resource quota is defined for a specific
     * storage class.
     *
     * Instrument: updowncounter
     * Unit: By
     * @experimental
     */
    public const OPENSHIFT_CLUSTERQUOTA_STORAGE_REQUEST_USED = 'openshift.clusterquota.storage.request.used';

}
