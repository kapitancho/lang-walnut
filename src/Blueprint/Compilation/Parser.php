<?php

namespace Walnut\Lang\Blueprint\Compilation;

use Walnut\Lang\Blueprint\AST\Builder\ModuleNodeBuilder;
use Walnut\Lang\Blueprint\AST\Builder\NodeBuilderFactory;
use Walnut\Lang\Blueprint\AST\Node\Module\ModuleNode;
use Walnut\Lib\Walex\Token;

interface Parser {
	/** @param Token[] $tokens */
	public function parseAndBuildCodeFromTokens(
		NodeBuilderFactory $nodeBuilderFactory,
		ModuleNodeBuilder $moduleNodeBuilder,
		array $tokens,
		string $moduleName
	): ModuleNode;
}