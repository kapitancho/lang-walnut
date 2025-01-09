<?php

namespace Walnut\Lang\Blueprint\Compilation\AST;

use Walnut\Lang\Blueprint\AST\Node\Value\ValueNode;
use Walnut\Lang\Blueprint\Value\Value;

interface AstValueCompiler {
	/** @throws AstCompilationException */
	public function value(ValueNode $valueNode): Value;
}