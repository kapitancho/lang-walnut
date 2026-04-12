<?php

declare(strict_types=1);

namespace Walnut\Lang\Almond\LSP\Implementation\Feature;

use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\EnumerationValueName;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\MethodName;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\TypeName;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\VariableName;
use Walnut\Lang\Almond\LSP\Blueprint\Document\CompilationSnapshot;
use Walnut\Lang\Almond\LSP\Blueprint\Feature\SemanticTokensProvider;

final readonly class AlmondSemanticTokensProvider implements SemanticTokensProvider {

    // Token type indices — must match tokenTypes() order.
    private const int TYPE_TYPE        = 0;
    private const int TYPE_ENUM_MEMBER = 1;
    private const int TYPE_METHOD      = 2;
    private const int TYPE_VARIABLE    = 3;

    public function tokenTypes(): array {
        return ['type', 'enumMember', 'method', 'variable'];
    }

    public function tokenModifiers(): array {
        return [];
    }

    public function semanticTokens(CompilationSnapshot $snapshot): array {
        $source     = $snapshot->sourceText;
        $moduleName = $snapshot->moduleName;
        $seen       = [];
        $raw        = [];

        // Scan the raw source text for identifier-shaped tokens.
        // Using the source text directly gives exact byte positions; we then
        // ask the positional index what semantic category each match belongs to.
        // This avoids relying on the AST source-location machinery, which is
        // designed for node spans rather than pinpoint identifier positions.
        preg_match_all('/[a-zA-Z_#$%][a-zA-Z0-9_]*/', $source, $matches, PREG_OFFSET_CAPTURE);

        foreach ($matches[0] as [$text, $byteOffset]) {
            // Ask the code index what's at this byte position.
            // elementsAtOffset() is the same lookup that powers hover/definition
            // and is known-correct.  Elements are returned narrowest-first, so
            // the first identifier-typed element wins.
            $elements = $snapshot->codeIndex->elementsAtOffset($moduleName, $byteOffset);

            $typeIdx = null;
            foreach ($elements as $element) {
                if ($element instanceof TypeName
                    && (string)$element === $text
                ) {
                    $typeIdx = self::TYPE_TYPE;
                    break;
                }
                if ($element instanceof EnumerationValueName
                    && (string)$element === $text
                ) {
                    $typeIdx = self::TYPE_ENUM_MEMBER;
                    break;
                }
                if ($element instanceof MethodName
                    && (string)$element === $text
                ) {
                    $typeIdx = self::TYPE_METHOD;
                    break;
                }
                if ($element instanceof VariableName
                    && (string)$element === $text
                ) {
                    $typeIdx = self::TYPE_VARIABLE;
                    break;
                }
            }

            if ($typeIdx === null) {
                continue;
            }

            // Compute 0-based line and column from the byte offset in the source.
            $before = substr($source, 0, $byteOffset);
            $line   = substr_count($before, "\n");
            $lastNl = strrpos($before, "\n");
            $col    = $lastNl === false ? $byteOffset : $byteOffset - $lastNl - 1;
            $length = strlen($text);

            $posKey = $line . ':' . $col;
            if (isset($seen[$posKey])) {
                continue;
            }
            $seen[$posKey] = true;

            $raw[] = [$line, $col, $length, $typeIdx, 0];
        }

        // Tokens must be sorted by position for the delta encoding to be correct.
        usort($raw, static fn(array $a, array $b): int => $a[0] <=> $b[0] ?: $a[1] <=> $b[1]);

        // Delta-encode into the LSP flat 5-integer-per-token format.
        $data     = [];
        $prevLine = 0;
        $prevCol  = 0;

        foreach ($raw as [$line, $col, $length, $typeIdx, $modifiers]) {
            $deltaLine = $line - $prevLine;
            $deltaCol  = $deltaLine === 0 ? $col - $prevCol : $col;
            $data[]    = $deltaLine;
            $data[]    = $deltaCol;
            $data[]    = $length;
            $data[]    = $typeIdx;
            $data[]    = $modifiers;
            $prevLine  = $line;
            $prevCol   = $col;
        }

        return $data;
    }
}
