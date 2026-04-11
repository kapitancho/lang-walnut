<?php

declare(strict_types=1);

namespace Walnut\Lang\Lsp\Blueprint\Document;

/**
 * Stores the live (in-editor) source content for open documents.
 *
 * URIs are used as-is from LSP client messages; the implementation
 * is responsible for converting file:// URIs to Walnut module names.
 */
interface DocumentStore {

    /**
     * Called on textDocument/didOpen and textDocument/didChange.
     */
    public function update(string $uri, int $version, string $content): void;

    /**
     * Called on textDocument/didClose.
     */
    public function remove(string $uri): void;

    /**
     * Returns the live content for $uri, or null if not open.
     */
    public function get(string $uri): string|null;

    /**
     * Returns true when the document is currently open.
     */
    public function isOpen(string $uri): bool;

    /**
     * Convert a file URI to the Walnut module name (path-relative, no extension).
     * e.g. file:///home/user/project/src/foo/bar.nut → "foo/bar"
     */
    public function uriToModuleName(string $uri): string;

    /**
     * Convert a Walnut module name back to a file URI.
     */
    public function moduleNameToUri(string $moduleName): string;
}
