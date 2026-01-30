<?php

namespace Walnut\Lang\Almond\AST\Blueprint\Node\Type;

interface RecordTypeNode extends TypeNode {
	/** @var array<string, TypeNode> */
	public array $types { get; }
	public TypeNode $restType { get; }
}