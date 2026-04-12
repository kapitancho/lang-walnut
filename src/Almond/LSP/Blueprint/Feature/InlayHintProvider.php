<?php

declare(strict_types=1);

namespace Walnut\Lang\Almond\LSP\Blueprint\Feature;

use Walnut\Lang\Almond\LSP\Blueprint\Document\CompilationSnapshot;

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
