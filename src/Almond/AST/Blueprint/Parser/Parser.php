<?php

namespace Walnut\Lang\Almond\AST\Blueprint\Parser;

use Walnut\Lang\Almond\AST\Blueprint\Node\Module\ModuleNode;
use Walnut\Lib\Walex\Token;

interface Parser {
	/**
	  * @param Token[] $tokens
	  * @throws ParserException
	  */
	public function parseAndBuildCodeFromTokens(
		array $tokens,
		string $moduleName
	): ModuleNode;
}