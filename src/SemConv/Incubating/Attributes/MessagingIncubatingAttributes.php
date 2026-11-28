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
     * Deprecated, no replacement at this time.
     *
     * @experimental
     * @deprecated Removed. No replacement at this time.
     */
    public const MESSAGING_DESTINATION_PUBLISH_ANONYMOUS = 'messaging.destination_publish.anonymous';

    /**
     * Deprecated, no replacement at this time.
     *
     * @experimental
     * @deprecated Removed. No replacement at this time.
     */
    public const MESSAGING_DESTINATION_PUBLISH_NAME = 'messaging.destination_publish.name';

    /**
     * Deprecated, use `messaging.consumer.group.name` instead.
     *
     * @experimental
     * @deprecated Replaced by `messaging.consumer.group.name`.
     */
    public const MESSAGING_EVENTHUBS_CONSUMER_GROUP = 'messaging.eventhubs.consumer.group';

    /**
     * Deprecated, use `azure.eventhubs.message.enqueued_time` instead.
     *
     * @experimental
     * @deprecated Replaced by `azure.eventhubs.message.enqueued_time`.
     */
    public const MESSAGING_EVENTHUBS_MESSAGE_ENQUEUED_TIME = 'messaging.eventhubs.message.enqueued_time';

    /**
     * Deprecated, use `gcp.pubsub.message.ack_deadline` instead.
     *
     * @experimental
     * @deprecated Replaced by `gcp.pubsub.message.ack_deadline`.
     */
    public const MESSAGING_GCP_PUBSUB_MESSAGE_ACK_DEADLINE = 'messaging.gcp_pubsub.message.ack_deadline';

    /**
     * Deprecated, use `gcp.pubsub.message.ack_id` instead.
     *
     * @experimental
     * @deprecated Replaced by `gcp.pubsub.message.ack_id`.
     */
    public const MESSAGING_GCP_PUBSUB_MESSAGE_ACK_ID = 'messaging.gcp_pubsub.message.ack_id';

    /**
     * Deprecated, use `gcp.pubsub.message.delivery_attempt` instead.
     *
     * @experimental
     * @deprecated Replaced by `gcp.pubsub.message.delivery_attempt`.
     */
    public const MESSAGING_GCP_PUBSUB_MESSAGE_DELIVERY_ATTEMPT = 'messaging.gcp_pubsub.message.delivery_attempt';

    /**
     * Deprecated, use `gcp.pubsub.message.ordering_key` instead.
     *
     * @experimental
     * @deprecated Replaced by `gcp.pubsub.message.ordering_key`.
     */
    public const MESSAGING_GCP_PUBSUB_MESSAGE_ORDERING_KEY = 'messaging.gcp_pubsub.message.ordering_key';

    /**
     * Deprecated, use `kafka.cluster.id` instead.
     * The cluster ID is a unique identifier reported by the Kafka broker. It identifies the cluster independently of the individual brokers the client is configured to connect to, and remains stable even if broker hostnames, IP addresses, or ports change.
     *
     * @experimental
     * @deprecated Replaced by `kafka.cluster.id`.
     */
    public const MESSAGING_KAFKA_CLUSTER_ID = 'messaging.kafka.cluster.id';

    /**
     * Deprecated, use `messaging.consumer.group.name` instead.
     *
     * @experimental
     * @deprecated Replaced by `messaging.consumer.group.name`.
     */
    public const MESSAGING_KAFKA_CONSUMER_GROUP = 'messaging.kafka.consumer.group';

    /**
     * Deprecated, use `messaging.destination.partition.id` instead.
     *
     * @experimental
     * @deprecated Record string representation of the partition ID in `messaging.destination.partition.id` attribute.
     */
    public const MESSAGING_KAFKA_DESTINATION_PARTITION = 'messaging.kafka.destination.partition';

    /**
     * Deprecated, use `kafka.message.key` instead.
     * If the key type is not string, it's string representation has to be supplied for the attribute. If the key has no unambiguous, canonical string form, don't include its value.
     *
     * @experimental
     * @deprecated Replaced by `kafka.message.key`.
     */
    public const MESSAGING_KAFKA_MESSAGE_KEY = 'messaging.kafka.message.key';

    /**
     * Deprecated, use `kafka.offset` instead.
     *
     * @experimental
     * @deprecated Replaced by `kafka.offset`.
     */
    public const MESSAGING_KAFKA_MESSAGE_OFFSET = 'messaging.kafka.message.offset';

    /**
     * Deprecated, use `kafka.message.tombstone` instead.
     *
     * @experimental
     * @deprecated Replaced by `kafka.message.tombstone`.
     */
    public const MESSAGING_KAFKA_MESSAGE_TOMBSTONE = 'messaging.kafka.message.tombstone';

    /**
     * Deprecated, use `kafka.offset` instead.
     *
     * @experimental
     * @deprecated Replaced by `kafka.offset`.
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
     * Deprecated, use `messaging.operation.type` instead.
     *
     * @experimental
     * @deprecated Replaced by `messaging.operation.type`.
     */
    public const MESSAGING_OPERATION = 'messaging.operation';

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
     * Deprecated. Use `process` instead.
     * @experimental
     * @deprecated Replaced by `process`.
     */
    public const MESSAGING_OPERATION_TYPE_VALUE_DELIVER = 'deliver';

    /**
     * Deprecated. Use `send` instead.
     * @experimental
     * @deprecated Replaced by `send`.
     */
    public const MESSAGING_OPERATION_TYPE_VALUE_PUBLISH = 'publish';

    /**
     * Deprecated, use `rabbitmq.destination.routing_key` instead.
     *
     * @experimental
     * @deprecated Replaced by `rabbitmq.destination.routing_key`.
     */
    public const MESSAGING_RABBITMQ_DESTINATION_ROUTING_KEY = 'messaging.rabbitmq.destination.routing_key';

    /**
     * Deprecated, use `rabbitmq.message.delivery_tag` instead.
     *
     * @experimental
     * @deprecated Replaced by `rabbitmq.message.delivery_tag`.
     */
    public const MESSAGING_RABBITMQ_MESSAGE_DELIVERY_TAG = 'messaging.rabbitmq.message.delivery_tag';

    /**
     * Deprecated, use `messaging.consumer.group.name` instead.
     *
     * @experimental
     * @deprecated Replaced by `messaging.consumer.group.name` on the consumer spans. No replacement for producer spans.
     */
    public const MESSAGING_ROCKETMQ_CLIENT_GROUP = 'messaging.rocketmq.client_group';

    /**
     * Deprecated, use `rocketmq.consumption_model` instead.
     *
     * @experimental
     * @deprecated Replaced by `rocketmq.consumption_model`.
     */
    public const MESSAGING_ROCKETMQ_CONSUMPTION_MODEL = 'messaging.rocketmq.consumption_model';

    /**
     * Clustering consumption model
     * @experimental
     * @deprecated Replaced by `rocketmq.consumption_model`.
     */
    public const MESSAGING_ROCKETMQ_CONSUMPTION_MODEL_VALUE_CLUSTERING = 'clustering';

    /**
     * Broadcasting consumption model
     * @experimental
     * @deprecated Replaced by `rocketmq.consumption_model`.
     */
    public const MESSAGING_ROCKETMQ_CONSUMPTION_MODEL_VALUE_BROADCASTING = 'broadcasting';

    /**
     * Deprecated, use `rocketmq.message.delay_time_level` instead.
     *
     * @experimental
     * @deprecated Replaced by `rocketmq.message.delay_time_level`.
     */
    public const MESSAGING_ROCKETMQ_MESSAGE_DELAY_TIME_LEVEL = 'messaging.rocketmq.message.delay_time_level';

    /**
     * Deprecated, use `rocketmq.message.delivery_timestamp` instead.
     *
     * @experimental
     * @deprecated Replaced by `rocketmq.message.delivery_timestamp`.
     */
    public const MESSAGING_ROCKETMQ_MESSAGE_DELIVERY_TIMESTAMP = 'messaging.rocketmq.message.delivery_timestamp';

    /**
     * Deprecated, use `rocketmq.message.group` instead.
     *
     * @experimental
     * @deprecated Replaced by `rocketmq.message.group`.
     */
    public const MESSAGING_ROCKETMQ_MESSAGE_GROUP = 'messaging.rocketmq.message.group';

    /**
     * Deprecated, use `rocketmq.message.keys` instead.
     *
     * @experimental
     * @deprecated Replaced by `rocketmq.message.keys`.
     */
    public const MESSAGING_ROCKETMQ_MESSAGE_KEYS = 'messaging.rocketmq.message.keys';

    /**
     * Deprecated, use `rocketmq.message.tag` instead.
     *
     * @experimental
     * @deprecated Replaced by `rocketmq.message.tag`.
     */
    public const MESSAGING_ROCKETMQ_MESSAGE_TAG = 'messaging.rocketmq.message.tag';

    /**
     * Deprecated, use `rocketmq.message.type` instead.
     *
     * @experimental
     * @deprecated Replaced by `rocketmq.message.type`.
     */
    public const MESSAGING_ROCKETMQ_MESSAGE_TYPE = 'messaging.rocketmq.message.type';

    /**
     * Normal message
     * @experimental
     * @deprecated Replaced by `rocketmq.message.type`.
     */
    public const MESSAGING_ROCKETMQ_MESSAGE_TYPE_VALUE_NORMAL = 'normal';

    /**
     * FIFO message
     * @experimental
     * @deprecated Replaced by `rocketmq.message.type`.
     */
    public const MESSAGING_ROCKETMQ_MESSAGE_TYPE_VALUE_FIFO = 'fifo';

    /**
     * Delay message
     * @experimental
     * @deprecated Replaced by `rocketmq.message.type`.
     */
    public const MESSAGING_ROCKETMQ_MESSAGE_TYPE_VALUE_DELAY = 'delay';

    /**
     * Transaction message
     * @experimental
     * @deprecated Replaced by `rocketmq.message.type`.
     */
    public const MESSAGING_ROCKETMQ_MESSAGE_TYPE_VALUE_TRANSACTION = 'transaction';

    /**
     * Deprecated, use `rocketmq.namespace` instead.
     *
     * @experimental
     * @deprecated Replaced by `rocketmq.namespace`.
     */
    public const MESSAGING_ROCKETMQ_NAMESPACE = 'messaging.rocketmq.namespace';

    /**
     * Deprecated, use `messaging.destination.subscription.name` instead.
     *
     * @experimental
     * @deprecated Replaced by `messaging.destination.subscription.name`.
     */
    public const MESSAGING_SERVICEBUS_DESTINATION_SUBSCRIPTION_NAME = 'messaging.servicebus.destination.subscription_name';

    /**
     * Deprecated, use `azure.servicebus.disposition_status` instead.
     *
     * @experimental
     * @deprecated Replaced by `azure.servicebus.disposition_status`.
     */
    public const MESSAGING_SERVICEBUS_DISPOSITION_STATUS = 'messaging.servicebus.disposition_status';

    /**
     * Message is completed
     * @experimental
     * @deprecated Replaced by `azure.servicebus.disposition_status`.
     */
    public const MESSAGING_SERVICEBUS_DISPOSITION_STATUS_VALUE_COMPLETE = 'complete';

    /**
     * Message is abandoned
     * @experimental
     * @deprecated Replaced by `azure.servicebus.disposition_status`.
     */
    public const MESSAGING_SERVICEBUS_DISPOSITION_STATUS_VALUE_ABANDON = 'abandon';

    /**
     * Message is sent to dead letter queue
     * @experimental
     * @deprecated Replaced by `azure.servicebus.disposition_status`.
     */
    public const MESSAGING_SERVICEBUS_DISPOSITION_STATUS_VALUE_DEAD_LETTER = 'dead_letter';

    /**
     * Message is deferred
     * @experimental
     * @deprecated Replaced by `azure.servicebus.disposition_status`.
     */
    public const MESSAGING_SERVICEBUS_DISPOSITION_STATUS_VALUE_DEFER = 'defer';

    /**
     * Deprecated, use `azure.servicebus.message.delivery_count` instead.
     *
     * @experimental
     * @deprecated Replaced by `azure.servicebus.message.delivery_count`.
     */
    public const MESSAGING_SERVICEBUS_MESSAGE_DELIVERY_COUNT = 'messaging.servicebus.message.delivery_count';

    /**
     * Deprecated, use `azure.servicebus.message.enqueued_time` instead.
     *
     * @experimental
     * @deprecated Replaced by `azure.servicebus.message.enqueued_time`.
     */
    public const MESSAGING_SERVICEBUS_MESSAGE_ENQUEUED_TIME = 'messaging.servicebus.message.enqueued_time';

    /**
     * Deprecated, use `messaging.system.name` instead.
     * The actual messaging system may differ from the one known by the client.
     * For example, when using Kafka client libraries to communicate with Azure Event Hubs, the `messaging.system`
     * is set to `kafka` based on the instrumentation's best knowledge.
     *
     * When migrating to `messaging.system.name`, update the following values:
     *
     * - `aws_sqs` to `aws.sqs`
     * - `eventgrid` to `azure.eventgrid`
     * - `eventhubs` to `azure.eventhubs`
     * - `servicebus` to `azure.servicebus`
     * - `gcp_pubsub` to `gcp.pubsub`
     *
     * All other values are unchanged.
     *
     * @experimental
     * @deprecated Replaced by `messaging.system.name`.
     */
    public const MESSAGING_SYSTEM = 'messaging.system';

    /**
     * Apache ActiveMQ
     * @experimental
     * @deprecated Replaced by `messaging.system.name`.
     */
    public const MESSAGING_SYSTEM_VALUE_ACTIVEMQ = 'activemq';

    /**
     * Amazon Simple Notification Service (SNS)
     * @experimental
     * @deprecated Replaced by `messaging.system.name`.
     */
    public const MESSAGING_SYSTEM_VALUE_AWS_SNS = 'aws.sns';

    /**
     * Amazon Simple Queue Service (SQS)
     * @experimental
     * @deprecated Replaced by `messaging.system.name`.
     */
    public const MESSAGING_SYSTEM_VALUE_AWS_SQS = 'aws_sqs';

    /**
     * Azure Event Grid
     * @experimental
     * @deprecated Replaced by `messaging.system.name`.
     */
    public const MESSAGING_SYSTEM_VALUE_EVENTGRID = 'eventgrid';

    /**
     * Azure Event Hubs
     * @experimental
     * @deprecated Replaced by `messaging.system.name`.
     */
    public const MESSAGING_SYSTEM_VALUE_EVENTHUBS = 'eventhubs';

    /**
     * Azure Service Bus
     * @experimental
     * @deprecated Replaced by `messaging.system.name`.
     */
    public const MESSAGING_SYSTEM_VALUE_SERVICEBUS = 'servicebus';

    /**
     * Google Cloud Pub/Sub
     * @experimental
     * @deprecated Replaced by `messaging.system.name`.
     */
    public const MESSAGING_SYSTEM_VALUE_GCP_PUBSUB = 'gcp_pubsub';

    /**
     * Java Message Service
     * @experimental
     * @deprecated Replaced by `messaging.system.name`.
     */
    public const MESSAGING_SYSTEM_VALUE_JMS = 'jms';

    /**
     * Apache Kafka
     * @experimental
     * @deprecated Replaced by `messaging.system.name`.
     */
    public const MESSAGING_SYSTEM_VALUE_KAFKA = 'kafka';

    /**
     * RabbitMQ
     * @experimental
     * @deprecated Replaced by `messaging.system.name`.
     */
    public const MESSAGING_SYSTEM_VALUE_RABBITMQ = 'rabbitmq';

    /**
     * Apache RocketMQ
     * @experimental
     * @deprecated Replaced by `messaging.system.name`.
     */
    public const MESSAGING_SYSTEM_VALUE_ROCKETMQ = 'rocketmq';

    /**
     * Apache Pulsar
     * @experimental
     * @deprecated Replaced by `messaging.system.name`.
     */
    public const MESSAGING_SYSTEM_VALUE_PULSAR = 'pulsar';

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
