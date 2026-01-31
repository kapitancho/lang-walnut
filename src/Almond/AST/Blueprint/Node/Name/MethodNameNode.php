<?php

namespace Walnut\Lang\Almond\AST\Blueprint\Node\Name;

interface MethodNameNode extends NameNode {
	public function equals(MethodNameNode $other): bool;
}