<?php

declare(strict_types=1);

namespace Walnut\Lang\Lsp\Implementation\Feature;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Function\FunctionBody;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\UnknownMethod;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\Userland\UserlandMethod;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\MethodName;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\VariableName;
use Walnut\Lang\Almond\Engine\Blueprint\Program\ProgramContext;
use Walnut\Lang\Lsp\Blueprint\Document\CompilationSnapshot;
use Walnut\Lang\Lsp\Blueprint\Feature\CompletionProvider;
use Walnut\Lang\Lsp\Blueprint\Support\LspLocationConverter;

final readonly class AlmondCompletionProvider implements CompletionProvider {

    /** LSP CompletionItemKind constants */
    private const int KIND_METHOD   = 2;
    private const int KIND_VARIABLE = 6;
    private const int KIND_CLASS    = 7;

    /**
     * Built-in Walnut types that do not appear in userlandTypeRegistry but are
     * valid in type-annotation positions.
     */
    private const array BUILTIN_TYPES = [
        'Integer', 'Real', 'String', 'Bytes', 'Boolean', 'True', 'False',
        'Null', 'Nothing', 'Any', 'Optional', 'Error', 'Result',
        'Array', 'Map', 'Set', 'Tuple', 'Record',
    ];

    public function __construct(
        private LspLocationConverter $converter,
    ) {}

    public function completions(
        CompilationSnapshot $snapshot,
        int                 $line,
        int                 $character,
        string              $triggerCharacter,
        string              $liveSourceText,
    ): array {
        if ($triggerCharacter === '>') {
            return $this->methodCompletions($snapshot, $line, $character, $liveSourceText);
        }
        return [
            ...$this->typeNameCompletions($snapshot),
            ...$this->variableCompletions($snapshot, $line, $character),
        ];
    }

    // -------------------------------------------------------------------------
    // Method completion (triggered after '>')
    // -------------------------------------------------------------------------

    /**
     * @return list<array<string, mixed>>
     *
     * Uses $liveSourceText (the editor's current text) for offset computation and
     * the '->' detection, because by the time this runs the snapshot may still
     * reflect the state before the user typed '->'.
     * Positions before the '->' are identical in both texts, so the snapshot's
     * code index can be queried with the same numeric offset.
     */
    private function methodCompletions(
        CompilationSnapshot $snapshot,
        int                 $line,
        int                 $character,
        string              $liveSourceText,
    ): array {
        // Compute offset from the LIVE text so line/char resolve correctly even
        // when the snapshot is one version behind.
        $offset = $this->converter->positionToOffset($liveSourceText, $line, $character);

        // The '>' is at offset-1, '-' at offset-2.  The expression target ends before that.
        if ($offset < 3
            || $liveSourceText[$offset - 2] !== '-'
            || $liveSourceText[$offset - 1] !== '>'
        ) {
            return [];
        }

        // Since '->' was appended at the cursor, every byte before it has the same
        // offset in the live text and in the last compiled snapshot — safe to reuse.
        $elements = $snapshot->codeIndex->elementsAtOffset($snapshot->moduleName, $offset - 3);
        $targetExpr = array_find($elements, fn($e): bool => $e instanceof Expression);
        if ($targetExpr === null) {
            return [];
        }

        $targetType = $snapshot->contextScope->typeOf($targetExpr);
        if ($targetType === null) {
            return [];
        }

        return $this->methodsForType($targetType, $snapshot->compilationResult->programContext);
    }

    /**
     * Enumerate all userland methods applicable to $targetType.
     * Uses methodForType() (which checks both native and userland registries) to filter.
     *
     * Native methods are not enumerable, so only userland method names are tested.
     *
     * @return list<array<string, mixed>>
     */
    private function methodsForType(Type $targetType, ProgramContext $programContext): array {
        $items = [];

        foreach ($programContext->userlandMethodRegistry->allMethods() as $name => $methods) {
            $methodName = new MethodName($name);
            $found = $programContext->methodContext->methodForType($targetType, $methodName);
            if ($found instanceof UnknownMethod) {
                continue;
            }

            // Pick the first overload for the detail string
            /** @var UserlandMethod|null $method */
            $method = array_first($methods);
            $detail = $method !== null
                ? (string)$method->parameterType . ' => ' . (string)$method->returnType
                : '';

            $items[] = [
                'label'  => $name,
                'kind'   => self::KIND_METHOD,
                'detail' => $detail,
            ];
        }

        return $items;
    }

    // -------------------------------------------------------------------------
    // Type-name completion
    // -------------------------------------------------------------------------

    /** @return list<array<string, mixed>> */
    private function typeNameCompletions(CompilationSnapshot $snapshot): array {
        $items = [];

        foreach ($snapshot->compilationResult->programContext->userlandTypeRegistry->all() as $name => $type) {
            $items[] = [
                'label'  => $name,
                'kind'   => self::KIND_CLASS,
                'detail' => (string)$type,
            ];
        }

        foreach (self::BUILTIN_TYPES as $name) {
            $items[] = ['label' => $name, 'kind' => self::KIND_CLASS];
        }

        return $items;
    }

    // -------------------------------------------------------------------------
    // Variable completion
    // -------------------------------------------------------------------------

    /** @return list<array<string, mixed>> */
    private function variableCompletions(CompilationSnapshot $snapshot, int $line, int $character): array {
        $offset = $this->converter->positionToOffset($snapshot->sourceText, $line, $character);

        $elements = $snapshot->codeIndex->elementsAtOffset($snapshot->moduleName, $offset);

        // Prefer the innermost Expression at the cursor: its stored scope reflects
        // exactly which variables are visible at that point in the analysis, so it
        // correctly respects function boundaries (no leaking of outer-scope variables
        // into a method body that cannot see them).
        // Fall back to the innermost FunctionBody only when no Expression covers the cursor.
        $target = array_find($elements, fn($e): bool => $e instanceof Expression)
               ?? array_find($elements, fn($e): bool => $e instanceof FunctionBody);

        if ($target === null) {
            return [];
        }

        $scope = $snapshot->contextScope->scopeAt($target);

        if ($scope === null) {
            return [];
        }

        $items = [];
        foreach ($scope->variables() as $varIdentifier) {
            try {
                $varName = new VariableName($varIdentifier);
                $type    = $scope->typeOf($varName);
            } catch (\Throwable) {
                continue;
            }

            $items[] = [
                'label'  => $varIdentifier,
                'kind'   => self::KIND_VARIABLE,
                'detail' => $type !== null ? (string)$type : '',
            ];
        }

        return $items;
    }
}
