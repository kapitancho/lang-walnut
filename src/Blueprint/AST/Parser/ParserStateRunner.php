<?php

namespace Walnut\Lang\Blueprint\AST\Parser;

use Walnut\Lang\Blueprint\AST\Node\Module\ModuleNode;
use Walnut\Lang\Implementation\AST\Builder\SourceLocator;

interface ParserStateRunner {
	public function run(SourceLocator $sourceLocator): ModuleNode;
}