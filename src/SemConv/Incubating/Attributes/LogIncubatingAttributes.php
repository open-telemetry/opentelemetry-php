<?php

// DO NOT EDIT, this is an Auto-generated file from script/semantic-conventions

declare(strict_types=1);

namespace OpenTelemetry\SemConv\Incubating\Attributes;

/**
 * Semantic attributes and corresponding values for log.
 * @see https://opentelemetry.io/docs/specs/semconv/registry/attributes/log/
 * May contain @experimental Semantic Conventions which may change or be removed in the future.
 */
interface LogIncubatingAttributes
{
    /**
     * The basename of the file.
     *
     * @experimental
     */
    public const LOG_FILE_NAME = 'log.file.name';

    /**
     * The basename of the file, with symlinks resolved.
     *
     * @experimental
     */
    public const LOG_FILE_NAME_RESOLVED = 'log.file.name_resolved';

    /**
     * The full path to the file.
     *
     * @experimental
     */
    public const LOG_FILE_PATH = 'log.file.path';

    /**
     * The full path to the file, with symlinks resolved.
     *
     * @experimental
     */
    public const LOG_FILE_PATH_RESOLVED = 'log.file.path_resolved';

    /**
     * The stream associated with the log. See below for a list of well-known values.
     *
     * @experimental
     */
    public const LOG_IOSTREAM = 'log.iostream';

    /**
     * Logs from stdout stream
     * @experimental
     */
    public const LOG_IOSTREAM_VALUE_STDOUT = 'stdout';

    /**
     * Events from stderr stream
     * @experimental
     */
    public const LOG_IOSTREAM_VALUE_STDERR = 'stderr';

    /**
     * The complete original Log Record.
     *
     * This value MAY be added when processing a Log Record which was originally transmitted as a string or equivalent data type AND the Body field of the Log Record does not contain the same value. (e.g. a syslog or a log record read from a file.)
     *
     * @experimental
     */
    public const LOG_RECORD_ORIGINAL = 'log.record.original';

    /**
     * A unique identifier for the Log Record.
     *
     * If an id is provided, other log records with the same id will be considered duplicates and can be removed safely. This means, that two distinguishable log records MUST have different values.
     * The id MAY be an [Universally Unique Lexicographically Sortable Identifier (ULID)](https://github.com/ulid/spec), but other identifiers (e.g. UUID) may be used as needed.
     *
     * @experimental
     */
    public const LOG_RECORD_UID = 'log.record.uid';

}
