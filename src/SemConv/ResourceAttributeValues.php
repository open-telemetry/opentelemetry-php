<?php

// DO NOT EDIT, this is archived and left for backward compatibility.

declare(strict_types=1);

namespace OpenTelemetry\SemConv;

/**
 * @deprecated Use {@see OpenTelemetry\SemConv\Attributes}\* or {@see OpenTelemetry\SemConv\Incubating\Attributes}\* instead.
 */
interface ResourceAttributeValues
{
    /**
     * The URL of the OpenTelemetry schema for these values.
     */
    public const SCHEMA_URL = 'https://opentelemetry.io/schemas/1.32.0';

    /**
     * ec2
     *
     * @see ResourceAttributes::AWS_ECS_LAUNCHTYPE
     */
    public const AWS_ECS_LAUNCHTYPE_EC2 = 'ec2';

    /**
     * fargate
     *
     * @see ResourceAttributes::AWS_ECS_LAUNCHTYPE
     */
    public const AWS_ECS_LAUNCHTYPE_FARGATE = 'fargate';

    /**
     * Alibaba Cloud Elastic Compute Service
     *
     * @see ResourceAttributes::CLOUD_PLATFORM
     */
    public const CLOUD_PLATFORM_ALIBABA_CLOUD_ECS = 'alibaba_cloud_ecs';

    /**
     * Alibaba Cloud Function Compute
     *
     * @see ResourceAttributes::CLOUD_PLATFORM
     */
    public const CLOUD_PLATFORM_ALIBABA_CLOUD_FC = 'alibaba_cloud_fc';

    /**
     * Red Hat OpenShift on Alibaba Cloud
     *
     * @see ResourceAttributes::CLOUD_PLATFORM
     */
    public const CLOUD_PLATFORM_ALIBABA_CLOUD_OPENSHIFT = 'alibaba_cloud_openshift';

    /**
     * AWS Elastic Compute Cloud
     *
     * @see ResourceAttributes::CLOUD_PLATFORM
     */
    public const CLOUD_PLATFORM_AWS_EC2 = 'aws_ec2';

    /**
     * AWS Elastic Container Service
     *
     * @see ResourceAttributes::CLOUD_PLATFORM
     */
    public const CLOUD_PLATFORM_AWS_ECS = 'aws_ecs';

    /**
     * AWS Elastic Kubernetes Service
     *
     * @see ResourceAttributes::CLOUD_PLATFORM
     */
    public const CLOUD_PLATFORM_AWS_EKS = 'aws_eks';

    /**
     * AWS Lambda
     *
     * @see ResourceAttributes::CLOUD_PLATFORM
     */
    public const CLOUD_PLATFORM_AWS_LAMBDA = 'aws_lambda';

    /**
     * AWS Elastic Beanstalk
     *
     * @see ResourceAttributes::CLOUD_PLATFORM
     */
    public const CLOUD_PLATFORM_AWS_ELASTIC_BEANSTALK = 'aws_elastic_beanstalk';

    /**
     * AWS App Runner
     *
     * @see ResourceAttributes::CLOUD_PLATFORM
     */
    public const CLOUD_PLATFORM_AWS_APP_RUNNER = 'aws_app_runner';

    /**
     * Red Hat OpenShift on AWS (ROSA)
     *
     * @see ResourceAttributes::CLOUD_PLATFORM
     */
    public const CLOUD_PLATFORM_AWS_OPENSHIFT = 'aws_openshift';

    /**
     * Azure Virtual Machines
     *
     * @see ResourceAttributes::CLOUD_PLATFORM
     */
    public const CLOUD_PLATFORM_AZURE_VM = 'azure_vm';

    /**
     * Azure Container Apps
     *
     * @see ResourceAttributes::CLOUD_PLATFORM
     */
    public const CLOUD_PLATFORM_AZURE_CONTAINER_APPS = 'azure_container_apps';

    /**
     * Azure Container Instances
     *
     * @see ResourceAttributes::CLOUD_PLATFORM
     */
    public const CLOUD_PLATFORM_AZURE_CONTAINER_INSTANCES = 'azure_container_instances';

    /**
     * Azure Kubernetes Service
     *
     * @see ResourceAttributes::CLOUD_PLATFORM
     */
    public const CLOUD_PLATFORM_AZURE_AKS = 'azure_aks';

    /**
     * Azure Functions
     *
     * @see ResourceAttributes::CLOUD_PLATFORM
     */
    public const CLOUD_PLATFORM_AZURE_FUNCTIONS = 'azure_functions';

    /**
     * Azure App Service
     *
     * @see ResourceAttributes::CLOUD_PLATFORM
     */
    public const CLOUD_PLATFORM_AZURE_APP_SERVICE = 'azure_app_service';

    /**
     * Azure Red Hat OpenShift
     *
     * @see ResourceAttributes::CLOUD_PLATFORM
     */
    public const CLOUD_PLATFORM_AZURE_OPENSHIFT = 'azure_openshift';

    /**
     * Google Bare Metal Solution (BMS)
     *
     * @see ResourceAttributes::CLOUD_PLATFORM
     */
    public const CLOUD_PLATFORM_GCP_BARE_METAL_SOLUTION = 'gcp_bare_metal_solution';

    /**
     * Google Cloud Compute Engine (GCE)
     *
     * @see ResourceAttributes::CLOUD_PLATFORM
     */
    public const CLOUD_PLATFORM_GCP_COMPUTE_ENGINE = 'gcp_compute_engine';

    /**
     * Google Cloud Run
     *
     * @see ResourceAttributes::CLOUD_PLATFORM
     */
    public const CLOUD_PLATFORM_GCP_CLOUD_RUN = 'gcp_cloud_run';

    /**
     * Google Cloud Kubernetes Engine (GKE)
     *
     * @see ResourceAttributes::CLOUD_PLATFORM
     */
    public const CLOUD_PLATFORM_GCP_KUBERNETES_ENGINE = 'gcp_kubernetes_engine';

    /**
     * Google Cloud Functions (GCF)
     *
     * @see ResourceAttributes::CLOUD_PLATFORM
     */
    public const CLOUD_PLATFORM_GCP_CLOUD_FUNCTIONS = 'gcp_cloud_functions';

    /**
     * Google Cloud App Engine (GAE)
     *
     * @see ResourceAttributes::CLOUD_PLATFORM
     */
    public const CLOUD_PLATFORM_GCP_APP_ENGINE = 'gcp_app_engine';

    /**
     * Red Hat OpenShift on Google Cloud
     *
     * @see ResourceAttributes::CLOUD_PLATFORM
     */
    public const CLOUD_PLATFORM_GCP_OPENSHIFT = 'gcp_openshift';

    /**
     * Red Hat OpenShift on IBM Cloud
     *
     * @see ResourceAttributes::CLOUD_PLATFORM
     */
    public const CLOUD_PLATFORM_IBM_CLOUD_OPENSHIFT = 'ibm_cloud_openshift';

    /**
     * Compute on Oracle Cloud Infrastructure (OCI)
     *
     * @see ResourceAttributes::CLOUD_PLATFORM
     */
    public const CLOUD_PLATFORM_ORACLE_CLOUD_COMPUTE = 'oracle_cloud_compute';

    /**
     * Kubernetes Engine (OKE) on Oracle Cloud Infrastructure (OCI)
     *
     * @see ResourceAttributes::CLOUD_PLATFORM
     */
    public const CLOUD_PLATFORM_ORACLE_CLOUD_OKE = 'oracle_cloud_oke';

    /**
     * Tencent Cloud Cloud Virtual Machine (CVM)
     *
     * @see ResourceAttributes::CLOUD_PLATFORM
     */
    public const CLOUD_PLATFORM_TENCENT_CLOUD_CVM = 'tencent_cloud_cvm';

    /**
     * Tencent Cloud Elastic Kubernetes Service (EKS)
     *
     * @see ResourceAttributes::CLOUD_PLATFORM
     */
    public const CLOUD_PLATFORM_TENCENT_CLOUD_EKS = 'tencent_cloud_eks';

    /**
     * Tencent Cloud Serverless Cloud Function (SCF)
     *
     * @see ResourceAttributes::CLOUD_PLATFORM
     */
    public const CLOUD_PLATFORM_TENCENT_CLOUD_SCF = 'tencent_cloud_scf';

    /**
     * Alibaba Cloud
     *
     * @see ResourceAttributes::CLOUD_PROVIDER
     */
    public const CLOUD_PROVIDER_ALIBABA_CLOUD = 'alibaba_cloud';

    /**
     * Amazon Web Services
     *
     * @see ResourceAttributes::CLOUD_PROVIDER
     */
    public const CLOUD_PROVIDER_AWS = 'aws';

    /**
     * Microsoft Azure
     *
     * @see ResourceAttributes::CLOUD_PROVIDER
     */
    public const CLOUD_PROVIDER_AZURE = 'azure';

    /**
     * Google Cloud Platform
     *
     * @see ResourceAttributes::CLOUD_PROVIDER
     */
    public const CLOUD_PROVIDER_GCP = 'gcp';

    /**
     * Heroku Platform as a Service
     *
     * @see ResourceAttributes::CLOUD_PROVIDER
     */
    public const CLOUD_PROVIDER_HEROKU = 'heroku';

    /**
     * IBM Cloud
     *
     * @see ResourceAttributes::CLOUD_PROVIDER
     */
    public const CLOUD_PROVIDER_IBM_CLOUD = 'ibm_cloud';

    /**
     * Oracle Cloud Infrastructure (OCI)
     *
     * @see ResourceAttributes::CLOUD_PROVIDER
     */
    public const CLOUD_PROVIDER_ORACLE_CLOUD = 'oracle_cloud';

    /**
     * Tencent Cloud
     *
     * @see ResourceAttributes::CLOUD_PROVIDER
     */
    public const CLOUD_PROVIDER_TENCENT_CLOUD = 'tencent_cloud';

    /**
     * Mission critical service.
     *
     * @see ResourceAttributes::GCP_APPHUB_SERVICE_CRITICALITY_TYPE
     */
    public const GCP_APPHUB_SERVICE_CRITICALITY_TYPE_MISSION_CRITICAL = 'MISSION_CRITICAL';

    /**
     * High impact.
     *
     * @see ResourceAttributes::GCP_APPHUB_SERVICE_CRITICALITY_TYPE
     */
    public const GCP_APPHUB_SERVICE_CRITICALITY_TYPE_HIGH = 'HIGH';

    /**
     * Medium impact.
     *
     * @see ResourceAttributes::GCP_APPHUB_SERVICE_CRITICALITY_TYPE
     */
    public const GCP_APPHUB_SERVICE_CRITICALITY_TYPE_MEDIUM = 'MEDIUM';

    /**
     * Low impact.
     *
     * @see ResourceAttributes::GCP_APPHUB_SERVICE_CRITICALITY_TYPE
     */
    public const GCP_APPHUB_SERVICE_CRITICALITY_TYPE_LOW = 'LOW';

    /**
     * Production environment.
     *
     * @see ResourceAttributes::GCP_APPHUB_SERVICE_ENVIRONMENT_TYPE
     */
    public const GCP_APPHUB_SERVICE_ENVIRONMENT_TYPE_PRODUCTION = 'PRODUCTION';

    /**
     * Staging environment.
     *
     * @see ResourceAttributes::GCP_APPHUB_SERVICE_ENVIRONMENT_TYPE
     */
    public const GCP_APPHUB_SERVICE_ENVIRONMENT_TYPE_STAGING = 'STAGING';

    /**
     * Test environment.
     *
     * @see ResourceAttributes::GCP_APPHUB_SERVICE_ENVIRONMENT_TYPE
     */
    public const GCP_APPHUB_SERVICE_ENVIRONMENT_TYPE_TEST = 'TEST';

    /**
     * Development environment.
     *
     * @see ResourceAttributes::GCP_APPHUB_SERVICE_ENVIRONMENT_TYPE
     */
    public const GCP_APPHUB_SERVICE_ENVIRONMENT_TYPE_DEVELOPMENT = 'DEVELOPMENT';

    /**
     * Mission critical service.
     *
     * @see ResourceAttributes::GCP_APPHUB_WORKLOAD_CRITICALITY_TYPE
     */
    public const GCP_APPHUB_WORKLOAD_CRITICALITY_TYPE_MISSION_CRITICAL = 'MISSION_CRITICAL';

    /**
     * High impact.
     *
     * @see ResourceAttributes::GCP_APPHUB_WORKLOAD_CRITICALITY_TYPE
     */
    public const GCP_APPHUB_WORKLOAD_CRITICALITY_TYPE_HIGH = 'HIGH';

    /**
     * Medium impact.
     *
     * @see ResourceAttributes::GCP_APPHUB_WORKLOAD_CRITICALITY_TYPE
     */
    public const GCP_APPHUB_WORKLOAD_CRITICALITY_TYPE_MEDIUM = 'MEDIUM';

    /**
     * Low impact.
     *
     * @see ResourceAttributes::GCP_APPHUB_WORKLOAD_CRITICALITY_TYPE
     */
    public const GCP_APPHUB_WORKLOAD_CRITICALITY_TYPE_LOW = 'LOW';

    /**
     * Production environment.
     *
     * @see ResourceAttributes::GCP_APPHUB_WORKLOAD_ENVIRONMENT_TYPE
     */
    public const GCP_APPHUB_WORKLOAD_ENVIRONMENT_TYPE_PRODUCTION = 'PRODUCTION';

    /**
     * Staging environment.
     *
     * @see ResourceAttributes::GCP_APPHUB_WORKLOAD_ENVIRONMENT_TYPE
     */
    public const GCP_APPHUB_WORKLOAD_ENVIRONMENT_TYPE_STAGING = 'STAGING';

    /**
     * Test environment.
     *
     * @see ResourceAttributes::GCP_APPHUB_WORKLOAD_ENVIRONMENT_TYPE
     */
    public const GCP_APPHUB_WORKLOAD_ENVIRONMENT_TYPE_TEST = 'TEST';

    /**
     * Development environment.
     *
     * @see ResourceAttributes::GCP_APPHUB_WORKLOAD_ENVIRONMENT_TYPE
     */
    public const GCP_APPHUB_WORKLOAD_ENVIRONMENT_TYPE_DEVELOPMENT = 'DEVELOPMENT';

    /**
     * AMD64
     *
     * @see ResourceAttributes::HOST_ARCH
     */
    public const HOST_ARCH_AMD64 = 'amd64';

    /**
     * ARM32
     *
     * @see ResourceAttributes::HOST_ARCH
     */
    public const HOST_ARCH_ARM32 = 'arm32';

    /**
     * ARM64
     *
     * @see ResourceAttributes::HOST_ARCH
     */
    public const HOST_ARCH_ARM64 = 'arm64';

    /**
     * Itanium
     *
     * @see ResourceAttributes::HOST_ARCH
     */
    public const HOST_ARCH_IA64 = 'ia64';

    /**
     * 32-bit PowerPC
     *
     * @see ResourceAttributes::HOST_ARCH
     */
    public const HOST_ARCH_PPC32 = 'ppc32';

    /**
     * 64-bit PowerPC
     *
     * @see ResourceAttributes::HOST_ARCH
     */
    public const HOST_ARCH_PPC64 = 'ppc64';

    /**
     * IBM z/Architecture
     *
     * @see ResourceAttributes::HOST_ARCH
     */
    public const HOST_ARCH_S390X = 's390x';

    /**
     * 32-bit x86
     *
     * @see ResourceAttributes::HOST_ARCH
     */
    public const HOST_ARCH_X86 = 'x86';

    /**
     * Microsoft Windows
     *
     * @see ResourceAttributes::OS_TYPE
     */
    public const OS_TYPE_WINDOWS = 'windows';

    /**
     * Linux
     *
     * @see ResourceAttributes::OS_TYPE
     */
    public const OS_TYPE_LINUX = 'linux';

    /**
     * Apple Darwin
     *
     * @see ResourceAttributes::OS_TYPE
     */
    public const OS_TYPE_DARWIN = 'darwin';

    /**
     * FreeBSD
     *
     * @see ResourceAttributes::OS_TYPE
     */
    public const OS_TYPE_FREEBSD = 'freebsd';

    /**
     * NetBSD
     *
     * @see ResourceAttributes::OS_TYPE
     */
    public const OS_TYPE_NETBSD = 'netbsd';

    /**
     * OpenBSD
     *
     * @see ResourceAttributes::OS_TYPE
     */
    public const OS_TYPE_OPENBSD = 'openbsd';

    /**
     * DragonFly BSD
     *
     * @see ResourceAttributes::OS_TYPE
     */
    public const OS_TYPE_DRAGONFLYBSD = 'dragonflybsd';

    /**
     * HP-UX (Hewlett Packard Unix)
     *
     * @see ResourceAttributes::OS_TYPE
     */
    public const OS_TYPE_HPUX = 'hpux';

    /**
     * AIX (Advanced Interactive eXecutive)
     *
     * @see ResourceAttributes::OS_TYPE
     */
    public const OS_TYPE_AIX = 'aix';

    /**
     * SunOS, Oracle Solaris
     *
     * @see ResourceAttributes::OS_TYPE
     */
    public const OS_TYPE_SOLARIS = 'solaris';

    /**
     * Deprecated. Use `zos` instead.
     *
     * @see ResourceAttributes::OS_TYPE
     */
    public const OS_TYPE_Z_OS = 'z_os';

    /**
     * IBM z/OS
     *
     * @see ResourceAttributes::OS_TYPE
     */
    public const OS_TYPE_ZOS = 'zos';

    /**
     * cpp
     *
     * @see ResourceAttributes::TELEMETRY_SDK_LANGUAGE
     */
    public const TELEMETRY_SDK_LANGUAGE_CPP = 'cpp';

    /**
     * dotnet
     *
     * @see ResourceAttributes::TELEMETRY_SDK_LANGUAGE
     */
    public const TELEMETRY_SDK_LANGUAGE_DOTNET = 'dotnet';

    /**
     * erlang
     *
     * @see ResourceAttributes::TELEMETRY_SDK_LANGUAGE
     */
    public const TELEMETRY_SDK_LANGUAGE_ERLANG = 'erlang';

    /**
     * go
     *
     * @see ResourceAttributes::TELEMETRY_SDK_LANGUAGE
     */
    public const TELEMETRY_SDK_LANGUAGE_GO = 'go';

    /**
     * java
     *
     * @see ResourceAttributes::TELEMETRY_SDK_LANGUAGE
     */
    public const TELEMETRY_SDK_LANGUAGE_JAVA = 'java';

    /**
     * nodejs
     *
     * @see ResourceAttributes::TELEMETRY_SDK_LANGUAGE
     */
    public const TELEMETRY_SDK_LANGUAGE_NODEJS = 'nodejs';

    /**
     * php
     *
     * @see ResourceAttributes::TELEMETRY_SDK_LANGUAGE
     */
    public const TELEMETRY_SDK_LANGUAGE_PHP = 'php';

    /**
     * python
     *
     * @see ResourceAttributes::TELEMETRY_SDK_LANGUAGE
     */
    public const TELEMETRY_SDK_LANGUAGE_PYTHON = 'python';

    /**
     * ruby
     *
     * @see ResourceAttributes::TELEMETRY_SDK_LANGUAGE
     */
    public const TELEMETRY_SDK_LANGUAGE_RUBY = 'ruby';

    /**
     * rust
     *
     * @see ResourceAttributes::TELEMETRY_SDK_LANGUAGE
     */
    public const TELEMETRY_SDK_LANGUAGE_RUST = 'rust';

    /**
     * swift
     *
     * @see ResourceAttributes::TELEMETRY_SDK_LANGUAGE
     */
    public const TELEMETRY_SDK_LANGUAGE_SWIFT = 'swift';

    /**
     * webjs
     *
     * @see ResourceAttributes::TELEMETRY_SDK_LANGUAGE
     */
    public const TELEMETRY_SDK_LANGUAGE_WEBJS = 'webjs';

    /**
     * [branch](https://git-scm.com/docs/gitglossary#Documentation/gitglossary.txt-aiddefbranchabranch)
     *
     * @see ResourceAttributes::VCS_REF_TYPE
     */
    public const VCS_REF_TYPE_BRANCH = 'branch';

    /**
     * [tag](https://git-scm.com/docs/gitglossary#Documentation/gitglossary.txt-aiddeftagatag)
     *
     * @see ResourceAttributes::VCS_REF_TYPE
     */
    public const VCS_REF_TYPE_TAG = 'tag';

}
