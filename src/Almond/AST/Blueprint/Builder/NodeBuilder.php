<?php

namespace Walnut\Lang\Almond\AST\Blueprint\Builder;

use Walnut\Lang\Almond\AST\Blueprint\Node\Expression\ExpressionNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\FunctionBodyNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Module\ModuleNode;

interface NodeBuilder {
	public NameNodeBuilder $name { get; }
	public ModuleLevelNodeBuilder $moduleLevel { get; }
	public ExpressionNodeBuilder $expression { get; }
	public TypeNodeBuilder $type { get; }
	public ValueNodeBuilder $value { get; }

	public function build(): ModuleNode;

	public function functionBody(ExpressionNode $expression): FunctionBodyNode;
}