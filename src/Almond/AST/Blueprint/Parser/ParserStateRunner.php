<?php

namespace Walnut\Lang\Almond\AST\Blueprint\Parser;

use Walnut\Lang\Almond\AST\Blueprint\Builder\SourceLocator;
use Walnut\Lang\Almond\AST\Blueprint\Node\Module\ModuleNode;

interface ParserStateRunner {
	public function run(SourceLocator $sourceLocator): ModuleNode;
}