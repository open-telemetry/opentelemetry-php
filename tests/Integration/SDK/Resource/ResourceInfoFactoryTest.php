<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Integration\SDK\Resource;

use Composer\InstalledVersions;
use OpenTelemetry\SDK\Resource\ResourceInfoFactory;
use OpenTelemetry\SemConv\ResourceAttributes;
use OpenTelemetry\Tests\TestState;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

#[CoversNothing]
class ResourceInfoFactoryTest extends TestCase
{
    use TestState;

    public function test_all_default_resources(): void
    {
        $resource = ResourceInfoFactory::defaultResource();

        $this->assertStringMatchesFormat('https://opentelemetry.io/schemas/%d.%d.%d', $resource->getSchemaUrl() ?? '');

        $this->assertNotNull($resource->getAttributes()->get(ResourceAttributes::HOST_NAME));
        $this->assertNotNull($resource->getAttributes()->get(ResourceAttributes::HOST_ARCH));
        $this->assertNotNull($resource->getAttributes()->get(ResourceAttributes::OS_TYPE));
        $this->assertNotNull($resource->getAttributes()->get(ResourceAttributes::OS_DESCRIPTION));
        $this->assertNotNull($resource->getAttributes()->get(ResourceAttributes::OS_NAME));
        $this->assertNotNull($resource->getAttributes()->get(ResourceAttributes::OS_VERSION));
        $this->assertNotNull($resource->getAttributes()->get(ResourceAttributes::PROCESS_PID));
        $this->assertNotNull($resource->getAttributes()->get(ResourceAttributes::PROCESS_EXECUTABLE_PATH));
        $this->assertNotNull($resource->getAttributes()->get(ResourceAttributes::PROCESS_COMMAND));
        $this->assertNotNull($resource->getAttributes()->get(ResourceAttributes::PROCESS_COMMAND_ARGS));
        $this->assertNotNull($resource->getAttributes()->get(ResourceAttributes::PROCESS_RUNTIME_NAME));
        $this->assertNotNull($resource->getAttributes()->get(ResourceAttributes::PROCESS_RUNTIME_VERSION));
        $this->assertNotNull($resource->getAttributes()->get(ResourceAttributes::TELEMETRY_SDK_NAME));
        $this->assertNotNull($resource->getAttributes()->get(ResourceAttributes::TELEMETRY_SDK_LANGUAGE));
        $this->assertNotNull($resource->getAttributes()->get(ResourceAttributes::TELEMETRY_SDK_VERSION));
        $this->assertNotNull($resource->getAttributes()->get(ResourceAttributes::SERVICE_NAME));

        $this->assertEquals('opentelemetry', $resource->getAttributes()->get(ResourceAttributes::TELEMETRY_SDK_NAME));
        $this->assertEquals('php', $resource->getAttributes()->get(ResourceAttributes::TELEMETRY_SDK_LANGUAGE));
        $this->assertEquals('unknown_service:php', $resource->getAttributes()->get(ResourceAttributes::SERVICE_NAME));
    }

    public function test_detector_priority(): void
    {
        $this->setEnvironmentVariable('OTEL_SERVICE_NAME', 'test-service');

        $resource = ResourceInfoFactory::defaultResource();

        $this->assertEquals('test-service', $resource->getAttributes()->get(ResourceAttributes::SERVICE_NAME));
    }

    public function test_none_default_resources(): void
    {
        $this->setEnvironmentVariable('OTEL_PHP_DETECTORS', 'none');

        $resource = ResourceInfoFactory::defaultResource();

        $this->assertNotNull($resource->getSchemaUrl());

        $this->assertNull($resource->getAttributes()->get(ResourceAttributes::HOST_NAME));
        $this->assertNull($resource->getAttributes()->get(ResourceAttributes::HOST_ARCH));
        $this->assertNull($resource->getAttributes()->get(ResourceAttributes::OS_TYPE));
        $this->assertNull($resource->getAttributes()->get(ResourceAttributes::OS_DESCRIPTION));
        $this->assertNull($resource->getAttributes()->get(ResourceAttributes::OS_NAME));
        $this->assertNull($resource->getAttributes()->get(ResourceAttributes::OS_VERSION));
        $this->assertNull($resource->getAttributes()->get(ResourceAttributes::PROCESS_PID));
        $this->assertNull($resource->getAttributes()->get(ResourceAttributes::PROCESS_EXECUTABLE_PATH));
        $this->assertNull($resource->getAttributes()->get(ResourceAttributes::PROCESS_COMMAND));
        $this->assertNull($resource->getAttributes()->get(ResourceAttributes::PROCESS_COMMAND_ARGS));
        $this->assertNull($resource->getAttributes()->get(ResourceAttributes::PROCESS_RUNTIME_NAME));
        $this->assertNull($resource->getAttributes()->get(ResourceAttributes::PROCESS_RUNTIME_VERSION));
        $this->assertNotNull($resource->getAttributes()->get(ResourceAttributes::TELEMETRY_SDK_NAME));
        $this->assertNotNull($resource->getAttributes()->get(ResourceAttributes::TELEMETRY_SDK_LANGUAGE));
        $this->assertNotNull($resource->getAttributes()->get(ResourceAttributes::TELEMETRY_SDK_VERSION));
        $this->assertNotNull($resource->getAttributes()->get(ResourceAttributes::SERVICE_NAME));
        $this->assertNull($resource->getAttributes()->get(ResourceAttributes::SERVICE_VERSION));
    }

    public function test_env_default_resources(): void
    {
        $this->setEnvironmentVariable('OTEL_PHP_DETECTORS', 'env');
        $this->setEnvironmentVariable('OTEL_SERVICE_NAME', 'test-service');

        $resource = ResourceInfoFactory::defaultResource();

        $this->assertStringMatchesFormat('https://opentelemetry.io/schemas/%d.%d.%d', $resource->getSchemaUrl() ?? '');

        $this->assertNull($resource->getAttributes()->get(ResourceAttributes::HOST_NAME));
        $this->assertNull($resource->getAttributes()->get(ResourceAttributes::HOST_ARCH));
        $this->assertNull($resource->getAttributes()->get(ResourceAttributes::OS_TYPE));
        $this->assertNull($resource->getAttributes()->get(ResourceAttributes::OS_DESCRIPTION));
        $this->assertNull($resource->getAttributes()->get(ResourceAttributes::OS_NAME));
        $this->assertNull($resource->getAttributes()->get(ResourceAttributes::OS_VERSION));
        $this->assertNull($resource->getAttributes()->get(ResourceAttributes::PROCESS_PID));
        $this->assertNull($resource->getAttributes()->get(ResourceAttributes::PROCESS_EXECUTABLE_PATH));
        $this->assertNull($resource->getAttributes()->get(ResourceAttributes::PROCESS_COMMAND));
        $this->assertNull($resource->getAttributes()->get(ResourceAttributes::PROCESS_COMMAND_ARGS));
        $this->assertNull($resource->getAttributes()->get(ResourceAttributes::PROCESS_RUNTIME_NAME));
        $this->assertNull($resource->getAttributes()->get(ResourceAttributes::PROCESS_RUNTIME_VERSION));
        $this->assertNotNull($resource->getAttributes()->get(ResourceAttributes::TELEMETRY_SDK_NAME));
        $this->assertNotNull($resource->getAttributes()->get(ResourceAttributes::TELEMETRY_SDK_LANGUAGE));
        $this->assertNotNull($resource->getAttributes()->get(ResourceAttributes::TELEMETRY_SDK_VERSION));

        $this->assertEquals('test-service', $resource->getAttributes()->get(ResourceAttributes::SERVICE_NAME));
    }

    public function test_os_and_host_default_resources(): void
    {
        $this->setEnvironmentVariable('OTEL_PHP_DETECTORS', 'os,host');

        $resource = ResourceInfoFactory::defaultResource();

        $this->assertStringMatchesFormat('https://opentelemetry.io/schemas/%d.%d.%d', $resource->getSchemaUrl() ?? '');

        $this->assertNotNull($resource->getAttributes()->get(ResourceAttributes::HOST_NAME));
        $this->assertNotNull($resource->getAttributes()->get(ResourceAttributes::HOST_ARCH));
        $this->assertNotNull($resource->getAttributes()->get(ResourceAttributes::OS_TYPE));
        $this->assertNotNull($resource->getAttributes()->get(ResourceAttributes::OS_DESCRIPTION));
        $this->assertNotNull($resource->getAttributes()->get(ResourceAttributes::OS_NAME));
        $this->assertNotNull($resource->getAttributes()->get(ResourceAttributes::OS_VERSION));
        $this->assertNull($resource->getAttributes()->get(ResourceAttributes::PROCESS_PID));
        $this->assertNull($resource->getAttributes()->get(ResourceAttributes::PROCESS_EXECUTABLE_PATH));
        $this->assertNull($resource->getAttributes()->get(ResourceAttributes::PROCESS_COMMAND));
        $this->assertNull($resource->getAttributes()->get(ResourceAttributes::PROCESS_COMMAND_ARGS));
        $this->assertNull($resource->getAttributes()->get(ResourceAttributes::PROCESS_RUNTIME_NAME));
        $this->assertNull($resource->getAttributes()->get(ResourceAttributes::PROCESS_RUNTIME_VERSION));
        $this->assertNotNull($resource->getAttributes()->get(ResourceAttributes::TELEMETRY_SDK_NAME));
        $this->assertNotNull($resource->getAttributes()->get(ResourceAttributes::TELEMETRY_SDK_LANGUAGE));
        $this->assertNotNull($resource->getAttributes()->get(ResourceAttributes::TELEMETRY_SDK_VERSION));
        $this->assertNotNull($resource->getAttributes()->get(ResourceAttributes::SERVICE_NAME));
    }

    public function test_process_and_process_runtime_default_resources(): void
    {
        $this->setEnvironmentVariable('OTEL_PHP_DETECTORS', 'process,process_runtime');

        $resource = ResourceInfoFactory::defaultResource();

        $this->assertStringMatchesFormat('https://opentelemetry.io/schemas/%d.%d.%d', $resource->getSchemaUrl() ?? '');

        $this->assertNull($resource->getAttributes()->get(ResourceAttributes::HOST_NAME));
        $this->assertNull($resource->getAttributes()->get(ResourceAttributes::HOST_ARCH));
        $this->assertNull($resource->getAttributes()->get(ResourceAttributes::OS_TYPE));
        $this->assertNull($resource->getAttributes()->get(ResourceAttributes::OS_DESCRIPTION));
        $this->assertNull($resource->getAttributes()->get(ResourceAttributes::OS_NAME));
        $this->assertNull($resource->getAttributes()->get(ResourceAttributes::OS_VERSION));
        $this->assertNotNull($resource->getAttributes()->get(ResourceAttributes::PROCESS_PID));
        $this->assertNotNull($resource->getAttributes()->get(ResourceAttributes::PROCESS_EXECUTABLE_PATH));
        $this->assertNotNull($resource->getAttributes()->get(ResourceAttributes::PROCESS_COMMAND));
        $this->assertNotNull($resource->getAttributes()->get(ResourceAttributes::PROCESS_COMMAND_ARGS));
        $this->assertNotNull($resource->getAttributes()->get(ResourceAttributes::PROCESS_RUNTIME_NAME));
        $this->assertNotNull($resource->getAttributes()->get(ResourceAttributes::PROCESS_RUNTIME_VERSION));
        $this->assertNotNull($resource->getAttributes()->get(ResourceAttributes::TELEMETRY_SDK_NAME));
        $this->assertNotNull($resource->getAttributes()->get(ResourceAttributes::TELEMETRY_SDK_LANGUAGE));
        $this->assertNotNull($resource->getAttributes()->get(ResourceAttributes::TELEMETRY_SDK_VERSION));
        $this->assertNotNull($resource->getAttributes()->get(ResourceAttributes::SERVICE_NAME));
    }

    public function test_composer_default_resources(): void
    {
        $this->setEnvironmentVariable('OTEL_PHP_DETECTORS', 'composer');

        $resource = ResourceInfoFactory::defaultResource();

        $this->assertStringMatchesFormat('https://opentelemetry.io/schemas/%d.%d.%d', $resource->getSchemaUrl() ?? '');

        $this->assertEquals('open-telemetry/opentelemetry', $resource->getAttributes()->get(ResourceAttributes::SERVICE_NAME));
        $this->assertEquals(InstalledVersions::getRootPackage()['pretty_version'], $resource->getAttributes()->get(ResourceAttributes::SERVICE_VERSION));
    }

    public function test_env_doesnt_clobber_service(): void
    {
        //service.name should be provided by mandatory Service detector, and should not be clobbered by environment detector
        $this->setEnvironmentVariable('OTEL_SERVICE_NAME', 'test-service');
        $this->setEnvironmentVariable('OTEL_RESOURCE_ATTRIBUTES', 'service.name=ignore-me');
        $this->setEnvironmentVariable('OTEL_PHP_DETECTORS', 'env');

        $resource = ResourceInfoFactory::defaultResource();

        $this->assertEquals('test-service', $resource->getAttributes()->get(ResourceAttributes::SERVICE_NAME));
    }
}
