<?php

namespace Walnut\Lang\Blueprint\AST\Node\Type;

use Walnut\Lang\Blueprint\Identifier\EnumValueIdentifier;
use Walnut\Lang\Blueprint\Identifier\TypeNameIdentifier;

interface EnumerationSubsetTypeNode extends TypeNode {
	public TypeNameIdentifier $name { get; }
	/** @var list<EnumValueIdentifier> $values */
	public array $values { get; }
}