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
     * SHOULD uniquely identify a specific queue, topic or other entity within the broker. If
     * the broker doesn't have such notion, it SHOULD uniquely identify the broker.
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
     * Destination names could be constructed from templates. An example would be a destination name involving a username or product ID. Although the destination name in this case is of high cardinality, the underlying template is of low cardinality and can be effectively used for grouping and aggregation.
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
     * The messaging system as identified by the client instrumentation.
     * The actual messaging system may differ from the one known by the client. For example, when using Kafka client libraries to communicate with Azure Event Hubs, the `messaging.system.name` is set to `kafka` based on the instrumentation's best knowledge.
     *
     * @experimental
     */
    public const MESSAGING_SYSTEM_NAME = 'messaging.system.name';

    /**
     * Apache ActiveMQ
     * @experimental
     */
    public const MESSAGING_SYSTEM_NAME_VALUE_ACTIVEMQ = 'activemq';

    /**
     * Amazon Simple Notification Service (SNS)
     * @experimental
     */
    public const MESSAGING_SYSTEM_NAME_VALUE_AWS_SNS = 'aws.sns';

    /**
     * Amazon Simple Queue Service (SQS)
     * @experimental
     */
    public const MESSAGING_SYSTEM_NAME_VALUE_AWS_SQS = 'aws.sqs';

    /**
     * Azure Event Grid
     * @experimental
     */
    public const MESSAGING_SYSTEM_NAME_VALUE_AZURE_EVENTGRID = 'azure.eventgrid';

    /**
     * Azure Event Hubs
     * @experimental
     */
    public const MESSAGING_SYSTEM_NAME_VALUE_AZURE_EVENTHUBS = 'azure.eventhubs';

    /**
     * Azure Service Bus
     * @experimental
     */
    public const MESSAGING_SYSTEM_NAME_VALUE_AZURE_SERVICEBUS = 'azure.servicebus';

    /**
     * Google Cloud Pub/Sub
     * @experimental
     */
    public const MESSAGING_SYSTEM_NAME_VALUE_GCP_PUBSUB = 'gcp.pubsub';

    /**
     * Java Message Service
     * @experimental
     */
    public const MESSAGING_SYSTEM_NAME_VALUE_JMS = 'jms';

    /**
     * Apache Kafka
     * @experimental
     */
    public const MESSAGING_SYSTEM_NAME_VALUE_KAFKA = 'kafka';

    /**
     * RabbitMQ
     * @experimental
     */
    public const MESSAGING_SYSTEM_NAME_VALUE_RABBITMQ = 'rabbitmq';

    /**
     * Apache RocketMQ
     * @experimental
     */
    public const MESSAGING_SYSTEM_NAME_VALUE_ROCKETMQ = 'rocketmq';

    /**
     * Apache Pulsar
     * @experimental
     */
    public const MESSAGING_SYSTEM_NAME_VALUE_PULSAR = 'pulsar';

}
