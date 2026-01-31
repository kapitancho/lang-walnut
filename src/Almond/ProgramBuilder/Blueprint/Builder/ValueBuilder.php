<?php

namespace Walnut\Lang\Almond\ProgramBuilder\Blueprint\Builder;


use Walnut\Lang\Almond\AST\Blueprint\Node\Value\ValueNode;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\BuildException;

interface ValueBuilder {
	/** @throws BuildException */
	public function value(ValueNode $valueNode): Value;
}