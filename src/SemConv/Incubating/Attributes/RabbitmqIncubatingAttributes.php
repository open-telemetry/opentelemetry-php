<?php

// DO NOT EDIT, this is an Auto-generated file from script/semantic-conventions

declare(strict_types=1);

namespace OpenTelemetry\SemConv\Incubating\Attributes;

/**
 * Semantic attributes and corresponding values for rabbitmq.
 * @see https://opentelemetry.io/docs/specs/semconv/registry/attributes/rabbitmq/
 * May contain @experimental Semantic Conventions which may change or be removed in the future.
 */
interface RabbitmqIncubatingAttributes
{
    /**
     * RabbitMQ message routing key.
     *
     * @experimental
     */
    public const RABBITMQ_DESTINATION_ROUTING_KEY = 'rabbitmq.destination.routing_key';

    /**
     * RabbitMQ message delivery tag
     *
     * @experimental
     */
    public const RABBITMQ_MESSAGE_DELIVERY_TAG = 'rabbitmq.message.delivery_tag';

}
