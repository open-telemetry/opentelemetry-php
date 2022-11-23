<?php

declare(strict_types=1);
\OpenTelemetry\SDK\FactoryRegistry::registerTransportFactory('grpc', \OpenTelemetry\Contrib\Grpc\GrpcTransportFactory::class);
