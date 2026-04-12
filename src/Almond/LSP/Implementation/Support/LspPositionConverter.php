<?php

declare(strict_types=1);

namespace Walnut\Lang\Almond\LSP\Implementation\Support;

use Walnut\Lang\Almond\AST\Blueprint\Node\SourceLocation;
use Walnut\Lang\Almond\LSP\Blueprint\Document\CompilationSnapshot;
use Walnut\Lang\Almond\LSP\Blueprint\Support\LspLocationConverter;

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
        $path = rawurldecode(str_replace('file://', '', $snapshot->uri));
        // Test file: moduleName = "foo-test", URI ends with "foo.test.nut"
        if (str_ends_with($snapshot->moduleName, '-test')) {
            $baseName = substr($snapshot->moduleName, 0, -5);
            $suffix = '/' . $baseName . '.test.nut';
            if (str_ends_with($path, $suffix)) {
                return substr($path, 0, -strlen($suffix));
            }
        }
        // Template file: moduleName = "foo", URI ends with "foo.nut.html"
        $suffix = '/' . $snapshot->moduleName . '.nut.html';
        if (str_ends_with($path, $suffix)) {
            return substr($path, 0, -strlen($suffix));
        }
        // Normal .nut file
        $suffix = '/' . $snapshot->moduleName . '.nut';
        if (str_ends_with($path, $suffix)) {
            return substr($path, 0, -strlen($suffix));
        }
        return dirname($path);
    }

    public function moduleNameToUri(string $sourceRoot, string $moduleName): string {
        // Test module: "foo-test" → "foo.test.nut"
        if (str_ends_with($moduleName, '-test')) {
            $baseName = substr($moduleName, 0, -5);
            $path = sprintf('%s/%s.test.nut', $sourceRoot, $baseName);
        } else {
            $path = sprintf('%s/%s.nut', $sourceRoot, $moduleName);
        }
        return 'file://' . implode('/', array_map(rawurlencode(...), explode('/', $path)));
    }
}
