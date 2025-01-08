<?php

namespace Walnut\Lang\Blueprint\AST\Compiler;

use Walnut\Lang\Blueprint\AST\Node\Type\TypeNode;
use Walnut\Lang\Blueprint\Type\Type;

interface AstTypeCompiler {
	/** @throws AstCompilationException */
	public function type(TypeNode $typeNode): Type;
}