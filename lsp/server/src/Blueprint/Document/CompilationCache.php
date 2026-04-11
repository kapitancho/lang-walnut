<?php

declare(strict_types=1);

namespace Walnut\Lang\Lsp\Blueprint\Document;

/**
 * Maintains three compilation snapshots per open document:
 *   - live      — most recent compile attempt (may have errors)
 *   - lastParsed — last parse-error-free result
 *   - lastValid  — last fully-valid (zero-error) result
 */
interface CompilationCache {

    public function store(CompilationSnapshot $snapshot): void;

    /** Remove all snapshots for $uri (called on textDocument/didClose). */
    public function evict(string $uri): void;

    /** Most recent compile result for $uri, or null if not compiled yet. */
    public function live(string $uri): CompilationSnapshot|null;

    /** Last parse-error-free result, or null. */
    public function lastParsed(string $uri): CompilationSnapshot|null;

    /** Last fully-valid result, or null. */
    public function lastValid(string $uri): CompilationSnapshot|null;

    /**
     * Returns source text for $moduleName from the best available tier
     * (lastValid → lastParsed → live). Returns null if not tracked.
     */
    public function bestSourceFor(string $moduleName): string|null;

    /**
     * Returns all URIs currently tracked (open and not yet evicted).
     * Used to build the peer source finder for cross-module compilation.
     *
     * @return list<string>
     */
    public function listUris(): array;
}
