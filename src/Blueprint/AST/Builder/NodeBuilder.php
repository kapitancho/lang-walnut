<?php

namespace Walnut\Lang\Blueprint\AST\Builder;

use Walnut\Lang\Blueprint\AST\Node\Expression\ExpressionNode;
use Walnut\Lang\Blueprint\AST\Node\FunctionBodyNode;
use Walnut\Lang\Blueprint\AST\Node\Module\ModuleNode;

interface NodeBuilder {
	public ModuleLevelNodeBuilder $moduleLevel { get; }
	public ExpressionNodeBuilder $expression { get; }
	public TypeNodeBuilder $type { get; }
	public ValueNodeBuilder $value { get; }

	public function build(): ModuleNode;

	public function functionBody(ExpressionNode $expression): FunctionBodyNode;
}