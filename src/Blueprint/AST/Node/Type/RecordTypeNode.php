<?php

namespace Walnut\Lang\Blueprint\AST\Node\Type;

interface RecordTypeNode extends TypeNode {
	/** @var array<string, TypeNode> */
	public array $types { get; }
	public TypeNode $restType { get; }
}