<?php

declare(strict_types=1);

namespace Walnut\Lang\Lsp\Implementation\Compilation;

use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationResultCollector;
use Walnut\Lang\Almond\Engine\Implementation\Program\Validation\BacktraceValidationResultCollector;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\CodeMapper;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\PositionalLocator;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\SourceNodeLocator;
use Walnut\Lang\Almond\ProgramBuilder\Implementation\CodeMapper\NodeCodeMapper;
use Walnut\Lang\Lsp\Blueprint\Compilation\CompilationIndexFactory;

final readonly class DefaultCompilationIndexFactory implements CompilationIndexFactory {

    public function createCodeIndex(): CodeMapper&SourceNodeLocator&PositionalLocator {
        return new NodeCodeMapper();
    }

    public function createValidationCollector(): ValidationResultCollector {
        return new BacktraceValidationResultCollector();
    }
}
