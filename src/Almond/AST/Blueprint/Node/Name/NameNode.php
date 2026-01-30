<?php

namespace Walnut\Lang\Almond\AST\Blueprint\Node\Name;

use Walnut\Lang\Almond\AST\Blueprint\Node\SourceNode;

interface NameNode extends SourceNode {
	public string $name { get; }
}