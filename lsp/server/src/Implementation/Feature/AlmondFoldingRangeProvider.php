<?php

declare(strict_types=1);

namespace Walnut\Lang\Lsp\Implementation\Feature;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Function\FunctionBody;
use Walnut\Lang\Almond\Engine\Implementation\Code\Expression\SequenceExpression;
use Walnut\Lang\Lsp\Blueprint\Document\CompilationSnapshot;
use Walnut\Lang\Lsp\Blueprint\Feature\FoldingRangeProvider;

final readonly class AlmondFoldingRangeProvider implements FoldingRangeProvider {

    public function foldingRanges(CompilationSnapshot $snapshot): array {
        $seen  = [];
        $items = [];

        foreach ($snapshot->codeIndex->allElements($snapshot->moduleName) as [, , $element]) {
            if (!($element instanceof SequenceExpression) && !($element instanceof FunctionBody)) {
                continue;
            }

            $id = spl_object_id($element);
            if (isset($seen[$id])) {
                continue;
            }
            $seen[$id] = true;

            $location = $snapshot->codeIndex->getSourceLocation($element);
            if ($location === null) {
                continue;
            }

            // SourceLocation lines are 1-based; LSP expects 0-based.
            $startLine = $location->startPosition->line - 1;
            $endLine   = $location->endPosition->line   - 1;

            if ($endLine <= $startLine) {
                continue;
            }

            $items[] = [
                'startLine' => $startLine,
                'endLine'   => $endLine,
                'kind'      => 'region',
            ];
        }

        return $items;
    }
}
