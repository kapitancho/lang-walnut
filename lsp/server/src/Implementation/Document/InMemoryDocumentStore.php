<?php

declare(strict_types=1);

namespace Walnut\Lang\Lsp\Implementation\Document;

use Walnut\Lang\Lsp\Blueprint\Document\DocumentStore;

/**
 * Keeps the live source text for all currently open documents in memory.
 *
 * URI ↔ module-name conversion assumes the project root is known at
 * construction time and that Walnut source files sit under $sourceRoot
 * with the `.nut` extension.
 */
final class InMemoryDocumentStore implements DocumentStore {

    /** @var array<string, array{version: int, content: string}> keyed by URI */
    private array $documents = [];

    public function __construct(
        /** Absolute path to the Walnut source root (no trailing slash). */
        private readonly string $sourceRoot,
    ) {}

    public function update(string $uri, int $version, string $content): void {
        $this->documents[$uri] = ['version' => $version, 'content' => $content];
    }

    public function remove(string $uri): void {
        unset($this->documents[$uri]);
    }

    public function get(string $uri): string|null {
        return $this->documents[$uri]['content'] ?? null;
    }

    public function isOpen(string $uri): bool {
        return isset($this->documents[$uri]);
    }

    public function uriToModuleName(string $uri): string {
        // file:///abs/path/to/src/foo/bar.nut → "foo/bar"
        $path = $this->uriToPath($uri);
        $relative = ltrim(str_replace($this->sourceRoot, '', $path), '/');
        return str_ends_with($relative, '.nut') ? substr($relative, 0, -4) : $relative;
    }

    public function moduleNameToUri(string $moduleName): string {
        return $this->pathToUri($this->sourceRoot . '/' . $moduleName . '.nut');
    }

    // -------------------------------------------------------------------------

    private function uriToPath(string $uri): string {
        return rawurldecode(str_replace('file://', '', $uri));
    }

    private function pathToUri(string $path): string {
        // Encode each segment individually to preserve slashes.
        // On Unix the path starts with '/', producing file:///segment/...
        return 'file://' . implode('/', array_map('rawurlencode', explode('/', $path)));
    }
}
