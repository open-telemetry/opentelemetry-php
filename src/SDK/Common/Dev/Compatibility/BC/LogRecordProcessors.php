<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Dev\Compatibility\BC;

use OpenTelemetry\SDK\Logs\Processor\BatchLogRecordProcessor;
use OpenTelemetry\SDK\Logs\Processor\SimpleLogRecordProcessor;

const OpenTelemetry_SDK_Logs_Processor_BatchLogsProcessor = '\OpenTelemetry\SDK\Logs\Processor\BatchLogsProcessor';
const OpenTelemetry_SDK_Logs_Processor_SimpleLogsProcessor = '\OpenTelemetry\SDK\Logs\Processor\SimpleLogsProcessor';

const LR_MAP = [
    OpenTelemetry_SDK_Logs_Processor_BatchLogsProcessor => BatchLogRecordProcessor::class,
    OpenTelemetry_SDK_Logs_Processor_SimpleLogsProcessor => SimpleLogRecordProcessor::class,
];

foreach (LR_MAP as $old => $new) {
    if (!class_exists($old, false)) {
        class_alias($new, $old);
    }
}
