<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\Newrelic;

use Http\Discovery\HttpClientDiscovery;
use Http\Discovery\Psr17FactoryDiscovery;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Usage:
 * >>> Pass defaults to the factory.
 * $factory = Factory::create(['service_name' => 'foo']);
 * >>> Create exporter with values resolved at runtime.
 * $exporter = $factory->build(['endpoint_url' => 'http://example.com/foo', 'license_key' => 'abc123', ])
 */
class Factory
{
    private const OPT_SERVICE_NAME = 'service_name';
    private const OPT_ENDPOINT_URL = 'endpoint_url';
    private const OPT_LICENSE_KEY = 'license_key';
    private const OPT_CLIENT = 'client';
    private const OPT_REQUEST_FACTORY = 'request_factory';
    private const OPT_STREAM_FACTORY = 'stream_factory';
    private const OPT_SPAN_CONVERTER = 'span_converter';
    private const OPT_DATA_FORMAT_VERSION = 'data_format_version';
    private const OPTIONS = [
        // Just used Factory::OPT_SERVICE_NAME, etc. to temporarily make my IDE happy.
        // It has a bug atm and marks self::OPT_SERVICE_NAME as an error.
        Factory::OPT_SERVICE_NAME,
        Factory::OPT_ENDPOINT_URL,
        Factory::OPT_LICENSE_KEY,
        Factory::OPT_CLIENT,
        Factory::OPT_REQUEST_FACTORY,
        Factory::OPT_STREAM_FACTORY,
        Factory::OPT_SPAN_CONVERTER,
        Factory::OPT_DATA_FORMAT_VERSION,
    ];
    private const REQUIRED_OPTIONS = [
        Factory::OPT_SERVICE_NAME,
        Factory::OPT_ENDPOINT_URL,
        Factory::OPT_LICENSE_KEY,
    ];

    private OptionsResolver $resolver;

    public function __construct(array $defaultOptions = [], ?OptionsResolver $resolver = null)
    {
        $this->resolver = $resolver ?? new OptionsResolver();
        $this->configureOptions($defaultOptions);
    }

    public function build(array $options = []): Exporter
    {
        $options = $this->resolver->resolve($options);

        return new Exporter(
            $options[Factory::OPT_SERVICE_NAME],
            $options[Factory::OPT_ENDPOINT_URL],
            $options[Factory::OPT_LICENSE_KEY],
            $options[Factory::OPT_CLIENT],
            $options[Factory::OPT_REQUEST_FACTORY],
            $options[Factory::OPT_STREAM_FACTORY],
            $options[Factory::OPT_SPAN_CONVERTER],
            $options[Factory::OPT_DATA_FORMAT_VERSION],
        );
    }

    public function configureOptions(array $options = [])
    {
        foreach (Factory::getOptions() as $option) {
            $this->resolver->define($option);
        }
        $this->resolver->setDefaults(array_merge([
            Factory::OPT_CLIENT  => fn (Options $options) => HttpClientDiscovery::find(),
            Factory::OPT_REQUEST_FACTORY => fn (Options $options) => Psr17FactoryDiscovery::findRequestFactory(),
            Factory::OPT_STREAM_FACTORY => fn (Options $options) => Psr17FactoryDiscovery::findStreamFactory(),
            Factory::OPT_SPAN_CONVERTER => null,
        ], $options));
        $this->resolver->setRequired(Factory::REQUIRED_OPTIONS);
    }

    public static function getOptions(): array
    {
        return Factory::OPTIONS;
    }

    public static function getRequiredOptions(): array
    {
        return Factory::REQUIRED_OPTIONS;
    }
}
