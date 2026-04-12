<?php

declare(strict_types=1);

namespace Walnut\Lang\Lsp\Blueprint\Feature;

use Walnut\Lang\Lsp\Blueprint\Document\CompilationSnapshot;

/**
 * Provides textDocument/semanticTokens/full responses.
 *
 * The legend (tokenTypes and tokenModifiers) is declared here so the server
 * can include it in the initialize capabilities without depending on a concrete class.
 */
interface SemanticTokensProvider {

    /** @return list<string> token type names, ordered by their integer index */
    public function tokenTypes(): array;

    /** @return list<string> token modifier names, ordered by their bit position */
    public function tokenModifiers(): array;

    /**
     * Returns the LSP delta-encoded flat integer array for the whole document.
     * Each token is represented by 5 consecutive integers:
     *   deltaLine, deltaStart, length, tokenTypeIndex, tokenModifiersBitmask
     *
     * @return list<int>
     */
    public function semanticTokens(CompilationSnapshot $snapshot): array;
}
