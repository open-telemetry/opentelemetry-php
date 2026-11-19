<?php

// DO NOT EDIT, this is an Auto-generated file from script/semantic-conventions

declare(strict_types=1);

namespace OpenTelemetry\SemConv\Incubating\Attributes;

/**
 * Semantic attributes and corresponding values for code.
 * @see https://opentelemetry.io/docs/specs/semconv/registry/attributes/code/
 * May contain @experimental Semantic Conventions which may change or be removed in the future.
 */
interface CodeIncubatingAttributes
{
    /**
     * Deprecated, use `code.column.number`
     *
     * @experimental
     * @deprecated Replaced by `code.column.number`.
     */
    public const CODE_COLUMN = 'code.column';

    /**
     * The column number in `code.file.path` best representing the operation. It SHOULD point within the code unit named in `code.function.name`. This attribute MUST NOT be used on the Profile signal since the data is already captured in 'message Line'. This constraint is imposed to prevent redundancy and maintain data integrity.
     *
     * @stable
     */
    public const CODE_COLUMN_NUMBER = 'code.column.number';

    /**
     * The source code filename that identifies the code unit as uniquely as possible (preferably an absolute file path). This attribute MUST NOT be used on the Profile signal since the data is already captured in 'message Function'. This constraint is imposed to prevent redundancy and maintain data integrity.
     *
     * @stable
     */
    public const CODE_FILE_PATH = 'code.file.path';

    /**
     * Deprecated, use `code.file.path` instead
     *
     * @experimental
     * @deprecated Replaced by `code.file.path`.
     */
    public const CODE_FILEPATH = 'code.filepath';

    /**
     * Deprecated, use `code.function.name` instead
     *
     * @experimental
     * @deprecated Value should be included in `code.function.name` which is expected to be a fully-qualified name.
     */
    public const CODE_FUNCTION = 'code.function';

    /**
     * The method or function fully-qualified name without arguments. The value should fit the natural representation of the language runtime, which is also likely the same used within `code.stacktrace` attribute value. This attribute MUST NOT be used on the Profile signal since the data is already captured in 'message Function'. This constraint is imposed to prevent redundancy and maintain data integrity.
     *
     * Values and format depends on each language runtime, thus it is impossible to provide an exhaustive list of examples.
     * The values are usually the same (or prefixes of) the ones found in native stack trace representation stored in
     * `code.stacktrace` without information on arguments.
     *
     * Examples:
     *
     * - Java method: `com.example.MyHttpService.serveRequest`
     * - Java anonymous class method: `com.mycompany.Main$1.myMethod`
     * - Java lambda method: `com.mycompany.Main$$Lambda/0x0000748ae4149c00.myMethod`
     * - PHP function: `GuzzleHttp\Client::transfer`
     * - Go function: `github.com/my/repo/pkg.foo.func5`
     * - Elixir: `OpenTelemetry.Ctx.new`
     * - Erlang: `opentelemetry_ctx:new`
     * - Rust: `playground::my_module::my_cool_func`
     * - C function: `fopen`
     *
     * @stable
     */
    public const CODE_FUNCTION_NAME = 'code.function.name';

    /**
     * The line number in `code.file.path` best representing the operation. It SHOULD point within the code unit named in `code.function.name`. This attribute MUST NOT be used on the Profile signal since the data is already captured in 'message Line'. This constraint is imposed to prevent redundancy and maintain data integrity.
     *
     * @stable
     */
    public const CODE_LINE_NUMBER = 'code.line.number';

    /**
     * Deprecated, use `code.line.number` instead
     *
     * @experimental
     * @deprecated Replaced by `code.line.number`.
     */
    public const CODE_LINENO = 'code.lineno';

    /**
     * Deprecated, namespace is now included into `code.function.name`
     *
     * @experimental
     * @deprecated Value should be included in `code.function.name` which is expected to be a fully-qualified name.
     */
    public const CODE_NAMESPACE = 'code.namespace';

    /**
     * A stacktrace as a string in the natural representation for the language runtime. The representation is identical to [`exception.stacktrace`](/docs/exceptions/exceptions-spans.md#stacktrace-representation). This attribute MUST NOT be used on the Profile signal since the data is already captured in 'message Location'. This constraint is imposed to prevent redundancy and maintain data integrity.
     *
     * @stable
     */
    public const CODE_STACKTRACE = 'code.stacktrace';

}
