<?php

namespace Walnut\Lang\Almond\AST\Blueprint\Node\Type;

interface ErrorTypeNode extends TypeNode {
	public TypeNode $errorType { get; }
}