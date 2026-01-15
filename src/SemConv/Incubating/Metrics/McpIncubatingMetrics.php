<?php

// DO NOT EDIT, this is an Auto-generated file from script/semantic-conventions

declare(strict_types=1);

namespace OpenTelemetry\SemConv\Incubating\Metrics;

/**
 * Metrics for mcp.
 * May contain @experimental Semantic Conventions which may change or be removed in the future.
 */
interface McpIncubatingMetrics
{
    /**
     * The duration of the MCP request or notification as observed on the sender from the time it was sent until the response or ack is received.
     *
     * Instrument: histogram
     * Unit: s
     * @experimental
     */
    public const MCP_CLIENT_OPERATION_DURATION = 'mcp.client.operation.duration';

    /**
     * The duration of the MCP session as observed on the MCP client.
     *
     * Instrument: histogram
     * Unit: s
     * @experimental
     */
    public const MCP_CLIENT_SESSION_DURATION = 'mcp.client.session.duration';

    /**
     * MCP request or notification duration as observed on the receiver from the time it was received until the result or ack is sent.
     *
     * Instrument: histogram
     * Unit: s
     * @experimental
     */
    public const MCP_SERVER_OPERATION_DURATION = 'mcp.server.operation.duration';

    /**
     * The duration of the MCP session as observed on the MCP server.
     *
     * Instrument: histogram
     * Unit: s
     * @experimental
     */
    public const MCP_SERVER_SESSION_DURATION = 'mcp.server.session.duration';

}
