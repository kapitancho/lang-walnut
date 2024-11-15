<?php

namespace Walnut\Lang\Blueprint\Compilation;

use Walnut\Lib\Walex\Token;

interface Parser {
	/** @param Token[] $tokens */
	public function parseAndBuildCodeFromTokens(
		ModuleImporter $moduleImporter,
		CodeBuilder $codeBuilder,
		array $tokens,
		string $moduleName
	): mixed;
}