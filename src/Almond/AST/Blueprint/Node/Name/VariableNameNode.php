<?php

namespace Walnut\Lang\Almond\AST\Blueprint\Node\Name;

interface VariableNameNode extends NameNode {
	public function equals(VariableNameNode $other): bool;
}