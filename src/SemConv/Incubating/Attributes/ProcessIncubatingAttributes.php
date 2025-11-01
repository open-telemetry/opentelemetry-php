<?php

// DO NOT EDIT, this is an Auto-generated file from script/semantic-conventions

declare(strict_types=1);

namespace OpenTelemetry\SemConv\Incubating\Attributes;

/**
 * Semantic attributes and corresponding values for process.
 * @see https://opentelemetry.io/docs/specs/semconv/registry/attributes/process/
 * May contain @experimental Semantic Conventions which may change or be removed in the future.
 */
interface ProcessIncubatingAttributes
{
    /**
     * Length of the process.command_args array
     *
     * This field can be useful for querying or performing bucket analysis on how many arguments were provided to start a process. More arguments may be an indication of suspicious activity.
     *
     * @experimental
     */
    public const PROCESS_ARGS_COUNT = 'process.args_count';

    /**
     * The command used to launch the process (i.e. the command name). On Linux based systems, can be set to the zeroth string in `proc/[pid]/cmdline`. On Windows, can be set to the first parameter extracted from `GetCommandLineW`.
     *
     * @experimental
     */
    public const PROCESS_COMMAND = 'process.command';

    /**
     * All the command arguments (including the command/executable itself) as received by the process. On Linux-based systems (and some other Unixoid systems supporting procfs), can be set according to the list of null-delimited strings extracted from `proc/[pid]/cmdline`. For libc-based executables, this would be the full argv vector passed to `main`. SHOULD NOT be collected by default unless there is sanitization that excludes sensitive data.
     *
     * @experimental
     */
    public const PROCESS_COMMAND_ARGS = 'process.command_args';

    /**
     * The full command used to launch the process as a single string representing the full command. On Windows, can be set to the result of `GetCommandLineW`. Do not set this if you have to assemble it just for monitoring; use `process.command_args` instead. SHOULD NOT be collected by default unless there is sanitization that excludes sensitive data.
     *
     * @experimental
     */
    public const PROCESS_COMMAND_LINE = 'process.command_line';

    /**
     * Specifies whether the context switches for this data point were voluntary or involuntary.
     *
     * @experimental
     */
    public const PROCESS_CONTEXT_SWITCH_TYPE = 'process.context_switch_type';

    /**
     * @experimental
     */
    public const PROCESS_CONTEXT_SWITCH_TYPE_VALUE_VOLUNTARY = 'voluntary';

    /**
     * @experimental
     */
    public const PROCESS_CONTEXT_SWITCH_TYPE_VALUE_INVOLUNTARY = 'involuntary';

    /**
     * The date and time the process was created, in ISO 8601 format.
     *
     * @experimental
     */
    public const PROCESS_CREATION_TIME = 'process.creation.time';

    /**
     * Process environment variables, `<key>` being the environment variable name, the value being the environment variable value.
     *
     * Examples:
     *
     * - an environment variable `USER` with value `"ubuntu"` SHOULD be recorded
     *   as the `process.environment_variable.USER` attribute with value `"ubuntu"`.
     * - an environment variable `PATH` with value `"/usr/local/bin:/usr/bin"`
     *   SHOULD be recorded as the `process.environment_variable.PATH` attribute
     *   with value `"/usr/local/bin:/usr/bin"`.
     *
     * @experimental
     */
    public const PROCESS_ENVIRONMENT_VARIABLE = 'process.environment_variable';

    /**
     * The GNU build ID as found in the `.note.gnu.build-id` ELF section (hex string).
     *
     * @experimental
     */
    public const PROCESS_EXECUTABLE_BUILD_ID_GNU = 'process.executable.build_id.gnu';

    /**
     * The Go build ID as retrieved by `go tool buildid <go executable>`.
     *
     * @experimental
     */
    public const PROCESS_EXECUTABLE_BUILD_ID_GO = 'process.executable.build_id.go';

    /**
     * Profiling specific build ID for executables. See the OTel specification for Profiles for more information.
     *
     * @experimental
     */
    public const PROCESS_EXECUTABLE_BUILD_ID_HTLHASH = 'process.executable.build_id.htlhash';

    /**
     * The name of the process executable. On Linux based systems, this SHOULD be set to the base name of the target of `/proc/[pid]/exe`. On Windows, this SHOULD be set to the base name of `GetProcessImageFileNameW`.
     *
     * @experimental
     */
    public const PROCESS_EXECUTABLE_NAME = 'process.executable.name';

    /**
     * The full path to the process executable. On Linux based systems, can be set to the target of `proc/[pid]/exe`. On Windows, can be set to the result of `GetProcessImageFileNameW`.
     *
     * @experimental
     */
    public const PROCESS_EXECUTABLE_PATH = 'process.executable.path';

    /**
     * The exit code of the process.
     *
     * @experimental
     */
    public const PROCESS_EXIT_CODE = 'process.exit.code';

    /**
     * The date and time the process exited, in ISO 8601 format.
     *
     * @experimental
     */
    public const PROCESS_EXIT_TIME = 'process.exit.time';

    /**
     * The PID of the process's group leader. This is also the process group ID (PGID) of the process.
     *
     * @experimental
     */
    public const PROCESS_GROUP_LEADER_PID = 'process.group_leader.pid';

    /**
     * Whether the process is connected to an interactive shell.
     *
     * @experimental
     */
    public const PROCESS_INTERACTIVE = 'process.interactive';

    /**
     * The control group associated with the process.
     * Control groups (cgroups) are a kernel feature used to organize and manage process resources. This attribute provides the path(s) to the cgroup(s) associated with the process, which should match the contents of the [/proc/[PID]/cgroup](https://man7.org/linux/man-pages/man7/cgroups.7.html) file.
     *
     * @experimental
     */
    public const PROCESS_LINUX_CGROUP = 'process.linux.cgroup';

    /**
     * The username of the user that owns the process.
     *
     * @experimental
     */
    public const PROCESS_OWNER = 'process.owner';

    /**
     * The type of page fault for this data point. Type `major` is for major/hard page faults, and `minor` is for minor/soft page faults.
     *
     * @experimental
     */
    public const PROCESS_PAGING_FAULT_TYPE = 'process.paging.fault_type';

    /**
     * @experimental
     */
    public const PROCESS_PAGING_FAULT_TYPE_VALUE_MAJOR = 'major';

    /**
     * @experimental
     */
    public const PROCESS_PAGING_FAULT_TYPE_VALUE_MINOR = 'minor';

    /**
     * Parent Process identifier (PPID).
     *
     * @experimental
     */
    public const PROCESS_PARENT_PID = 'process.parent_pid';

    /**
     * Process identifier (PID).
     *
     * @experimental
     */
    public const PROCESS_PID = 'process.pid';

    /**
     * The real user ID (RUID) of the process.
     *
     * @experimental
     */
    public const PROCESS_REAL_USER_ID = 'process.real_user.id';

    /**
     * The username of the real user of the process.
     *
     * @experimental
     */
    public const PROCESS_REAL_USER_NAME = 'process.real_user.name';

    /**
     * An additional description about the runtime of the process, for example a specific vendor customization of the runtime environment.
     *
     * @experimental
     */
    public const PROCESS_RUNTIME_DESCRIPTION = 'process.runtime.description';

    /**
     * The name of the runtime of this process.
     *
     * @experimental
     */
    public const PROCESS_RUNTIME_NAME = 'process.runtime.name';

    /**
     * The version of the runtime of this process, as returned by the runtime without modification.
     *
     * @experimental
     */
    public const PROCESS_RUNTIME_VERSION = 'process.runtime.version';

    /**
     * The saved user ID (SUID) of the process.
     *
     * @experimental
     */
    public const PROCESS_SAVED_USER_ID = 'process.saved_user.id';

    /**
     * The username of the saved user.
     *
     * @experimental
     */
    public const PROCESS_SAVED_USER_NAME = 'process.saved_user.name';

    /**
     * The PID of the process's session leader. This is also the session ID (SID) of the process.
     *
     * @experimental
     */
    public const PROCESS_SESSION_LEADER_PID = 'process.session_leader.pid';

    /**
     * Process title (proctitle)
     *
     * In many Unix-like systems, process title (proctitle), is the string that represents the name or command line of a running process, displayed by system monitoring tools like ps, top, and htop.
     *
     * @experimental
     */
    public const PROCESS_TITLE = 'process.title';

    /**
     * The effective user ID (EUID) of the process.
     *
     * @experimental
     */
    public const PROCESS_USER_ID = 'process.user.id';

    /**
     * The username of the effective user of the process.
     *
     * @experimental
     */
    public const PROCESS_USER_NAME = 'process.user.name';

    /**
     * Virtual process identifier.
     *
     * The process ID within a PID namespace. This is not necessarily unique across all processes on the host but it is unique within the process namespace that the process exists within.
     *
     * @experimental
     */
    public const PROCESS_VPID = 'process.vpid';

    /**
     * The working directory of the process.
     *
     * @experimental
     */
    public const PROCESS_WORKING_DIRECTORY = 'process.working_directory';

}
