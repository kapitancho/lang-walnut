<?php

namespace Walnut\Lang\Almond\AST\Blueprint\Node\Name;

interface TypeNameNode extends NameNode {
	public function equals(TypeNameNode $other): bool;
}