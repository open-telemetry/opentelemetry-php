<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\Jaeger;

use OpenTelemetry\Contrib\Zipkin;
use OpenTelemetry\Sdk\Trace;

class Exporter extends Zipkin\Exporter implements Trace\Exporter
{
}
