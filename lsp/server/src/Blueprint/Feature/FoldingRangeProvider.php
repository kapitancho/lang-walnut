<?php

declare(strict_types=1);

namespace Walnut\Lang\Lsp\Blueprint\Feature;

use Walnut\Lang\Lsp\Blueprint\Document\CompilationSnapshot;

/**
 * Provides textDocument/foldingRange responses.
 *
 * Returns one folding range per SequenceExpression or FunctionBody whose
 * start and end lines differ, allowing editors to collapse blocks.
 */
interface FoldingRangeProvider {

    /**
     * @return list<array{startLine: int, endLine: int, kind: string}>
     */
    public function foldingRanges(CompilationSnapshot $snapshot): array;
}
