<?php

// DO NOT EDIT, this is an Auto-generated file from script/semantic-conventions

declare(strict_types=1);

namespace OpenTelemetry\SemConv\Incubating\Attributes;

/**
 * Semantic attributes and corresponding values for onc_rpc.
 * @see https://opentelemetry.io/docs/specs/semconv/registry/attributes/onc_rpc/
 * May contain @experimental Semantic Conventions which may change or be removed in the future.
 */
interface OncRpcIncubatingAttributes
{
    /**
     * ONC/Sun RPC procedure name.
     *
     * @experimental
     */
    public const ONC_RPC_PROCEDURE_NAME = 'onc_rpc.procedure.name';

    /**
     * ONC/Sun RPC procedure number.
     *
     * @experimental
     */
    public const ONC_RPC_PROCEDURE_NUMBER = 'onc_rpc.procedure.number';

    /**
     * ONC/Sun RPC program name.
     *
     * @experimental
     */
    public const ONC_RPC_PROGRAM_NAME = 'onc_rpc.program.name';

    /**
     * ONC/Sun RPC program version.
     *
     * @experimental
     */
    public const ONC_RPC_VERSION = 'onc_rpc.version';

}
