<?php

namespace Walnut\Lang\Almond\ProgramBuilder\Blueprint\Builder;


use Walnut\Lang\Almond\AST\Blueprint\Node\Value\ValueNode;
use Walnut\Lang\Almond\Engine\Blueprint\Value\Value;

interface ValueBuilder {
	/** @throws CompilationException */
	public function value(ValueNode $valueNode): Value;
}