<?php

declare(strict_types=1);

namespace Walnut\Lang\Lsp\Implementation\Support;

use Walnut\Lang\Almond\AST\Blueprint\Node\SourceLocation;
use Walnut\Lang\Lsp\Blueprint\Document\CompilationSnapshot;
use Walnut\Lang\Lsp\Blueprint\Support\LspLocationConverter;

final readonly class LspPositionConverter implements LspLocationConverter {

    public function positionToOffset(string $sourceText, int $line, int $character): int {
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

    public function locationToRange(SourceLocation $location): array {
        return [
            'start' => [
                'line'      => $location->startPosition->line - 1,   // LSP is 0-based
                'character' => $location->startPosition->column - 1,
            ],
            'end' => [
                'line'      => $location->endPosition->line - 1,
                'character' => $location->endPosition->column - 1,
            ],
        ];
    }

    public function locationToLspLocation(SourceLocation $location, string $sourceRoot): array {
        return [
            'uri'   => $this->moduleNameToUri($sourceRoot, $location->moduleName),
            'range' => $this->locationToRange($location),
        ];
    }

    public function sourceRootFromSnapshot(CompilationSnapshot $snapshot): string {
        $path   = rawurldecode(str_replace('file://', '', $snapshot->uri));
        $suffix = '/' . $snapshot->moduleName . '.nut';
        return str_ends_with($path, $suffix)
            ? substr($path, 0, -strlen($suffix))
            : dirname($path);
    }

    public function moduleNameToUri(string $sourceRoot, string $moduleName): string {
        $path = sprintf('%s/%s.nut', $sourceRoot, $moduleName);
        return 'file://' . implode('/', array_map(rawurlencode(...), explode('/', $path)));
    }
}
