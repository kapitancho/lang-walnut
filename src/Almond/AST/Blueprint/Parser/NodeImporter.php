<?php

namespace Walnut\Lang\Almond\AST\Blueprint\Parser;

use Walnut\Lang\Almond\AST\Blueprint\Node\RootNode;

interface NodeImporter {
	public function importFromSource(
		string $startModuleName,
		ModuleContentProvider $moduleContentProvider
	): RootNode;
}