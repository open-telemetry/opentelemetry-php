<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Dev\Compatibility\BC;

use OpenTelemetry\API\Globals;
use OpenTelemetry\API\Instrumentation\CachedInstrumentation;
use OpenTelemetry\API\Instrumentation\Configurator;
use OpenTelemetry\API\Instrumentation\ContextKeys;
use OpenTelemetry\API\Instrumentation\InstrumentationInterface;
use OpenTelemetry\API\Instrumentation\InstrumentationTrait;
use OpenTelemetry\API\LoggerHolder;
use OpenTelemetry\API\Signals;

/**
 * @codeCoverageIgnoreStart
 */
const OpenTelemetry_API_Common_Instrumentation_CachedInstrumentation = 'OpenTelemetry\API\Common\Instrumentation\CachedInstrumentation.php';
const OpenTelemetry_API_Common_Instrumentation_Configurator = 'OpenTelemetry\API\Common\Instrumentation\Configurator.php';
const OpenTelemetry_API_Common_Instrumentation_ContextKeys = 'OpenTelemetry\API\Common\Instrumentation\ContextKeys.php';
const OpenTelemetry_API_Common_Instrumentation_Globals = 'OpenTelemetry\API\Common\Instrumentation\Globals.php';
const OpenTelemetry_API_Common_Instrumentation_InstrumentationInterface = 'OpenTelemetry\API\Common\Instrumentation\InstrumentationInterface.php';
const OpenTelemetry_API_Common_Instrumentation_InstrumentationTrait = 'OpenTelemetry\API\Common\Instrumentation\InstrumentationTrait.php';
const OpenTelemetry_API_Common_Log_LoggerHolder = 'OpenTelemetry\API\LoggerHolder.php';
const OpenTelemetry_API_Common_Signal_Signals = 'OpenTelemetry\API\Common\Signal\Signals.php';

const MAP = [
    OpenTelemetry_API_Common_Instrumentation_CachedInstrumentation => CachedInstrumentation::class,
    OpenTelemetry_API_Common_Instrumentation_Configurator => Configurator::class,
    OpenTelemetry_API_Common_Instrumentation_ContextKeys => ContextKeys::class,
    OpenTelemetry_API_Common_Instrumentation_Globals => Globals::class,
    OpenTelemetry_API_Common_Instrumentation_InstrumentationInterface => InstrumentationInterface::class,
    OpenTelemetry_API_Common_Instrumentation_InstrumentationTrait => InstrumentationTrait::class,
    OpenTelemetry_API_Common_Log_LoggerHolder => LoggerHolder::class,
    OpenTelemetry_API_Common_Signal_Signals => Signals::class,
];

foreach (MAP as $old => $new) {
    if (!class_exists($old, false)) {
        class_alias($new, $old);
    }
}

/**
 * @codeCoverageIgnoreEnd
 */
