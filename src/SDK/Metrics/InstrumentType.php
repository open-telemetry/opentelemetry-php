<?php declare(strict_types=1);
namespace OpenTelemetry\SDK\Metrics;

enum InstrumentType {

    case Counter;
    case UpDownCounter;
    case Histogram;

    case AsynchronousCounter;
    case AsynchronousUpDownCounter;
    case AsynchronousGauge;
}
