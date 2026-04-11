<?php

declare(strict_types=1);

namespace Walnut\Lang\Lsp\Blueprint\Feature;

use Walnut\Lang\Lsp\Blueprint\Document\CompilationSnapshot;

/**
 * Provides textDocument/signatureHelp responses.
 *
 * When the cursor is inside the argument of a method call, this returns
 * the method's parameter type and return type.
 *
 * Requires Gap 1 (PositionalLocator) and Gap 2 (TypeAnnotationCollector).
 * For native methods, the NativeMethod interface must also expose parameterType().
 */
interface SignatureHelpProvider {

    /**
     * @param int $line      0-based line
     * @param int $character 0-based character offset
     * @return array<string, mixed>|null  LSP SignatureHelp object, or null
     */
    public function signatureHelp(CompilationSnapshot $snapshot, int $line, int $character): array|null;
}
