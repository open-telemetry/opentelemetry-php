<?php

declare(strict_types=1);
require __DIR__ . '/../vendor/autoload.php';

use OpenTelemetry\Sdk\Internal\Duration;
use OpenTelemetry\Sdk\Internal\Time;
use OpenTelemetry\Sdk\Internal\Timestamp;

/**
 * Example of \OpenTelemetry\Sdk\Internal\Timestamp and \OpenTelemetry\Sdk\Internal\Duration usage
 */

// current timestamp
$timestampNow = Timestamp::now();

// timestamp ar current `time()` in seconds
$timestampAt = Timestamp::at(time() * Time::SECOND);

// Duration of 5 ms
$duration5Ms = Duration::of(5 * Time::MILLISECOND);

// Duration between two timestamps
$duration24h = Duration::between(
    Timestamp::at(strtotime('-24 hours') * Time::SECOND),
    Timestamp::at(strtotime('now') * Time::SECOND)
);

// Duration time in seconds
$seconds = $duration24h->to(Time::SECOND);
