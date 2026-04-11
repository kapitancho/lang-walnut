<?php

declare(strict_types=1);

namespace Walnut\Lang\Lsp\Implementation\Support;

use Walnut\Lang\Almond\AST\Blueprint\Node\SourceLocation;
use Walnut\Lang\Lsp\Blueprint\Document\CompilationSnapshot;

/**
 * Static helpers for converting between LSP positions and Almond source locations.
 *
 * LSP positions are 0-based (line, character).
 * Almond SourcePosition is 1-based (line, column) with an additional byte offset.
 *
 * Note: character offsets in LSP are UTF-16 code units, but Almond uses byte offsets.
 * For ASCII/single-byte content this is equivalent; proper UTF-16 handling is a TODO.
 */
final class LspPositionConverter {

    /**
     * Convert a 0-based LSP (line, character) position to the byte offset
     * within $sourceText where that position falls.
     */
    public static function positionToOffset(string $sourceText, int $line, int $character): int {
        $offset = 0;
        $currentLine = 0;
        $len = strlen($sourceText);

        while ($currentLine < $line && $offset < $len) {
            if ($sourceText[$offset] === "\n") {
                $currentLine++;
            }
            $offset++;
        }
        // Clamp to source length to avoid over-shooting on the last line
        return min($offset + $character, $len);
    }

    /**
     * Convert a Walnut SourceLocation to an LSP Range (0-based).
     *
     * @return array{start: array{line: int, character: int}, end: array{line: int, character: int}}
     */
    public static function locationToRange(SourceLocation $location): array {
        return [
            'start' => [
                'line'      => $location->startPosition->line - 1,
                'character' => $location->startPosition->column - 1,
            ],
            'end' => [
                'line'      => $location->endPosition->line - 1,
                'character' => $location->endPosition->column - 1,
            ],
        ];
    }

    /**
     * Convert a Walnut SourceLocation to an LSP Location { uri, range }.
     * $sourceRoot is the absolute path of the walnut-src folder (no trailing slash).
     *
     * @return array{uri: string, range: array<string, mixed>}
     */
    public static function locationToLspLocation(SourceLocation $location, string $sourceRoot): array {
        return [
            'uri'   => self::moduleNameToUri($sourceRoot, $location->moduleName),
            'range' => self::locationToRange($location),
        ];
    }

    /**
     * Derive the absolute walnut-src root from a snapshot.
     * Works by stripping the module name and '.nut' extension from the snapshot URI's path.
     *
     * Example:
     *   URI='file:///project/walnut-src/foo/bar.nut', moduleName='foo/bar'
     *   → '/project/walnut-src'
     */
    public static function sourceRootFromSnapshot(CompilationSnapshot $snapshot): string {
        $path   = rawurldecode(str_replace('file://', '', $snapshot->uri));
        $suffix = '/' . $snapshot->moduleName . '.nut';
        return str_ends_with($path, $suffix)
            ? substr($path, 0, -strlen($suffix))
            : dirname($path);
    }

    /**
     * Build a file:// URI from a source-root path and a Walnut module name.
     */
    public static function moduleNameToUri(string $sourceRoot, string $moduleName): string {
        $path = $sourceRoot . '/' . $moduleName . '.nut';
        return 'file://' . implode('/', array_map('rawurlencode', explode('/', $path)));
    }
}
