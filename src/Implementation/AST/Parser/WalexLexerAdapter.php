<?php

namespace Walnut\Lang\Implementation\AST\Parser;

use Walnut\Lang\Blueprint\AST\Parser\Lexer as LexerInterface;
use Walnut\Lang\Implementation\AST\Parser\Token as CompilationToken;
use Walnut\Lib\Walex\Lexer;
use Walnut\Lib\Walex\Pattern;
use Walnut\Lib\Walex\Rule;
use Walnut\Lib\Walex\SpecialRuleTag;
use Walnut\Lib\Walex\Token;

final readonly class WalexLexerAdapter implements LexerInterface {
	private Lexer $lexer;

	public function __construct() {
        $this->lexer = new Lexer([
            ... array_map(
                static fn(CompilationToken $token): Rule => new Rule(
                    new Pattern($token->value),
                    $token->name
                ),
                CompilationToken::cases()
            ),
            new Rule(
                new Pattern('[\n]'),
                SpecialRuleTag::newLine
            ),
            new Rule(
                new Pattern('.'),
                SpecialRuleTag::skip
            )
        ]);
	}

    /**
     * @param string $sourceCode
     * @return array<Token>
     */
	public function tokensFromSource(string $sourceCode): array {
		return iterator_to_array(
            $this->lexer->getTokensFor($sourceCode)
        );
	}
}