<?php

declare(strict_types=1);

namespace Walnut\Lang\Almond\LSP\Implementation\Document;

use Walnut\Lang\Almond\LSP\Blueprint\Document\DocumentStore;

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

    public function uriToSourceKey(string $uri): string {
        // file:///abs/path/to/src/foo/bar.test.nut → "foo/bar.test.nut"
        $path = $this->uriToPath($uri);
        return ltrim(str_replace($this->sourceRoot, '', $path), '/');
    }

    public function uriToModuleName(string $uri): string {
        $relative = $this->uriToSourceKey($uri);
        // foo/bar.test.nut → "foo/bar-test"  (TestPrecompiler convention)
        if (str_ends_with($relative, '.test.nut')) {
            return substr($relative, 0, -9) . '-test';
        }
        // foo/bar.nut.html → "foo/bar"  (TemplatePrecompiler convention)
        if (str_ends_with($relative, '.nut.html')) {
            return substr($relative, 0, -9);
        }
        // foo/bar.nut → "foo/bar"
        return str_ends_with($relative, '.nut') ? substr($relative, 0, -4) : $relative;
    }

    public function moduleNameToUri(string $moduleName): string {
        // "foo/bar-test" → foo/bar.test.nut
        if (str_ends_with($moduleName, '-test')) {
            $baseName = substr($moduleName, 0, -5);
            return $this->pathToUri($this->sourceRoot . '/' . $baseName . '.test.nut');
        }
        // "foo" could be foo.nut or foo.nut.html — prefer the template when it exists
        $templatePath = $this->sourceRoot . '/' . $moduleName . '.nut.html';
        if (file_exists($templatePath)) {
            return $this->pathToUri($templatePath);
        }
        return $this->pathToUri($this->sourceRoot . '/' . $moduleName . '.nut');
    }

    // -------------------------------------------------------------------------

    private function uriToPath(string $uri): string {
        return rawurldecode(str_replace('file://', '', $uri));
    }

    private function pathToUri(string $path): string {
        // Encode each segment individually to preserve slashes.
        // On Unix the path starts with '/', producing file:///segment/...
        return 'file://' . implode('/', array_map(rawurlencode(...), explode('/', $path)));
    }
}
