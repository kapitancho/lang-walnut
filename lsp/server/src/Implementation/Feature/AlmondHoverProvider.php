<?php

declare(strict_types=1);

namespace Walnut\Lang\Lsp\Implementation\Feature;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\Userland\UserlandMethod;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\NamedType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Userland\UserlandTypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\TypeName;
use Walnut\Lang\Lsp\Blueprint\Document\CompilationSnapshot;
use Walnut\Lang\Lsp\Blueprint\Feature\HoverProvider;
use Walnut\Lang\Lsp\Implementation\Support\LspPositionConverter;

final readonly class AlmondHoverProvider implements HoverProvider {

    public function hover(CompilationSnapshot $snapshot, int $line, int $character): array|null {
        $offset   = LspPositionConverter::positionToOffset($snapshot->sourceText, $line, $character);
        $elements = $snapshot->codeIndex->elementsAtOffset($snapshot->moduleName, $offset);

        // Priority 1: narrowest Expression with a tracked type
        foreach ($elements as $element) {
            if (!($element instanceof Expression)) {
                continue;
            }
            $type = $snapshot->contextScope->typeOf($element);
            if ($type === null) {
                continue;
            }
            $location = $snapshot->codeIndex->getSourceLocation($element);
            return [
                'contents' => ['kind' => 'markdown', 'value' => "```walnut\n{$type}\n```"],
                'range'    => $location !== null ? LspPositionConverter::locationToRange($location) : null,
            ];
        }

        $typeRegistry = $snapshot->compilationResult->programContext->userlandTypeRegistry;

        // Priority 2: UserlandMethod → show its signature
        foreach ($elements as $element) {
            if (!($element instanceof UserlandMethod)) {
                continue;
            }
            $location = $snapshot->codeIndex->getSourceLocation($element);
            $sig = $this->methodSignature($element);
            return [
                'contents' => ['kind' => 'markdown', 'value' => "```walnut\n{$sig}\n```"],
                'range'    => $location !== null ? LspPositionConverter::locationToRange($location) : null,
            ];
        }

        // Priority 3: TypeName → show the type definition
        foreach ($elements as $element) {
            if (!($element instanceof TypeName)) {
                continue;
            }
            $namedType = $this->lookupType($typeRegistry, $element);
            if ($namedType === null) {
                continue;
            }
            $location = $snapshot->codeIndex->getSourceLocation($element);
            return [
                'contents' => ['kind' => 'markdown', 'value' => "```walnut\n{$namedType}\n```"],
                'range'    => $location !== null ? LspPositionConverter::locationToRange($location) : null,
            ];
        }

        return null;
    }

    private function methodSignature(UserlandMethod $method): string {
        return sprintf(
            '%s->%s: %s => %s',
            $method->targetType,
            $method->methodName,
            $method->parameterType,
            $method->returnType,
        );
    }

    private function lookupType(UserlandTypeRegistry $registry, TypeName $name): NamedType|null {
        try {
            return $registry->withName($name);
        } catch (\Throwable) {
            return null;
        }
    }
}
