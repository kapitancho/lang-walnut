<?php

namespace Walnut\Lang\Blueprint\AST\Builder;

use Walnut\Lang\Blueprint\AST\Parser\ParserState;
use Walnut\Lib\Walex\Token;

interface NodeBuilderFactory {
	public function newBuilder(
		SourceLocator $sourceLocator
	): NodeBuilder;
}
