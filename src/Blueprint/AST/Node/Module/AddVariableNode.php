<?php

namespace Walnut\Lang\Blueprint\AST\Node\Module;

use Walnut\Lang\Blueprint\AST\Node\Value\ValueNode;
use Walnut\Lang\Blueprint\Common\Identifier\VariableNameIdentifier;

interface AddVariableNode extends ModuleDefinitionNode {
	public VariableNameIdentifier $name { get; }
	public ValueNode $value { get; }
}