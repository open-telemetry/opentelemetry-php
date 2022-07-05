<?php declare(strict_types=1);

class_alias(OpenTelemetry\Context\ContextStorageInterface::class, OpenTelemetry\ContextStorage::class);

class_alias(OpenTelemetry\SDK\Resource\ResourceInfo::class, OpenTelemetry\SDK\Resource::class);

class_alias(OpenTelemetry\SDK\Common\Attribute\AttributesInterface::class, OpenTelemetry\SDK\Attributes::class);
class_alias(OpenTelemetry\SDK\Common\Attribute\AttributesFactoryInterface::class, OpenTelemetry\SDK\AttributesFactory::class);
class_alias(OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeInterface::class, OpenTelemetry\SDK\InstrumentationScope::class);
class_alias(OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeFactoryInterface::class, OpenTelemetry\SDK\InstrumentationScopeFactory::class);
class_alias(OpenTelemetry\SDK\Common\Time\ClockInterface::class, OpenTelemetry\SDK\Clock::class);
