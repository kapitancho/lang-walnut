<?php

namespace Walnut\Lang\Almond\ProgramBuilder\Blueprint\Builder;

use Walnut\Lang\Almond\AST\Blueprint\Node\Type\TypeNode;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\BuildException;

interface TypeBuilder {
	/** @throws BuildException */
	public function type(TypeNode $typeNode): Type;
}