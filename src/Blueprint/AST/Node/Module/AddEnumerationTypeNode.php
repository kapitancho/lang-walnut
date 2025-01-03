<?php

namespace Walnut\Lang\Blueprint\AST\Node\Module;

use Walnut\Lang\Blueprint\Common\Identifier\EnumValueIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;

interface AddEnumerationTypeNode extends ModuleDefinitionNode {
	public TypeNameIdentifier $name { get; }
	/** @var list<EnumValueIdentifier> $values */
	public array $values { get; }
}