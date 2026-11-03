<?php

// DO NOT EDIT, this is an Auto-generated file from script/semantic-conventions

declare(strict_types=1);

namespace OpenTelemetry\SemConv\Incubating\Attributes;

/**
 * Semantic attributes and corresponding values for rocketmq.
 * @see https://opentelemetry.io/docs/specs/semconv/registry/attributes/rocketmq/
 * May contain @experimental Semantic Conventions which may change or be removed in the future.
 */
interface RocketmqIncubatingAttributes
{
    /**
     * Model of message consumption. This only applies to consumer spans.
     *
     * @experimental
     */
    public const ROCKETMQ_CONSUMPTION_MODEL = 'rocketmq.consumption_model';

    /**
     * Clustering consumption model
     * @experimental
     */
    public const ROCKETMQ_CONSUMPTION_MODEL_VALUE_CLUSTERING = 'clustering';

    /**
     * Broadcasting consumption model
     * @experimental
     */
    public const ROCKETMQ_CONSUMPTION_MODEL_VALUE_BROADCASTING = 'broadcasting';

    /**
     * The delay time level for delay message, which determines the message delay time.
     *
     * @experimental
     */
    public const ROCKETMQ_MESSAGE_DELAY_TIME_LEVEL = 'rocketmq.message.delay_time_level';

    /**
     * The timestamp in milliseconds that the delay message is expected to be delivered to consumer.
     *
     * @experimental
     */
    public const ROCKETMQ_MESSAGE_DELIVERY_TIMESTAMP = 'rocketmq.message.delivery_timestamp';

    /**
     * It is essential for FIFO message. Messages that belong to the same message group are always processed one by one within the same consumer group.
     *
     * @experimental
     */
    public const ROCKETMQ_MESSAGE_GROUP = 'rocketmq.message.group';

    /**
     * Key(s) of message, another way to mark message besides message ID.
     *
     * @experimental
     */
    public const ROCKETMQ_MESSAGE_KEYS = 'rocketmq.message.keys';

    /**
     * The secondary classifier of message besides topic.
     *
     * @experimental
     */
    public const ROCKETMQ_MESSAGE_TAG = 'rocketmq.message.tag';

    /**
     * Type of message.
     *
     * @experimental
     */
    public const ROCKETMQ_MESSAGE_TYPE = 'rocketmq.message.type';

    /**
     * Normal message
     * @experimental
     */
    public const ROCKETMQ_MESSAGE_TYPE_VALUE_NORMAL = 'normal';

    /**
     * FIFO message
     * @experimental
     */
    public const ROCKETMQ_MESSAGE_TYPE_VALUE_FIFO = 'fifo';

    /**
     * Delay message
     * @experimental
     */
    public const ROCKETMQ_MESSAGE_TYPE_VALUE_DELAY = 'delay';

    /**
     * Transaction message
     * @experimental
     */
    public const ROCKETMQ_MESSAGE_TYPE_VALUE_TRANSACTION = 'transaction';

    /**
     * Namespace of RocketMQ resources, resources in different namespaces are individual.
     *
     * @experimental
     */
    public const ROCKETMQ_NAMESPACE = 'rocketmq.namespace';

}
