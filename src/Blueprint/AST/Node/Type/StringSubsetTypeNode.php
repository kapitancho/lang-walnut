<?php

namespace Walnut\Lang\Blueprint\AST\Node\Type;

interface StringSubsetTypeNode extends TypeNode {
	/** @var list<string> */
	public array $values { get; }
}