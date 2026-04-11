<?php

declare(strict_types=1);

namespace Walnut\Lang\Lsp\Implementation\Feature;

use Walnut\Lang\Almond\Runner\Blueprint\Compilation\Error\CompilationFailure;
use Walnut\Lang\Lsp\Blueprint\Document\CompilationSnapshot;
use Walnut\Lang\Lsp\Blueprint\Feature\DiagnosticsProvider;
use Walnut\Lang\Lsp\Blueprint\Support\LspLocationConverter;

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

    public function __construct(
        private LspLocationConverter $converter,
    ) {}

    public function diagnosticsFor(CompilationSnapshot $snapshot): array {
        $result = $snapshot->compilationResult;

        if (!($result instanceof CompilationFailure)) {
            return [];
        }

        $sourceRoot  = $this->converter->sourceRootFromSnapshot($snapshot);
        $diagnostics = [];

        foreach ($result->errors as $error) {
            // An error may span multiple locations (e.g. type mismatch shows both sides).
            // LSP allows one primary range + relatedInformation for extra locations.
            $locations = $error->sourceLocations;
            $primaryLocation = array_first($locations);

            if ($primaryLocation !== null && $primaryLocation->moduleName !== $snapshot->moduleName) {
                // Error location is in a dependency file — skip (reported when that file is compiled)
                continue;
            }

            // If there is no source location (e.g. moduleDependencyMissing), show
            // the error at the very start of the file so it is not silently dropped.
            $range = $primaryLocation !== null
                ? $this->converter->locationToRange($primaryLocation)
                : ['start' => ['line' => 0, 'character' => 0], 'end' => ['line' => 0, 'character' => 0]];

            $related = array_map(
                fn($loc): array => [
                    'location' => $this->converter->locationToLspLocation($loc, $sourceRoot),
                    'message'  => $error->errorMessage,
                ],
                array_slice($locations, 1)
            );

            $diagnostic = [
                'range'    => $range,
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
}
