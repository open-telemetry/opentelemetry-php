<?php

declare(strict_types=1);
\OpenTelemetry\SDK\Registry::registerTransportFactory('grpc', \OpenTelemetry\Contrib\Grpc\GrpcTransportFactory::class);
