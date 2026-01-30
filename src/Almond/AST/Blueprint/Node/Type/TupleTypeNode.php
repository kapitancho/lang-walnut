<?php

namespace Walnut\Lang\Almond\AST\Blueprint\Node\Type;

interface TupleTypeNode extends TypeNode {
	/** @var list<TypeNode> */
	public array $types { get; }
	public TypeNode $restType { get; }
}