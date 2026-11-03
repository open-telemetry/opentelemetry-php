<?php

// DO NOT EDIT, this is an Auto-generated file from script/semantic-conventions

declare(strict_types=1);

namespace OpenTelemetry\SemConv\Incubating\Attributes;

/**
 * Semantic attributes and corresponding values for kafka.
 * @see https://opentelemetry.io/docs/specs/semconv/registry/attributes/kafka/
 * May contain @experimental Semantic Conventions which may change or be removed in the future.
 */
interface KafkaIncubatingAttributes
{
    /**
     * The Kafka cluster ID, obtained from the broker metadata exposed through the Kafka client (or AdminClient) API.
     *
     * The cluster ID is a unique identifier reported by the Kafka broker. It identifies the cluster independently of the individual brokers the client is configured to connect to, and remains stable even if broker hostnames, IP addresses, or ports change.
     *
     * @experimental
     */
    public const KAFKA_CLUSTER_ID = 'kafka.cluster.id';

    /**
     * Message keys in Kafka are used for grouping alike messages to ensure they're processed on the same partition. They differ from `messaging.message.id` in that they're not unique. If the key is `null`, the attribute MUST NOT be set.
     *
     * If the key type is not string, its string representation has to be supplied for the attribute. If the key has no unambiguous, canonical string form, don't include its value.
     *
     * @experimental
     */
    public const KAFKA_MESSAGE_KEY = 'kafka.message.key';

    /**
     * A boolean that is true if the message is a tombstone.
     *
     * @experimental
     */
    public const KAFKA_MESSAGE_TOMBSTONE = 'kafka.message.tombstone';

    /**
     * The offset of a record in the corresponding Kafka partition.
     *
     * @experimental
     */
    public const KAFKA_OFFSET = 'kafka.offset';

}
