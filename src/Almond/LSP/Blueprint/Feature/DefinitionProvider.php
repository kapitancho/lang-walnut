<?php

declare(strict_types=1);

namespace Walnut\Lang\Almond\LSP\Blueprint\Feature;

use Walnut\Lang\Almond\LSP\Blueprint\Document\CompilationSnapshot;

/**
 * Provides textDocument/definition responses.
 *
 * Requires Gap 1 (PositionalLocator) in the Almond engine.
 * Variable definition tracking also needs a dedicated definition-site
 * flag in the NodeCodeMapper.
 */
interface DefinitionProvider {

    /**
     * @param int $line      0-based line
     * @param int $character 0-based character offset
     * @return array<string, mixed>|null  LSP Location object, or null if not found
     */
    public function definition(CompilationSnapshot $snapshot, int $line, int $character): array|null;
}
