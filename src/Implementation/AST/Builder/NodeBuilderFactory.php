<?php

namespace Walnut\Lang\Implementation\AST\Builder;

use Walnut\Lang\Blueprint\AST\Builder\NodeBuilderFactory as NodeBuilderFactoryInterface;
use Walnut\Lang\Blueprint\AST\Parser\ParserState;
use Walnut\Lib\Walex\Token;

final readonly class NodeBuilderFactory implements NodeBuilderFactoryInterface {

	/** @param Token[] $tokens */
	public function newBuilder(
		string $moduleName,
		array $tokens,
		ParserState $state
	): NodeBuilder {
		return new NodeBuilder($moduleName, $tokens, $state);
	}
}