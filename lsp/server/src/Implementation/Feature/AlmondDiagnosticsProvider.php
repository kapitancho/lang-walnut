<?php

declare(strict_types=1);

namespace Walnut\Lang\Lsp\Implementation\Feature;

use Walnut\Lang\Almond\AST\Blueprint\Node\SourceLocation;
use Walnut\Lang\Almond\Runner\Blueprint\Compilation\Error\CompilationFailure;
use Walnut\Lang\Lsp\Blueprint\Document\CompilationSnapshot;
use Walnut\Lang\Lsp\Blueprint\Feature\DiagnosticsProvider;

/**
 * Translates Almond compilation errors into LSP Diagnostic objects.
 *
 * LSP Diagnostic severity:
 *   1 = Error, 2 = Warning, 3 = Information, 4 = Hint
 *
 * All Walnut compilation errors are treated as errors (severity 1).
 * The errorType name is placed in the `code` field for easy filtering in the client.
 */
final readonly class AlmondDiagnosticsProvider implements DiagnosticsProvider {

    public function diagnosticsFor(CompilationSnapshot $snapshot): array {
        $result = $snapshot->compilationResult;

        if (!($result instanceof CompilationFailure)) {
            return [];
        }

        $diagnostics = [];

        foreach ($result->errors as $error) {
            // An error may span multiple locations (e.g. type mismatch shows both sides).
            // LSP allows one primary range + relatedInformation for extra locations.
            $locations = $error->sourceLocations;
            $primaryLocation = array_first($locations);

            if ($primaryLocation === null || $primaryLocation->moduleName !== $snapshot->moduleName) {
                // Error is in a dependency — skip (reported when that file is compiled)
                continue;
            }

            $related = array_map(
                fn(SourceLocation $loc): array => [
                    'location' => [
                        'uri'   => $snapshot->uri, // TODO: resolve cross-file URIs
                        'range' => $this->locationToRange($loc),
                    ],
                    'message' => $error->errorMessage,
                ],
                array_slice($locations, 1)
            );

            $diagnostic = [
                'range'    => $this->locationToRange($primaryLocation),
                'severity' => 1,
                'code'     => $error->errorType->name,
                'source'   => 'walnut',
                'message'  => $error->errorMessage,
            ];

            if ($related !== []) {
                $diagnostic['relatedInformation'] = $related;
            }

            $diagnostics[] = $diagnostic;
        }

        return $diagnostics;
    }

    private function locationToRange(SourceLocation $location): array {
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
}
