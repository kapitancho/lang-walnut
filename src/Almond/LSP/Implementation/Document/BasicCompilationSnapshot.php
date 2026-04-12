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

    public bool $isParsed {
        get => !($this->compilationResult instanceof CompilationFailure)
            || !$this->hasParseErrors();
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

    private function hasParseErrors(): bool {
        if (!($this->compilationResult instanceof CompilationFailure)) {
            return false;
        }
        return array_any(
            $this->compilationResult->errors,
            static fn($error): bool => match ($error->errorType->name) {
                'parseError', 'moduleDependencyLoop', 'moduleDependencyMissing' => true,
                default => false,
            }
        );
    }
}
