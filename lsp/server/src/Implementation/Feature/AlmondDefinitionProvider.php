<?php

declare(strict_types=1);

namespace Walnut\Lang\Lsp\Implementation\Feature;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\Userland\UserlandMethod;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Userland\UserlandTypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\TypeName;
use Walnut\Lang\Almond\Engine\Implementation\Code\Expression\VariableAssignmentExpression;
use Walnut\Lang\Almond\Engine\Implementation\Code\Expression\VariableNameExpression;
use Walnut\Lang\Lsp\Blueprint\Document\CompilationSnapshot;
use Walnut\Lang\Lsp\Blueprint\Feature\DefinitionProvider;
use Walnut\Lang\Lsp\Blueprint\Support\LspLocationConverter;

final readonly class AlmondDefinitionProvider implements DefinitionProvider {

    public function __construct(
        private LspLocationConverter $converter,
    ) {}

    public function definition(CompilationSnapshot $snapshot, int $line, int $character): array|null {
        $offset       = $this->converter->positionToOffset($snapshot->sourceText, $line, $character);
        $elements     = $snapshot->codeIndex->elementsAtOffset($snapshot->moduleName, $offset);
        $sourceRoot   = $this->converter->sourceRootFromSnapshot($snapshot);
        $typeRegistry = $snapshot->compilationResult->programContext->userlandTypeRegistry;

        foreach ($elements as $element) {
            // TypeName reference → resolve to the NamedType and find where IT was defined
            if ($element instanceof TypeName) {
                $location = $this->typeDefinitionLocation($typeRegistry, $element, $snapshot);
                if ($location !== null) {
                    return $this->converter->locationToLspLocation($location, $sourceRoot);
                }
            }

            // UserlandMethod → the method object itself was mapped at its definition site
            if ($element instanceof UserlandMethod) {
                $location = $snapshot->codeIndex->getSourceLocation($element);
                if ($location !== null) {
                    return $this->converter->locationToLspLocation($location, $sourceRoot);
                }
            }

            // Variable reference → find the closest assignment before the cursor
            if ($element instanceof VariableNameExpression) {
                $varName = (string)$element->variableName;
                $assignment = $snapshot->codeIndex->findLast(
                    $snapshot->moduleName,
                    static fn($e, int $start): bool =>
                        $e instanceof VariableAssignmentExpression &&
                        (string)$e->variableName === $varName &&
                        $start < $offset
                );
                if ($assignment !== null) {
                    $location = $snapshot->codeIndex->getSourceLocation($assignment);
                    if ($location !== null) {
                        return $this->converter->locationToLspLocation($location, $sourceRoot);
                    }
                }
            }
        }

        return null;
    }

    private function typeDefinitionLocation(
        UserlandTypeRegistry $registry,
        TypeName             $name,
        CompilationSnapshot  $snapshot,
    ): ?\Walnut\Lang\Almond\AST\Blueprint\Node\SourceLocation {
        try {
            $namedType = $registry->withName($name);
        } catch (\Throwable) {
            return null; // built-in type — no Walnut source definition
        }
        // The NamedType object is mapped at its definition site in the NodeCodeMapper
        return $snapshot->codeIndex->getSourceLocation($namedType);
    }
}
