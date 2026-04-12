<?php

declare(strict_types=1);

namespace Walnut\Lang\Almond\LSP\Blueprint\Support;

use Walnut\Lang\Almond\AST\Blueprint\Node\SourceLocation;
use Walnut\Lang\Almond\LSP\Blueprint\Document\CompilationSnapshot;

/**
 * Converts between LSP wire-protocol positions/locations and Almond
 * source-level positions/locations.
 *
 * LSP positions are 0-based (line, character = UTF-16 code unit offset).
 * Almond positions are 1-based (line, column) plus a byte offset.
 *
 * Note: character/column offsets are treated as bytes here, which is
 * correct for ASCII content; full UTF-16 support is a not yet supported.
 */
interface LspLocationConverter {

    /**
     * Convert a 0-based LSP (line, character) position to the byte offset
     * within $sourceText where that position falls.
     */
    public function positionToOffset(string $sourceText, int $line, int $character): int;

    /**
     * Convert a Walnut SourceLocation to an LSP Range (0-based line/character).
     *
     * @return array{start: array{line: int, character: int}, end: array{line: int, character: int}}
     */
    public function locationToRange(SourceLocation $location): array;

    /**
     * Convert a Walnut SourceLocation to an LSP Location object { uri, range }.
     * $sourceRoot is the absolute path of the walnut-src folder (no trailing slash).
     *
     * @return array{uri: string, range: array<string, mixed>}
     */
    public function locationToLspLocation(SourceLocation $location, string $sourceRoot): array;

    /**
     * Derive the absolute source-root path from a compiled snapshot.
     * Works by stripping the module name and '.nut' extension from the
     * snapshot URI's file path.
     */
    public function sourceRootFromSnapshot(CompilationSnapshot $snapshot): string;

    /**
     * Build a file:// URI from a source-root path and a Walnut module name.
     */
    public function moduleNameToUri(string $sourceRoot, string $moduleName): string;
}
