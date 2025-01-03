<?php

namespace Walnut\Lang\Blueprint\Compilation;

use Walnut\Lang\Blueprint\AST\Node\RootNode;

interface AstCompiler {
	public function compile(RootNode $root): void;
}