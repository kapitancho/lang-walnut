<?php

namespace Walnut\Lang\Almond\ProgramBuilder\Blueprint\Builder;

use Walnut\Lang\Almond\AST\Blueprint\Node\Type\TypeNode;
use Walnut\Lang\Almond\Engine\Blueprint\Type\Type;

interface TypeBuilder {
	/** @throws CompilationException */
	public function type(TypeNode $typeNode): Type;
}