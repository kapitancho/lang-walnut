<?php

declare(strict_types=1);

namespace Walnut\Lang\Almond\LSP\Implementation\Feature;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\UnknownMethod;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\Userland\UserlandMethod;
use Walnut\Lang\Almond\Engine\Implementation\Code\Expression\MethodCallExpression;
use Walnut\Lang\Almond\LSP\Blueprint\Document\CompilationSnapshot;
use Walnut\Lang\Almond\LSP\Blueprint\Feature\SignatureHelpProvider;
use Walnut\Lang\Almond\LSP\Blueprint\Support\LspLocationConverter;

final readonly class AlmondSignatureHelpProvider implements SignatureHelpProvider {

    public function __construct(
        private LspLocationConverter $converter,
    ) {}

    public function signatureHelp(CompilationSnapshot $snapshot, int $line, int $character): array|null {
        $offset   = $this->converter->positionToOffset($snapshot->sourceText, $line, $character);
        $elements = $snapshot->codeIndex->elementsAtOffset($snapshot->moduleName, $offset);

        // elementsAtOffset() returns narrowest-first; the first MethodCallExpression
        // found is therefore the innermost one that encloses the cursor — exactly the
        // call whose argument the user is currently editing.
        $mce = array_find($elements, fn($e): bool => $e instanceof MethodCallExpression);
        if ($mce === null) {
            return null;
        }

        $targetType = $snapshot->contextScope->typeOf($mce->target);
        if ($targetType === null) {
            return null;
        }

        $programContext = $snapshot->compilationResult->programContext;
        $method = $programContext->methodContext->methodForType($targetType, $mce->methodName);
        if ($method instanceof UnknownMethod) {
            return null;
        }

        $name = (string)$mce->methodName;

        if ($method instanceof UserlandMethod) {
            $paramStr   = (string)$method->parameterType;
            $retStr     = (string)$method->returnType;
            $label      = $name . ': ' . $paramStr . ' => ' . $retStr;
            // Character offsets of the parameter type within the label string.
            $paramStart = strlen($name) + 2; // skip "name: "
            $paramEnd   = $paramStart + strlen($paramStr);
        } else {
            // Native method: parameterType is not exposed; show the resolved return type.
            $returnType = $snapshot->contextScope->typeOf($mce);
            $retStr     = $returnType !== null ? (string)$returnType : '?';
            $label      = $name . ' => ' . $retStr;
            $paramStart = 0;
            $paramEnd   = 0;
        }

        return [
            'signatures' => [[
                'label'      => $label,
                'parameters' => [[
                    'label' => [$paramStart, $paramEnd],
                ]],
            ]],
            'activeSignature' => 0,
            'activeParameter' => 0,
        ];
    }
}
