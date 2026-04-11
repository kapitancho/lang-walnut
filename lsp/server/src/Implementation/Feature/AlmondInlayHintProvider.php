<?php

declare(strict_types=1);

namespace Walnut\Lang\Lsp\Implementation\Feature;

use Walnut\Lang\Almond\Engine\Implementation\Code\Expression\VariableAssignmentExpression;
use Walnut\Lang\Lsp\Blueprint\Document\CompilationSnapshot;
use Walnut\Lang\Lsp\Blueprint\Feature\InlayHintProvider;
use Walnut\Lang\Lsp\Blueprint\Support\LspLocationConverter;

final readonly class AlmondInlayHintProvider implements InlayHintProvider {

    /** LSP InlayHintKind: 1 = Type */
    private const int KIND_TYPE = 1;

    /**
     * Type labels longer than this many characters are omitted to avoid clutter.
     * The label includes the leading ': ', so this caps the total appended text.
     */
    private const int MAX_LABEL_LENGTH = 60;

    public function __construct(
        private LspLocationConverter $converter,
    ) {}

    public function inlayHints(CompilationSnapshot $snapshot, int $startLine, int $endLine): array {
        $items = [];

        foreach ($snapshot->codeIndex->allElements($snapshot->moduleName) as [, , $element]) {
            if (!($element instanceof VariableAssignmentExpression)) {
                continue;
            }

            $type = $snapshot->contextScope->typeOf($element->assignedExpression)
                 ?? $snapshot->contextScope->typeOf($element);
            if ($type === null) {
                continue;
            }

            $label = ': ' . (string)$type;
            if (strlen($label) > self::MAX_LABEL_LENGTH) {
                continue;
            }

            $location = $snapshot->codeIndex->getSourceLocation($element);
            if ($location === null) {
                continue;
            }

            // SourceLocation lines/columns are 1-based; LSP expects 0-based.
            $line = $location->startPosition->line   - 1;
            $col  = $location->startPosition->column - 1;

            if ($line < $startLine || $line > $endLine) {
                continue;
            }

            // Place the hint immediately after the variable name.
            $varLen    = strlen((string)$element->variableName);
            $character = $col + $varLen;

            $items[] = [
                'position'     => ['line' => $line, 'character' => $character],
                'label'        => $label,
                'kind'         => self::KIND_TYPE,
                'paddingLeft'  => false,
                'paddingRight' => true,
                'tooltip'      => (string)$type,
            ];
        }

        return $items;
    }
}
