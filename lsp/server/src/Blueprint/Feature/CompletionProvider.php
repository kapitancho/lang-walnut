<?php

declare(strict_types=1);

namespace Walnut\Lang\Lsp\Blueprint\Feature;

use Walnut\Lang\Lsp\Blueprint\Document\CompilationSnapshot;

/**
 * Provides textDocument/completion responses.
 *
 * Three completion contexts are handled:
 *   - After `->` : method completion (requires Gap 2 + Gap 4)
 *   - Inside a type annotation position : type name completion (ready now)
 *   - Inside an expression : variable completion (requires Gap 3)
 */
interface CompletionProvider {

    /**
     * @param int    $line             0-based line
     * @param int    $character        0-based character offset
     * @param string $triggerCharacter e.g. ">" when typed after "->"
     * @param string $liveSourceText   Current document text as the editor sees it.
     *                                 May be newer than $snapshot->sourceText when the
     *                                 trigger character fires before the debounce-delayed
     *                                 didChange has been compiled (e.g. user just typed "->").
     * @return list<array<string, mixed>>  list of LSP CompletionItem objects
     */
    public function completions(
        CompilationSnapshot $snapshot,
        int                 $line,
        int                 $character,
        string              $triggerCharacter,
        string              $liveSourceText,
    ): array;
}
