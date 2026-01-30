<?php

namespace Walnut\Lang\Almond\AST\Blueprint\Parser;

use Walnut\Lib\Walex\Token;

interface Lexer {
	/** @return array<Token> */
	public function tokensFromSource(string $sourceCode): array;
}