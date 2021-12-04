<?php

declare(strict_types=1);

namespace OpenTelemetry\Experimental\NetteConfig;

use Nette\Schema\Expect;
use Nette\Schema\Processor;

/** @phan-file-suppress PhanUndeclaredClassMethod */

class ConfigBuilder
{
    //single-value env vars
    private array $single = [
        'OTEL_LOG_LEVEL' => 'log.level',
        'OTEL_PHP_LOG_DESTINATION' => 'log.destination',
        'OTEL_SERVICE_NAME' => 'service.name',
        'OTEL_TRACES_SAMPLER' => 'trace.sampler',
        'OTEL_TRACES_SAMPLER_ARG' => 'trace.samplers.traceidratio.probability',
        'OTEL_ATTRIBUTE_VALUE_LENGTH_LIMIT' => 'resource.limits.attribute_value_length',
        'OTEL_ATTRIBUTE_COUNT_LIMIT' => 'resource.limits.attribute_count',
        'OTEL_BSP_SCHEDULE_DELAY' => 'trace.processors.batch.schedule_delay',
        'OTEL_BSP_EXPORT_TIMEOUT' => 'trace.processors.batch.export_timeout',
        'OTEL_BSP_MAX_QUEUE_SIZE' => 'trace.processors.batch.max_queue_size',
        'OTEL_BSP_MAX_EXPORT_BATCH_SIZE' => 'trace.processors.batch.max_export_batch_size',
        'OTEL_SPAN_ATTRIBUTE_VALUE_LENGTH_LIMIT' => 'span.limits.attribute_value_length',
        'OTEL_SPAN_ATTRIBUTE_COUNT_LIMIT' => 'span.limits.attribute_count',
        'OTEL_SPAN_EVENT_COUNT_LIMIT' => 'span.limits.event_count',
        'OTEL_SPAN_LINK_COUNT_LIMIT' => 'span.limits.link_count',
        'OTEL_EVENT_ATTRIBUTE_COUNT_LIMIT' => 'span.limits.attribute_per_event',
        'OTEL_LINK_ATTRIBUTE_COUNT_LIMIT' => 'span.limits.attribute_per_link',
        'OTEL_EXPORTER_ZIPKIN_ENDPOINT' => 'trace.exporters.zipkin.endpoint',
        'OTEL_EXPORTER_ZIPKIN_TIMEOUT' => 'trace.exporters.zipkin.timeout',
        'OTEL_EXPORTER_OTLP_ENDPOINT' => 'trace.exporters.otlp.endpoint',
        'OTEL_EXPORTER_OTLP_TRACES_ENDPOINT' => 'trace.exporters.otlp.endpoint',
        'OTEL_EXPORTER_OTLP_INSECURE' => 'trace.exporters.otlp.insecure',
        'OTEL_EXPORTER_OTLP_SPAN_INSECURE' => 'trace.exporters.otlp.insecure',
        'OTEL_EXPORTER_OTLP_CERTIFICATE' => 'trace.exporters.otlp.certificate_file',
        'OTEL_EXPORTER_OTLP_TRACES_CERTIFICATE' => 'trace.exporters.otlp.certificate_file',
        'OTEL_EXPORTER_OTLP_HEADERS' => 'trace.exporters.otlp.headers',
        'OTEL_EXPORTER_OTLP_TRACES_HEADERS' => 'trace.exporters.otlp.headers',
        'OTEL_EXPORTER_OTLP_COMPRESSION' => 'trace.exporters.otlp.compression',
        'OTEL_EXPORTER_OTLP_TRACES_COMPRESSION' => 'trace.exporters.otlp.compression',
        'OTEL_EXPORTER_OTLP_TIMEOUT' => 'trace.exporters.otlp.timeout',
        'OTEL_EXPORTER_OTLP_TRACES_TIMEOUT' => 'trace.exporters.otlp.timeout',
        'OTEL_EXPORTER_OTLP_PROTOCOL' => 'trace.exporters.otlp.protocol',
        'OTEL_EXPORTER_OTLP_TRACES_PROTOCOL' => 'trace.exporters.otlp.protocol',
        'OTEL_EXPORTER_JAEGER_AGENT_HOST' => 'trace.exporters.jaeger.agent_host',
        'OTEL_EXPORTER_JAEGER_AGENT_PORT' => 'trace.exporters.jaeger.agent_port',
        'OTEL_EXPORTER_JAEGER_ENDPOINT' => 'trace.exporters.jaeger.endpoint',
        'OTEL_EXPORTER_JAEGER_TIMEOUT' => 'trace.exporters.jaeger.timeout',
        'OTEL_EXPORTER_JAEGER_USER' => 'trace.exporters.jaeger.user',
        'OTEL_EXPORTER_JAEGER_PASSWORD' => 'trace.exporters.jaeger.password',
        'OTEL_PROPAGATORS' => 'propagators',
        'OTEL_TRACES_EXPORTER' => 'trace.exporter',
        'OTEL_METRICS_EXPORTER' => 'metrics.exporter',
        'OTEL_PHP_TRACES_PROCESSOR' => 'trace.processor',
    ];

    public function build(array $data = []): object
    {
        //@phan-ignore PhanUndeclaredClassMethod
        $schema = Expect::structure([
            'log' => Expect::structure([
                'stream' => Expect::string('php://stdout'),
                'level' => Expect::string('info'),
            ]),
            'propagators' => Expect::string('tracecontext,baggage'),
            'service' => Expect::structure([
                'name' => Expect::string(),
            ]),
            'resource' => Expect::structure([
                'limits' => Expect::structure([
                    'attribute_count' => Expect::string()->castTo('int'),
                    'attribute_value_length' => Expect::string()->castTo('int'),
                ]),
                'attributes' => Expect::array(),
            ]),
            'trace' => Expect::structure([
                'sampler' => Expect::string('parentbased_always_on'),
                'samplers' => Expect::structure([
                    'traceidratio' => Expect::structure([
                        'probability' => Expect::string()->castTo('float'),
                    ]),
                ]),
                'processor' => Expect::string('batch'),
                'processors' => Expect::structure([
                    'batch' => Expect::structure([
                        'schedule_delay' => Expect::string()->castTo('int'),
                        'export_timeout' => Expect::string()->castTo('int'),
                        'max_queue_size' => Expect::string()->castTo('int'),
                        'max_export_batch_size' => Expect::string()->castTo('int'),
                    ]),
                    'simple' => Expect::structure([]),
                    'noop' => Expect::structure([]),
                ]),
                'exporter' => Expect::string('grpc'),
                'exporters' => Expect::structure([
                    'zipkin' => Expect::structure([
                        'endpoint' => Expect::string(),
                        'timeout' => Expect::string()->castTo('int'),
                    ]),
                    'otlp' => Expect::structure([
                        'endpoint' => Expect::string(),
                        'insecure' => Expect::anyOf('true', 'false', '0', '1'),
                        'certificate_file' => Expect::string(),
                        'headers' => Expect::string(),
                        'compression' => Expect::string(),
                        'timeout' => Expect::string()->castTo('int'),
                        'protocol' => Expect::string('http/protobuf'),
                        
                    ]),
                    'jaeger' => Expect::structure([
                        'agent_host' => Expect::string(),
                        'agent_port' => Expect::string()->castTo('int'),
                        'endpoint' => Expect::string(),
                        'timeout' => Expect::string()->castTo('int'),
                        'user' => Expect::string(),
                        'password' => Expect::string(),
                    ]),
                ]),
            ]),
            'span' => Expect::structure([
                'limits' => Expect::structure([
                    'attribute_count' => Expect::string()->castTo('int'),
                    'attribute_value_length' => Expect::string()->castTo('int'),
                    'event_count' => Expect::string()->castTo('int'),
                    'link_count' => Expect::string()->castTo('int'),
                    'attribute_per_event' => Expect::string()->castTo('int'),
                    'attribute_per_link' => Expect::string()->castTo('int'),
                ]),
            ]),
        ]);
        $env = $this->env();
        $resourceAttributes = $this->resourceAttributes();

        return (new Processor())->processMultiple($schema, [$env, $resourceAttributes, $data]);
    }

    /**
     * Retrieve all OTEL_* environment variables, and map them to a nested array
     */
    private function env(): array
    {
        $vars = array_filter(getenv(), function ($key) {
            return strpos($key, 'OTEL_') !== false;
        }, ARRAY_FILTER_USE_KEY);
        $output = [];
        foreach ($vars as $key => $val) {
            if (array_key_exists($key, $this->single) && (!empty($val))) {
                $path = $this->single[$key];
                $this->set($output, $path, $val);
            }
        }

        return $output;
    }

    /**
     * Retrieve resource attributes from env var, and organise as key-value array
     */
    private function resourceAttributes(): array
    {
        $output = [];
        $env = getenv('OTEL_RESOURCE_ATTRIBUTES');
        $path = 'resource.attributes';
        if (false === $env) {
            return [];
        }
        $values = [];
        $pairs = array_map(function ($pair) {
            return explode('=', $pair, 2);
        }, explode(',', $env));
        foreach ($pairs as $pair) {
            $values[$pair[0]] = $pair[1];
        }
        $this->set($output, $path, $values);

        return $output;
    }

    /**
     * Convert dot-notation array path to nested array
     * @example 'foo.bar.baz=bat' -> ['foo'=>['bar'=>['baz'=>'bat']]]
     */
    private function set(&$array, $key, $value): void
    {
        $keys = explode('.', $key);
        while (count($keys) > 1) {
            $key = array_shift($keys);
            if (!array_key_exists($key, $array)) {
                $array[$key] = [];
            }
            $array = &$array[$key];
        }
        $array[array_shift($keys)] = $value;
    }
}
