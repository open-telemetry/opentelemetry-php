<?php

// DO NOT EDIT, this is an Auto-generated file from script/semantic-conventions

declare(strict_types=1);

namespace OpenTelemetry\SemConv\Incubating\Attributes;

/**
 * Semantic attributes and corresponding values for messaging.
 * @see https://opentelemetry.io/docs/specs/semconv/registry/attributes/messaging/
 * May contain @experimental Semantic Conventions which may change or be removed in the future.
 */
interface MessagingIncubatingAttributes
{
    /**
     * The number of messages sent, received, or processed in the scope of the batching operation.
     * Instrumentations SHOULD NOT set `messaging.batch.message_count` on spans that operate with a single message. When a messaging client library supports both batch and single-message API for the same operation, instrumentations SHOULD use `messaging.batch.message_count` for batching APIs and SHOULD NOT use it for single-message APIs.
     *
     * @experimental
     */
    public const MESSAGING_BATCH_MESSAGE_COUNT = 'messaging.batch.message_count';

    /**
     * A unique identifier for the client that consumes or produces a message.
     *
     * @experimental
     */
    public const MESSAGING_CLIENT_ID = 'messaging.client.id';

    /**
     * The name of the consumer group with which a consumer is associated.
     *
     * Semantic conventions for individual messaging systems SHOULD document whether `messaging.consumer.group.name` is applicable and what it means in the context of that system.
     *
     * @experimental
     */
    public const MESSAGING_CONSUMER_GROUP_NAME = 'messaging.consumer.group.name';

    /**
     * A boolean that is true if the message destination is anonymous (could be unnamed or have auto-generated name).
     *
     * @experimental
     */
    public const MESSAGING_DESTINATION_ANONYMOUS = 'messaging.destination.anonymous';

    /**
     * The message destination name
     * Destination name SHOULD uniquely identify a specific queue, topic or other entity within the broker. If
     * the broker doesn't have such notion, the destination name SHOULD uniquely identify the broker.
     *
     * @experimental
     */
    public const MESSAGING_DESTINATION_NAME = 'messaging.destination.name';

    /**
     * The identifier of the partition messages are sent to or received from, unique within the `messaging.destination.name`.
     *
     * @experimental
     */
    public const MESSAGING_DESTINATION_PARTITION_ID = 'messaging.destination.partition.id';

    /**
     * The name of the destination subscription from which a message is consumed.
     * Semantic conventions for individual messaging systems SHOULD document whether `messaging.destination.subscription.name` is applicable and what it means in the context of that system.
     *
     * @experimental
     */
    public const MESSAGING_DESTINATION_SUBSCRIPTION_NAME = 'messaging.destination.subscription.name';

    /**
     * Low cardinality representation of the messaging destination name
     * Destination names could be constructed from templates. An example would be a destination name involving a user name or product id. Although the destination name in this case is of high cardinality, the underlying template is of low cardinality and can be effectively used for grouping and aggregation.
     *
     * @experimental
     */
    public const MESSAGING_DESTINATION_TEMPLATE = 'messaging.destination.template';

    /**
     * A boolean that is true if the message destination is temporary and might not exist anymore after messages are processed.
     *
     * @experimental
     */
    public const MESSAGING_DESTINATION_TEMPORARY = 'messaging.destination.temporary';

    /**
     * The UTC epoch seconds at which the message has been accepted and stored in the entity.
     *
     * @experimental
     */
    public const MESSAGING_EVENTHUBS_MESSAGE_ENQUEUED_TIME = 'messaging.eventhubs.message.enqueued_time';

    /**
     * The ack deadline in seconds set for the modify ack deadline request.
     *
     * @experimental
     */
    public const MESSAGING_GCP_PUBSUB_MESSAGE_ACK_DEADLINE = 'messaging.gcp_pubsub.message.ack_deadline';

    /**
     * The ack id for a given message.
     *
     * @experimental
     */
    public const MESSAGING_GCP_PUBSUB_MESSAGE_ACK_ID = 'messaging.gcp_pubsub.message.ack_id';

    /**
     * The delivery attempt for a given message.
     *
     * @experimental
     */
    public const MESSAGING_GCP_PUBSUB_MESSAGE_DELIVERY_ATTEMPT = 'messaging.gcp_pubsub.message.delivery_attempt';

    /**
     * The ordering key for a given message. If the attribute is not present, the message does not have an ordering key.
     *
     * @experimental
     */
    public const MESSAGING_GCP_PUBSUB_MESSAGE_ORDERING_KEY = 'messaging.gcp_pubsub.message.ordering_key';

    /**
     * Message keys in Kafka are used for grouping alike messages to ensure they're processed on the same partition. They differ from `messaging.message.id` in that they're not unique. If the key is `null`, the attribute MUST NOT be set.
     *
     * If the key type is not string, it's string representation has to be supplied for the attribute. If the key has no unambiguous, canonical string form, don't include its value.
     *
     * @experimental
     */
    public const MESSAGING_KAFKA_MESSAGE_KEY = 'messaging.kafka.message.key';

    /**
     * A boolean that is true if the message is a tombstone.
     *
     * @experimental
     */
    public const MESSAGING_KAFKA_MESSAGE_TOMBSTONE = 'messaging.kafka.message.tombstone';

    /**
     * The offset of a record in the corresponding Kafka partition.
     *
     * @experimental
     */
    public const MESSAGING_KAFKA_OFFSET = 'messaging.kafka.offset';

    /**
     * The size of the message body in bytes.
     *
     * This can refer to both the compressed or uncompressed body size. If both sizes are known, the uncompressed
     * body size should be used.
     *
     * @experimental
     */
    public const MESSAGING_MESSAGE_BODY_SIZE = 'messaging.message.body.size';

    /**
     * The conversation ID identifying the conversation to which the message belongs, represented as a string. Sometimes called "Correlation ID".
     *
     * @experimental
     */
    public const MESSAGING_MESSAGE_CONVERSATION_ID = 'messaging.message.conversation_id';

    /**
     * The size of the message body and metadata in bytes.
     *
     * This can refer to both the compressed or uncompressed size. If both sizes are known, the uncompressed
     * size should be used.
     *
     * @experimental
     */
    public const MESSAGING_MESSAGE_ENVELOPE_SIZE = 'messaging.message.envelope.size';

    /**
     * A value used by the messaging system as an identifier for the message, represented as a string.
     *
     * @experimental
     */
    public const MESSAGING_MESSAGE_ID = 'messaging.message.id';

    /**
     * The system-specific name of the messaging operation.
     *
     * @experimental
     */
    public const MESSAGING_OPERATION_NAME = 'messaging.operation.name';

    /**
     * A string identifying the type of the messaging operation.
     *
     * If a custom value is used, it MUST be of low cardinality.
     * @experimental
     */
    public const MESSAGING_OPERATION_TYPE = 'messaging.operation.type';

    /**
     * A message is created. "Create" spans always refer to a single message and are used to provide a unique creation context for messages in batch sending scenarios.
     *
     * @experimental
     */
    public const MESSAGING_OPERATION_TYPE_VALUE_CREATE = 'create';

    /**
     * One or more messages are provided for sending to an intermediary. If a single message is sent, the context of the "Send" span can be used as the creation context and no "Create" span needs to be created.
     *
     * @experimental
     */
    public const MESSAGING_OPERATION_TYPE_VALUE_SEND = 'send';

    /**
     * One or more messages are requested by a consumer. This operation refers to pull-based scenarios, where consumers explicitly call methods of messaging SDKs to receive messages.
     *
     * @experimental
     */
    public const MESSAGING_OPERATION_TYPE_VALUE_RECEIVE = 'receive';

    /**
     * One or more messages are processed by a consumer.
     *
     * @experimental
     */
    public const MESSAGING_OPERATION_TYPE_VALUE_PROCESS = 'process';

    /**
     * One or more messages are settled.
     *
     * @experimental
     */
    public const MESSAGING_OPERATION_TYPE_VALUE_SETTLE = 'settle';

    /**
     * RabbitMQ message routing key.
     *
     * @experimental
     */
    public const MESSAGING_RABBITMQ_DESTINATION_ROUTING_KEY = 'messaging.rabbitmq.destination.routing_key';

    /**
     * RabbitMQ message delivery tag
     *
     * @experimental
     */
    public const MESSAGING_RABBITMQ_MESSAGE_DELIVERY_TAG = 'messaging.rabbitmq.message.delivery_tag';

    /**
     * Model of message consumption. This only applies to consumer spans.
     *
     * @experimental
     */
    public const MESSAGING_ROCKETMQ_CONSUMPTION_MODEL = 'messaging.rocketmq.consumption_model';

    /**
     * Clustering consumption model
     * @experimental
     */
    public const MESSAGING_ROCKETMQ_CONSUMPTION_MODEL_VALUE_CLUSTERING = 'clustering';

    /**
     * Broadcasting consumption model
     * @experimental
     */
    public const MESSAGING_ROCKETMQ_CONSUMPTION_MODEL_VALUE_BROADCASTING = 'broadcasting';

    /**
     * The delay time level for delay message, which determines the message delay time.
     *
     * @experimental
     */
    public const MESSAGING_ROCKETMQ_MESSAGE_DELAY_TIME_LEVEL = 'messaging.rocketmq.message.delay_time_level';

    /**
     * The timestamp in milliseconds that the delay message is expected to be delivered to consumer.
     *
     * @experimental
     */
    public const MESSAGING_ROCKETMQ_MESSAGE_DELIVERY_TIMESTAMP = 'messaging.rocketmq.message.delivery_timestamp';

    /**
     * It is essential for FIFO message. Messages that belong to the same message group are always processed one by one within the same consumer group.
     *
     * @experimental
     */
    public const MESSAGING_ROCKETMQ_MESSAGE_GROUP = 'messaging.rocketmq.message.group';

    /**
     * Key(s) of message, another way to mark message besides message id.
     *
     * @experimental
     */
    public const MESSAGING_ROCKETMQ_MESSAGE_KEYS = 'messaging.rocketmq.message.keys';

    /**
     * The secondary classifier of message besides topic.
     *
     * @experimental
     */
    public const MESSAGING_ROCKETMQ_MESSAGE_TAG = 'messaging.rocketmq.message.tag';

    /**
     * Type of message.
     *
     * @experimental
     */
    public const MESSAGING_ROCKETMQ_MESSAGE_TYPE = 'messaging.rocketmq.message.type';

    /**
     * Normal message
     * @experimental
     */
    public const MESSAGING_ROCKETMQ_MESSAGE_TYPE_VALUE_NORMAL = 'normal';

    /**
     * FIFO message
     * @experimental
     */
    public const MESSAGING_ROCKETMQ_MESSAGE_TYPE_VALUE_FIFO = 'fifo';

    /**
     * Delay message
     * @experimental
     */
    public const MESSAGING_ROCKETMQ_MESSAGE_TYPE_VALUE_DELAY = 'delay';

    /**
     * Transaction message
     * @experimental
     */
    public const MESSAGING_ROCKETMQ_MESSAGE_TYPE_VALUE_TRANSACTION = 'transaction';

    /**
     * Namespace of RocketMQ resources, resources in different namespaces are individual.
     *
     * @experimental
     */
    public const MESSAGING_ROCKETMQ_NAMESPACE = 'messaging.rocketmq.namespace';

    /**
     * Describes the [settlement type](https://learn.microsoft.com/azure/service-bus-messaging/message-transfers-locks-settlement#peeklock).
     *
     * @experimental
     */
    public const MESSAGING_SERVICEBUS_DISPOSITION_STATUS = 'messaging.servicebus.disposition_status';

    /**
     * Message is completed
     * @experimental
     */
    public const MESSAGING_SERVICEBUS_DISPOSITION_STATUS_VALUE_COMPLETE = 'complete';

    /**
     * Message is abandoned
     * @experimental
     */
    public const MESSAGING_SERVICEBUS_DISPOSITION_STATUS_VALUE_ABANDON = 'abandon';

    /**
     * Message is sent to dead letter queue
     * @experimental
     */
    public const MESSAGING_SERVICEBUS_DISPOSITION_STATUS_VALUE_DEAD_LETTER = 'dead_letter';

    /**
     * Message is deferred
     * @experimental
     */
    public const MESSAGING_SERVICEBUS_DISPOSITION_STATUS_VALUE_DEFER = 'defer';

    /**
     * Number of deliveries that have been attempted for this message.
     *
     * @experimental
     */
    public const MESSAGING_SERVICEBUS_MESSAGE_DELIVERY_COUNT = 'messaging.servicebus.message.delivery_count';

    /**
     * The UTC epoch seconds at which the message has been accepted and stored in the entity.
     *
     * @experimental
     */
    public const MESSAGING_SERVICEBUS_MESSAGE_ENQUEUED_TIME = 'messaging.servicebus.message.enqueued_time';

    /**
     * The messaging system as identified by the client instrumentation.
     * The actual messaging system may differ from the one known by the client. For example, when using Kafka client libraries to communicate with Azure Event Hubs, the `messaging.system` is set to `kafka` based on the instrumentation's best knowledge.
     *
     * @experimental
     */
    public const MESSAGING_SYSTEM = 'messaging.system';

    /**
     * Apache ActiveMQ
     * @experimental
     */
    public const MESSAGING_SYSTEM_VALUE_ACTIVEMQ = 'activemq';

    /**
     * Amazon Simple Notification Service (SNS)
     * @experimental
     */
    public const MESSAGING_SYSTEM_VALUE_AWS_SNS = 'aws.sns';

    /**
     * Amazon Simple Queue Service (SQS)
     * @experimental
     */
    public const MESSAGING_SYSTEM_VALUE_AWS_SQS = 'aws_sqs';

    /**
     * Azure Event Grid
     * @experimental
     */
    public const MESSAGING_SYSTEM_VALUE_EVENTGRID = 'eventgrid';

    /**
     * Azure Event Hubs
     * @experimental
     */
    public const MESSAGING_SYSTEM_VALUE_EVENTHUBS = 'eventhubs';

    /**
     * Azure Service Bus
     * @experimental
     */
    public const MESSAGING_SYSTEM_VALUE_SERVICEBUS = 'servicebus';

    /**
     * Google Cloud Pub/Sub
     * @experimental
     */
    public const MESSAGING_SYSTEM_VALUE_GCP_PUBSUB = 'gcp_pubsub';

    /**
     * Java Message Service
     * @experimental
     */
    public const MESSAGING_SYSTEM_VALUE_JMS = 'jms';

    /**
     * Apache Kafka
     * @experimental
     */
    public const MESSAGING_SYSTEM_VALUE_KAFKA = 'kafka';

    /**
     * RabbitMQ
     * @experimental
     */
    public const MESSAGING_SYSTEM_VALUE_RABBITMQ = 'rabbitmq';

    /**
     * Apache RocketMQ
     * @experimental
     */
    public const MESSAGING_SYSTEM_VALUE_ROCKETMQ = 'rocketmq';

    /**
     * Apache Pulsar
     * @experimental
     */
    public const MESSAGING_SYSTEM_VALUE_PULSAR = 'pulsar';

}
