<?php

// DO NOT EDIT, this is an Auto-generated file from script/semantic-convention/templates/AttributeValues.php.j2

declare(strict_types=1);

namespace OpenTelemetry\SemConv;

interface ResourceAttributeValues
{
    /**
     * The URL of the OpenTelemetry schema for these keys and values.
     */
    public const SCHEMA_URL = 'https://opentelemetry.io/schemas/1.24.0';
    /**
     * @see ResourceAttributes::AWS_ECS_LAUNCHTYPE ec2
     */
    public const AWS_ECS_LAUNCHTYPE_EC2 = 'ec2';

    /**
     * @see ResourceAttributes::AWS_ECS_LAUNCHTYPE fargate
     */
    public const AWS_ECS_LAUNCHTYPE_FARGATE = 'fargate';

    /**
     * @see ResourceAttributes::CLOUD_PLATFORM Alibaba Cloud Elastic Compute Service
     */
    public const CLOUD_PLATFORM_ALIBABA_CLOUD_ECS = 'alibaba_cloud_ecs';

    /**
     * @see ResourceAttributes::CLOUD_PLATFORM Alibaba Cloud Function Compute
     */
    public const CLOUD_PLATFORM_ALIBABA_CLOUD_FC = 'alibaba_cloud_fc';

    /**
     * @see ResourceAttributes::CLOUD_PLATFORM Red Hat OpenShift on Alibaba Cloud
     */
    public const CLOUD_PLATFORM_ALIBABA_CLOUD_OPENSHIFT = 'alibaba_cloud_openshift';

    /**
     * @see ResourceAttributes::CLOUD_PLATFORM AWS Elastic Compute Cloud
     */
    public const CLOUD_PLATFORM_AWS_EC2 = 'aws_ec2';

    /**
     * @see ResourceAttributes::CLOUD_PLATFORM AWS Elastic Container Service
     */
    public const CLOUD_PLATFORM_AWS_ECS = 'aws_ecs';

    /**
     * @see ResourceAttributes::CLOUD_PLATFORM AWS Elastic Kubernetes Service
     */
    public const CLOUD_PLATFORM_AWS_EKS = 'aws_eks';

    /**
     * @see ResourceAttributes::CLOUD_PLATFORM AWS Lambda
     */
    public const CLOUD_PLATFORM_AWS_LAMBDA = 'aws_lambda';

    /**
     * @see ResourceAttributes::CLOUD_PLATFORM AWS Elastic Beanstalk
     */
    public const CLOUD_PLATFORM_AWS_ELASTIC_BEANSTALK = 'aws_elastic_beanstalk';

    /**
     * @see ResourceAttributes::CLOUD_PLATFORM AWS App Runner
     */
    public const CLOUD_PLATFORM_AWS_APP_RUNNER = 'aws_app_runner';

    /**
     * @see ResourceAttributes::CLOUD_PLATFORM Red Hat OpenShift on AWS (ROSA)
     */
    public const CLOUD_PLATFORM_AWS_OPENSHIFT = 'aws_openshift';

    /**
     * @see ResourceAttributes::CLOUD_PLATFORM Azure Virtual Machines
     */
    public const CLOUD_PLATFORM_AZURE_VM = 'azure_vm';

    /**
     * @see ResourceAttributes::CLOUD_PLATFORM Azure Container Instances
     */
    public const CLOUD_PLATFORM_AZURE_CONTAINER_INSTANCES = 'azure_container_instances';

    /**
     * @see ResourceAttributes::CLOUD_PLATFORM Azure Kubernetes Service
     */
    public const CLOUD_PLATFORM_AZURE_AKS = 'azure_aks';

    /**
     * @see ResourceAttributes::CLOUD_PLATFORM Azure Functions
     */
    public const CLOUD_PLATFORM_AZURE_FUNCTIONS = 'azure_functions';

    /**
     * @see ResourceAttributes::CLOUD_PLATFORM Azure App Service
     */
    public const CLOUD_PLATFORM_AZURE_APP_SERVICE = 'azure_app_service';

    /**
     * @see ResourceAttributes::CLOUD_PLATFORM Azure Red Hat OpenShift
     */
    public const CLOUD_PLATFORM_AZURE_OPENSHIFT = 'azure_openshift';

    /**
     * @see ResourceAttributes::CLOUD_PLATFORM Google Bare Metal Solution (BMS)
     */
    public const CLOUD_PLATFORM_GCP_BARE_METAL_SOLUTION = 'gcp_bare_metal_solution';

    /**
     * @see ResourceAttributes::CLOUD_PLATFORM Google Cloud Compute Engine (GCE)
     */
    public const CLOUD_PLATFORM_GCP_COMPUTE_ENGINE = 'gcp_compute_engine';

    /**
     * @see ResourceAttributes::CLOUD_PLATFORM Google Cloud Run
     */
    public const CLOUD_PLATFORM_GCP_CLOUD_RUN = 'gcp_cloud_run';

    /**
     * @see ResourceAttributes::CLOUD_PLATFORM Google Cloud Kubernetes Engine (GKE)
     */
    public const CLOUD_PLATFORM_GCP_KUBERNETES_ENGINE = 'gcp_kubernetes_engine';

    /**
     * @see ResourceAttributes::CLOUD_PLATFORM Google Cloud Functions (GCF)
     */
    public const CLOUD_PLATFORM_GCP_CLOUD_FUNCTIONS = 'gcp_cloud_functions';

    /**
     * @see ResourceAttributes::CLOUD_PLATFORM Google Cloud App Engine (GAE)
     */
    public const CLOUD_PLATFORM_GCP_APP_ENGINE = 'gcp_app_engine';

    /**
     * @see ResourceAttributes::CLOUD_PLATFORM Red Hat OpenShift on Google Cloud
     */
    public const CLOUD_PLATFORM_GCP_OPENSHIFT = 'gcp_openshift';

    /**
     * @see ResourceAttributes::CLOUD_PLATFORM Red Hat OpenShift on IBM Cloud
     */
    public const CLOUD_PLATFORM_IBM_CLOUD_OPENSHIFT = 'ibm_cloud_openshift';

    /**
     * @see ResourceAttributes::CLOUD_PLATFORM Tencent Cloud Cloud Virtual Machine (CVM)
     */
    public const CLOUD_PLATFORM_TENCENT_CLOUD_CVM = 'tencent_cloud_cvm';

    /**
     * @see ResourceAttributes::CLOUD_PLATFORM Tencent Cloud Elastic Kubernetes Service (EKS)
     */
    public const CLOUD_PLATFORM_TENCENT_CLOUD_EKS = 'tencent_cloud_eks';

    /**
     * @see ResourceAttributes::CLOUD_PLATFORM Tencent Cloud Serverless Cloud Function (SCF)
     */
    public const CLOUD_PLATFORM_TENCENT_CLOUD_SCF = 'tencent_cloud_scf';

    /**
     * @see ResourceAttributes::CLOUD_PROVIDER Alibaba Cloud
     */
    public const CLOUD_PROVIDER_ALIBABA_CLOUD = 'alibaba_cloud';

    /**
     * @see ResourceAttributes::CLOUD_PROVIDER Amazon Web Services
     */
    public const CLOUD_PROVIDER_AWS = 'aws';

    /**
     * @see ResourceAttributes::CLOUD_PROVIDER Microsoft Azure
     */
    public const CLOUD_PROVIDER_AZURE = 'azure';

    /**
     * @see ResourceAttributes::CLOUD_PROVIDER Google Cloud Platform
     */
    public const CLOUD_PROVIDER_GCP = 'gcp';

    /**
     * @see ResourceAttributes::CLOUD_PROVIDER Heroku Platform as a Service
     */
    public const CLOUD_PROVIDER_HEROKU = 'heroku';

    /**
     * @see ResourceAttributes::CLOUD_PROVIDER IBM Cloud
     */
    public const CLOUD_PROVIDER_IBM_CLOUD = 'ibm_cloud';

    /**
     * @see ResourceAttributes::CLOUD_PROVIDER Tencent Cloud
     */
    public const CLOUD_PROVIDER_TENCENT_CLOUD = 'tencent_cloud';

    /**
     * @see ResourceAttributes::HOST_ARCH AMD64
     */
    public const HOST_ARCH_AMD64 = 'amd64';

    /**
     * @see ResourceAttributes::HOST_ARCH ARM32
     */
    public const HOST_ARCH_ARM32 = 'arm32';

    /**
     * @see ResourceAttributes::HOST_ARCH ARM64
     */
    public const HOST_ARCH_ARM64 = 'arm64';

    /**
     * @see ResourceAttributes::HOST_ARCH Itanium
     */
    public const HOST_ARCH_IA64 = 'ia64';

    /**
     * @see ResourceAttributes::HOST_ARCH 32-bit PowerPC
     */
    public const HOST_ARCH_PPC32 = 'ppc32';

    /**
     * @see ResourceAttributes::HOST_ARCH 64-bit PowerPC
     */
    public const HOST_ARCH_PPC64 = 'ppc64';

    /**
     * @see ResourceAttributes::HOST_ARCH IBM z/Architecture
     */
    public const HOST_ARCH_S390X = 's390x';

    /**
     * @see ResourceAttributes::HOST_ARCH 32-bit x86
     */
    public const HOST_ARCH_X86 = 'x86';

    /**
     * @see ResourceAttributes::OS_TYPE Microsoft Windows
     */
    public const OS_TYPE_WINDOWS = 'windows';

    /**
     * @see ResourceAttributes::OS_TYPE Linux
     */
    public const OS_TYPE_LINUX = 'linux';

    /**
     * @see ResourceAttributes::OS_TYPE Apple Darwin
     */
    public const OS_TYPE_DARWIN = 'darwin';

    /**
     * @see ResourceAttributes::OS_TYPE FreeBSD
     */
    public const OS_TYPE_FREEBSD = 'freebsd';

    /**
     * @see ResourceAttributes::OS_TYPE NetBSD
     */
    public const OS_TYPE_NETBSD = 'netbsd';

    /**
     * @see ResourceAttributes::OS_TYPE OpenBSD
     */
    public const OS_TYPE_OPENBSD = 'openbsd';

    /**
     * @see ResourceAttributes::OS_TYPE DragonFly BSD
     */
    public const OS_TYPE_DRAGONFLYBSD = 'dragonflybsd';

    /**
     * @see ResourceAttributes::OS_TYPE HP-UX (Hewlett Packard Unix)
     */
    public const OS_TYPE_HPUX = 'hpux';

    /**
     * @see ResourceAttributes::OS_TYPE AIX (Advanced Interactive eXecutive)
     */
    public const OS_TYPE_AIX = 'aix';

    /**
     * @see ResourceAttributes::OS_TYPE SunOS, Oracle Solaris
     */
    public const OS_TYPE_SOLARIS = 'solaris';

    /**
     * @see ResourceAttributes::OS_TYPE IBM z/OS
     */
    public const OS_TYPE_Z_OS = 'z_os';

    /**
     * @see ResourceAttributes::TELEMETRY_SDK_LANGUAGE cpp
     */
    public const TELEMETRY_SDK_LANGUAGE_CPP = 'cpp';

    /**
     * @see ResourceAttributes::TELEMETRY_SDK_LANGUAGE dotnet
     */
    public const TELEMETRY_SDK_LANGUAGE_DOTNET = 'dotnet';

    /**
     * @see ResourceAttributes::TELEMETRY_SDK_LANGUAGE erlang
     */
    public const TELEMETRY_SDK_LANGUAGE_ERLANG = 'erlang';

    /**
     * @see ResourceAttributes::TELEMETRY_SDK_LANGUAGE go
     */
    public const TELEMETRY_SDK_LANGUAGE_GO = 'go';

    /**
     * @see ResourceAttributes::TELEMETRY_SDK_LANGUAGE java
     */
    public const TELEMETRY_SDK_LANGUAGE_JAVA = 'java';

    /**
     * @see ResourceAttributes::TELEMETRY_SDK_LANGUAGE nodejs
     */
    public const TELEMETRY_SDK_LANGUAGE_NODEJS = 'nodejs';

    /**
     * @see ResourceAttributes::TELEMETRY_SDK_LANGUAGE php
     */
    public const TELEMETRY_SDK_LANGUAGE_PHP = 'php';

    /**
     * @see ResourceAttributes::TELEMETRY_SDK_LANGUAGE python
     */
    public const TELEMETRY_SDK_LANGUAGE_PYTHON = 'python';

    /**
     * @see ResourceAttributes::TELEMETRY_SDK_LANGUAGE ruby
     */
    public const TELEMETRY_SDK_LANGUAGE_RUBY = 'ruby';

    /**
     * @see ResourceAttributes::TELEMETRY_SDK_LANGUAGE rust
     */
    public const TELEMETRY_SDK_LANGUAGE_RUST = 'rust';

    /**
     * @see ResourceAttributes::TELEMETRY_SDK_LANGUAGE swift
     */
    public const TELEMETRY_SDK_LANGUAGE_SWIFT = 'swift';

    /**
     * @see ResourceAttributes::TELEMETRY_SDK_LANGUAGE webjs
     */
    public const TELEMETRY_SDK_LANGUAGE_WEBJS = 'webjs';
}
