<?php

namespace Walnut\Lang\Blueprint\Compilation;

use Walnut\Lib\Walex\Token;

interface Lexer {
	/** @return array<Token> */
	public function tokensFromSource(string $sourceCode): array;
}