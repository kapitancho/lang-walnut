<?php

declare(strict_types=1);

namespace Walnut\Lang\Almond\LSP\Implementation\Feature;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Function\FunctionBody;
use Walnut\Lang\Almond\Engine\Implementation\Code\Expression\GroupExpression;
use Walnut\Lang\Almond\Engine\Implementation\Code\Expression\RecordExpression;
use Walnut\Lang\Almond\Engine\Implementation\Code\Expression\SequenceExpression;
use Walnut\Lang\Almond\Engine\Implementation\Code\Expression\SetExpression;
use Walnut\Lang\Almond\Engine\Implementation\Code\Expression\TupleExpression;
use Walnut\Lang\Almond\LSP\Blueprint\Document\CompilationSnapshot;
use Walnut\Lang\Almond\LSP\Blueprint\Feature\FoldingRangeProvider;

final readonly class AlmondFoldingRangeProvider implements FoldingRangeProvider {

    public function foldingRanges(CompilationSnapshot $snapshot): array {
        $seen  = [];
        $items = [];

        foreach ($snapshot->codeIndex->allElements($snapshot->moduleName) as [, , $element]) {
            if (!($element instanceof SequenceExpression)
                && !($element instanceof FunctionBody)
                && !($element instanceof GroupExpression)
                && !($element instanceof TupleExpression)
                && !($element instanceof RecordExpression)
                && !($element instanceof SetExpression)
            ) {
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
