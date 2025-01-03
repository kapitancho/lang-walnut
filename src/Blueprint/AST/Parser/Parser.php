<?php

namespace Walnut\Lang\Blueprint\AST\Parser;

use Walnut\Lang\Blueprint\AST\Builder\ModuleNodeBuilder;
use Walnut\Lang\Blueprint\AST\Builder\NodeBuilderFactory;
use Walnut\Lang\Blueprint\AST\Node\Module\ModuleNode;
use Walnut\Lib\Walex\Token;

interface Parser {
	/**
	  * @param Token[] $tokens
	  * @throws ParserException
	  */
	public function parseAndBuildCodeFromTokens(
		NodeBuilderFactory $nodeBuilderFactory,
		ModuleNodeBuilder $moduleNodeBuilder,
		array $tokens,
		string $moduleName
	): ModuleNode;
}