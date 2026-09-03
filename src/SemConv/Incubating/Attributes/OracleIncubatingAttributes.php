<?php

// DO NOT EDIT, this is an Auto-generated file from script/semantic-conventions

declare(strict_types=1);

namespace OpenTelemetry\SemConv\Incubating\Attributes;

/**
 * Semantic attributes and corresponding values for oracle.
 * @see https://opentelemetry.io/docs/specs/semconv/registry/attributes/oracle/
 * May contain @experimental Semantic Conventions which may change or be removed in the future.
 */
interface OracleIncubatingAttributes
{
    /**
     * The database domain associated with the connection.
     *
     * This attribute SHOULD be set to the value of the `DB_DOMAIN` initialization parameter,
     * as exposed in `v$parameter`. `DB_DOMAIN` defines the domain portion of the global
     * database name and SHOULD be configured when a database is, or may become, part of a
     * distributed environment. Its value consists of one or more valid identifiers
     * (alphanumeric ASCII characters) separated by periods.
     *
     * @experimental
     */
    public const ORACLE_DB_DOMAIN = 'oracle.db.domain';

    /**
     * The instance name associated with the connection in an Oracle Real Application Clusters environment.
     *
     * There can be multiple instances associated with a single database service. It indicates the
     * unique instance name to which the connection is currently bound. For non-RAC databases, this value
     * defaults to the `oracle.db.name`.
     *
     * @experimental
     */
    public const ORACLE_DB_INSTANCE_NAME = 'oracle.db.instance.name';

    /**
     * The database name associated with the connection.
     *
     * This attribute SHOULD be set to the value of the parameter `DB_NAME` exposed in `v$parameter`.
     *
     * @experimental
     */
    public const ORACLE_DB_NAME = 'oracle.db.name';

    /**
     * The pluggable database (PDB) name associated with the connection.
     *
     * This attribute SHOULD reflect the PDB that the session is currently connected to.
     * If instrumentation cannot reliably obtain the active PDB name for each operation
     * without issuing an additional query (such as `SELECT SYS_CONTEXT`), it is
     * RECOMMENDED to fall back to the PDB name specified at connection establishment.
     *
     * @experimental
     */
    public const ORACLE_DB_PDB = 'oracle.db.pdb';

    /**
     * The service name currently associated with the database connection.
     *
     * The effective service name for a connection can change during its lifetime,
     * for example after executing SQL, `ALTER SESSION`. If an instrumentation cannot reliably
     * obtain the current service name for each operation without issuing an additional
     * query (such as `SELECT SYS_CONTEXT`), it is RECOMMENDED to fall back to the
     * service name originally provided at connection establishment.
     *
     * @experimental
     */
    public const ORACLE_DB_SERVICE = 'oracle.db.service';

}
