<?php

declare(strict_types=1);

namespace Walnut\Lang\Lsp\Blueprint\Feature;

use Walnut\Lang\Lsp\Blueprint\Document\CompilationSnapshot;

/**
 * Provides textDocument/inlayHint responses.
 *
 * Returns type-annotation hints rendered after variable assignment targets,
 * e.g.  x«: Integer» = 42.  Long type labels are omitted.
 */
interface InlayHintProvider {

    /**
     * @param int $startLine 0-based, inclusive
     * @param int $endLine   0-based, inclusive
     * @return list<array<string, mixed>>
     */
    public function inlayHints(CompilationSnapshot $snapshot, int $startLine, int $endLine): array;
}
