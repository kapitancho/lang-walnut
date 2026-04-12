<?php

declare(strict_types=1);

namespace Walnut\Lang\Almond\LSP\Blueprint\Document;

use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationContextScope;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\PositionalLocator;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\SourceNodeLocator;
use Walnut\Lang\Almond\Runner\Blueprint\Compilation\CompilationResult;

/**
 * A point-in-time compilation result for a single module.
 *
 * Three tiers are maintained per file:
 *  - live:        most recent compile attempt (may have errors)
 *  - lastParsed:  last parse-error-free result
 *  - lastValid:   last fully-valid (zero-error) result
 *
 * The snapshot bundles everything feature providers need post-compilation:
 *  - codeIndex     → positional lookup (offset → element) + forward lookup (element → location)
 *  - contextScope  → type of each expression + variable scope at each function body
 */
interface CompilationSnapshot {

    public string $uri { get; }

    /** The Walnut module name corresponding to this URI (e.g. "foo/bar"). */
    public string $moduleName { get; }

    /** Version counter from the LSP client (incremented on each didChange). */
    public int $version { get; }

    /** The source text that was compiled to produce this snapshot. */
    public string $sourceText { get; }

    /** The raw compilation result (may be CompilationFailure). */
    public CompilationResult $compilationResult { get; }

    /**
     * Positional index (offset → elements) and forward locator (element → source location),
     * built during compilation by NodeCodeMapper.
     *
     * Always present — is a noop when compilation was skipped.
     */
    public PositionalLocator&SourceNodeLocator $codeIndex { get; }

    /**
     * Type / scope index built during validation by BacktraceValidationResultCollector.
     *
     * typeOf($expression)  → the inferred Type, or null if not tracked.
     * scopeAt($expression) → the VariableScope visible at that expression.
     */
    public ValidationContextScope $contextScope { get; }

    /** True when the compilation result contains no errors of any kind. */
    public bool $isValid { get; }

    /** True when the source was parsed without parse errors (isValid implies isParsed). */
    public bool $isParsed { get; }
}
