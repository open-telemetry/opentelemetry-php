<?php
require 'vendor/autoload.php';

$bench = new OpenTelemetry\Tests\Benchmark\OtlpBench();
$bench->setUpGrpcHttp();
$bench->benchExportSpansViaOtlpHttp();

