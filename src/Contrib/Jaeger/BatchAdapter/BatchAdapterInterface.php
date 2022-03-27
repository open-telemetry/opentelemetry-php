<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\Jaeger\BatchAdapter;

use Thrift\Protocol\TProtocol;

interface BatchAdapterInterface
{
    public function write(TProtocol $output): void;
}
