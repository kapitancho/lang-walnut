<?php

namespace Walnut\Lang\Blueprint\AST\Parser;

use Walnut\Lang\Blueprint\AST\Builder\SourceLocator;
use Walnut\Lang\Blueprint\AST\Node\Module\ModuleNode;

interface ParserStateRunner {
	public function run(SourceLocator $sourceLocator): ModuleNode;
}