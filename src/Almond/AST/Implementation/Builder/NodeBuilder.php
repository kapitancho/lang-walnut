<?php

namespace Walnut\Lang\Almond\AST\Implementation\Builder;

use Walnut\Lang\Almond\AST\Blueprint\Builder\NodeBuilder as NodeBuilderInterface;
use Walnut\Lang\Almond\AST\Blueprint\Builder\SourceLocator;
use Walnut\Lang\Almond\AST\Blueprint\Node\Expression\ExpressionNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\FunctionBodyNode as FunctionBodyNodeInterface;
use Walnut\Lang\Almond\AST\Blueprint\Node\Module\ModuleNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\SourceLocation;
use Walnut\Lang\Almond\AST\Implementation\Node\FunctionBodyNode;

final class NodeBuilder implements NodeBuilderInterface {

	public NameNodeBuilder $name;
	public ExpressionNodeBuilder $expression;
	public ModuleLevelNodeBuilder $moduleLevel;
	public TypeNodeBuilder $type;
	public ValueNodeBuilder $value;

	public function __construct(
		private readonly SourceLocator $sourceLocator
	) {
		$this->name = new NameNodeBuilder($sourceLocator);
		$this->expression = new ExpressionNodeBuilder($sourceLocator);
		$this->moduleLevel = new ModuleLevelNodeBuilder($sourceLocator);
		$this->type = new TypeNodeBuilder($sourceLocator);
		$this->value = new ValueNodeBuilder($sourceLocator);
	}

	public function build(): ModuleNode {
		return $this->moduleLevel->build();
	}

	private function getSourceLocation(): SourceLocation {
		return $this->sourceLocator->getSourceLocation();
	}

	public function functionBody(ExpressionNode $expression): FunctionBodyNodeInterface {
		return new FunctionBodyNode($this->getSourceLocation(), $expression);
	}

}