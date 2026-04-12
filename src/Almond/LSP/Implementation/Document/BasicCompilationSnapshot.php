<?php

declare(strict_types=1);

namespace Walnut\Lang\Almond\LSP\Implementation\Document;

use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationContextScope;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\PositionalLocator;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\SourceNodeLocator;
use Walnut\Lang\Almond\Runner\Blueprint\Compilation\CompilationResult;
use Walnut\Lang\Almond\Runner\Blueprint\Compilation\Error\CompilationFailure;
use Walnut\Lang\Almond\LSP\Blueprint\Document\CompilationSnapshot;

final class BasicCompilationSnapshot implements CompilationSnapshot {

    public bool $isValid {
        get => !($this->compilationResult instanceof CompilationFailure);
    }

    /**
     * True when the engine build ran to completion for this module, meaning the
     * codeIndex is populated and structural LSP features (semantic tokens, folding
     * ranges, hover, etc.) can work.
     *
     * This is more accurate than checking for the absence of parse errors: a build
     * exception or pre-build validation failure also produces an empty codeIndex
     * even though no parse error occurred, and should not overwrite lastParsed.
     */
    public bool $isParsed {
        get => count($this->codeIndex->allElements($this->moduleName)) > 0;
    }

    public function __construct(
        public readonly string                         $uri,
        public readonly string                         $moduleName,
        public readonly int                            $version,
        public readonly string                         $sourceText,
        public readonly CompilationResult              $compilationResult,
        public readonly PositionalLocator&SourceNodeLocator $codeIndex,
        public readonly ValidationContextScope         $contextScope,
    ) {}
}
