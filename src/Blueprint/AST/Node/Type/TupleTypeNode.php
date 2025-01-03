<?php

namespace Walnut\Lang\Blueprint\AST\Node\Type;

interface TupleTypeNode extends TypeNode {
	/** @var list<TypeNode> */
	public array $types { get; }
	public TypeNode $restType { get; }
}