<?php

namespace Walnut\Lang\Almond\AST\Blueprint\Node\Type;

interface EitherTypeNode extends TypeNode {
	public TypeNode $valueType { get; }
	public TypeNode $errorType { get; }
}