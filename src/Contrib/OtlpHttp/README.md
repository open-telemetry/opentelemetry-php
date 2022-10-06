# OpenTelemetry HTTP Exporter

OTLP HTTP exporter for OpenTelemetry.

## Usage

See https://github.com/open-telemetry/opentelemetry-php/blob/main/examples/traces/features/exporters/otlp_http.php

```php
$transport = (new \OpenTelemetry\Contrib\OtlpHttp\OtlpHttpTransportFactory())->create('http://collector:4318');
$exporter = new \OpenTelemetry\Contrib\Otlp\Exporter($transport);
```