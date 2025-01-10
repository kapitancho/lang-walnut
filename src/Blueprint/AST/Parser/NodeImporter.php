<?php

namespace Walnut\Lang\Blueprint\AST\Parser;

use Walnut\Lang\Blueprint\AST\Node\RootNode;
use Walnut\Lang\Blueprint\Compilation\Module\ModuleLookupContext;

interface NodeImporter {
	public function importFromSource(
		string $startModuleName,
		ModuleLookupContext $moduleLookupContext
	): RootNode;
}