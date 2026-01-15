<?php

// DO NOT EDIT, this is an Auto-generated file from script/semantic-conventions

declare(strict_types=1);

namespace OpenTelemetry\SemConv\Incubating\Attributes;

/**
 * Semantic attributes and corresponding values for mcp.
 * @see https://opentelemetry.io/docs/specs/semconv/registry/attributes/mcp/
 * May contain @experimental Semantic Conventions which may change or be removed in the future.
 */
interface McpIncubatingAttributes
{
    /**
     * The name of the request or notification method.
     *
     * @experimental
     */
    public const MCP_METHOD_NAME = 'mcp.method.name';

    /**
     * Notification cancelling a previously-issued request.
     *
     * @experimental
     */
    public const MCP_METHOD_NAME_VALUE_NOTIFICATIONS_CANCELLED = 'notifications/cancelled';

    /**
     * Request to initialize the MCP client.
     *
     * @experimental
     */
    public const MCP_METHOD_NAME_VALUE_INITIALIZE = 'initialize';

    /**
     * Notification indicating that the MCP client has been initialized.
     *
     * @experimental
     */
    public const MCP_METHOD_NAME_VALUE_NOTIFICATIONS_INITIALIZED = 'notifications/initialized';

    /**
     * Notification indicating the progress for a long-running operation.
     *
     * @experimental
     */
    public const MCP_METHOD_NAME_VALUE_NOTIFICATIONS_PROGRESS = 'notifications/progress';

    /**
     * Request to check that the other party is still alive.
     *
     * @experimental
     */
    public const MCP_METHOD_NAME_VALUE_PING = 'ping';

    /**
     * Request to list resources available on server.
     *
     * @experimental
     */
    public const MCP_METHOD_NAME_VALUE_RESOURCES_LIST = 'resources/list';

    /**
     * Request to list resource templates available on server.
     *
     * @experimental
     */
    public const MCP_METHOD_NAME_VALUE_RESOURCES_TEMPLATES_LIST = 'resources/templates/list';

    /**
     * Request to read a resource.
     *
     * @experimental
     */
    public const MCP_METHOD_NAME_VALUE_RESOURCES_READ = 'resources/read';

    /**
     * Notification indicating that the list of resources has changed.
     *
     * @experimental
     */
    public const MCP_METHOD_NAME_VALUE_NOTIFICATIONS_RESOURCES_LIST_CHANGED = 'notifications/resources/list_changed';

    /**
     * Request to subscribe to a resource.
     *
     * @experimental
     */
    public const MCP_METHOD_NAME_VALUE_RESOURCES_SUBSCRIBE = 'resources/subscribe';

    /**
     * Request to unsubscribe from resource updates.
     *
     * @experimental
     */
    public const MCP_METHOD_NAME_VALUE_RESOURCES_UNSUBSCRIBE = 'resources/unsubscribe';

    /**
     * Notification indicating that a resource has been updated.
     *
     * @experimental
     */
    public const MCP_METHOD_NAME_VALUE_NOTIFICATIONS_RESOURCES_UPDATED = 'notifications/resources/updated';

    /**
     * Request to list prompts available on server.
     *
     * @experimental
     */
    public const MCP_METHOD_NAME_VALUE_PROMPTS_LIST = 'prompts/list';

    /**
     * Request to get a prompt.
     *
     * @experimental
     */
    public const MCP_METHOD_NAME_VALUE_PROMPTS_GET = 'prompts/get';

    /**
     * Notification indicating that the list of prompts has changed.
     *
     * @experimental
     */
    public const MCP_METHOD_NAME_VALUE_NOTIFICATIONS_PROMPTS_LIST_CHANGED = 'notifications/prompts/list_changed';

    /**
     * Request to list tools available on server.
     *
     * @experimental
     */
    public const MCP_METHOD_NAME_VALUE_TOOLS_LIST = 'tools/list';

    /**
     * Request to call a tool.
     *
     * @experimental
     */
    public const MCP_METHOD_NAME_VALUE_TOOLS_CALL = 'tools/call';

    /**
     * Notification indicating that the list of tools has changed.
     *
     * @experimental
     */
    public const MCP_METHOD_NAME_VALUE_NOTIFICATIONS_TOOLS_LIST_CHANGED = 'notifications/tools/list_changed';

    /**
     * Request to set the logging level.
     *
     * @experimental
     */
    public const MCP_METHOD_NAME_VALUE_LOGGING_SET_LEVEL = 'logging/setLevel';

    /**
     * Notification indicating that a message has been received.
     *
     * @experimental
     */
    public const MCP_METHOD_NAME_VALUE_NOTIFICATIONS_MESSAGE = 'notifications/message';

    /**
     * Request to create a sampling message.
     *
     * @experimental
     */
    public const MCP_METHOD_NAME_VALUE_SAMPLING_CREATE_MESSAGE = 'sampling/createMessage';

    /**
     * Request to complete a prompt.
     *
     * @experimental
     */
    public const MCP_METHOD_NAME_VALUE_COMPLETION_COMPLETE = 'completion/complete';

    /**
     * Request to list roots available on server.
     *
     * @experimental
     */
    public const MCP_METHOD_NAME_VALUE_ROOTS_LIST = 'roots/list';

    /**
     * Notification indicating that the list of roots has changed.
     *
     * @experimental
     */
    public const MCP_METHOD_NAME_VALUE_NOTIFICATIONS_ROOTS_LIST_CHANGED = 'notifications/roots/list_changed';

    /**
     * Request from the server to elicit additional information from the user via the client
     *
     * @experimental
     */
    public const MCP_METHOD_NAME_VALUE_ELICITATION_CREATE = 'elicitation/create';

    /**
     * The [version](https://modelcontextprotocol.io/specification/versioning) of the Model Context Protocol used.
     *
     * @experimental
     */
    public const MCP_PROTOCOL_VERSION = 'mcp.protocol.version';

    /**
     * The value of the resource uri.
     * This is a URI of the resource provided in the following requests or notifications: `resources/read`, `resources/subscribe`, `resources/unsubscribe`, or `notifications/resources/updated`.
     *
     * @experimental
     */
    public const MCP_RESOURCE_URI = 'mcp.resource.uri';

    /**
     * Identifies [MCP session](https://modelcontextprotocol.io/specification/2025-06-18/basic/transports#session-management).
     *
     * @experimental
     */
    public const MCP_SESSION_ID = 'mcp.session.id';

}
