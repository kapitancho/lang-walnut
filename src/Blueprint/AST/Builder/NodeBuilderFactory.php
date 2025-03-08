<?php

namespace Walnut\Lang\Blueprint\AST\Builder;

use Walnut\Lang\Blueprint\AST\Parser\ParserState;
use Walnut\Lib\Walex\Token;

interface NodeBuilderFactory {
	/** @param Token[] $tokens */
	public function newBuilder(
		string $moduleName,
		array $tokens,
		ParserState $state
	): NodeBuilder;
}
