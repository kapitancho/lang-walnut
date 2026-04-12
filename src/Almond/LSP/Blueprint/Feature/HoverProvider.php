<?php

declare(strict_types=1);

namespace Walnut\Lang\Almond\LSP\Blueprint\Feature;

use Walnut\Lang\Almond\LSP\Blueprint\Document\CompilationSnapshot;

/**
 * Provides textDocument/hover responses.
 *
 * Requires Gap 1 (PositionalLocator) and Gap 2 (TypeAnnotationCollector)
 * to be implemented in the Almond engine before this can be fully realised.
 */
interface HoverProvider {

    /**
     * @param int $line    0-based line (as sent by LSP client)
     * @param int $character 0-based character offset within the line
     * @return array<string, mixed>|null  LSP Hover object, or null if nothing to show
     */
    public function hover(CompilationSnapshot $snapshot, int $line, int $character): array|null;
}
