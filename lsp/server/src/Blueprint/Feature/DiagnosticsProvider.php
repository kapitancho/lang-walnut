<?php

declare(strict_types=1);

namespace Walnut\Lang\Lsp\Blueprint\Feature;

use Walnut\Lang\Lsp\Blueprint\Document\CompilationSnapshot;

/**
 * Translates a compilation snapshot into LSP diagnostic objects.
 *
 * The caller is responsible for sending the resulting
 * textDocument/publishDiagnostics notification.
 */
interface DiagnosticsProvider {

    /**
     * @return list<array<string, mixed>>  LSP Diagnostic objects
     */
    public function diagnosticsFor(CompilationSnapshot $snapshot): array;
}
