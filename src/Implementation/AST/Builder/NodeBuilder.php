<?php

namespace Walnut\Lang\Implementation\AST\Builder;

use Walnut\Lang\Blueprint\AST\Builder\ExpressionNodeBuilder as ExpressionNodeBuilderInterface;
use Walnut\Lang\Blueprint\AST\Builder\ModuleLevelNodeBuilder as ModuleLevelNodeBuilderInterface;
use Walnut\Lang\Blueprint\AST\Builder\NodeBuilder as NodeBuilderInterface;
use Walnut\Lang\Blueprint\AST\Builder\SourceLocator;
use Walnut\Lang\Blueprint\AST\Builder\TypeNodeBuilder as TypeNodeBuilderInterface;
use Walnut\Lang\Blueprint\AST\Builder\ValueNodeBuilder as ValueNodeBuilderInterface;
use Walnut\Lang\Blueprint\AST\Node\Expression\ExpressionNode;
use Walnut\Lang\Blueprint\AST\Node\SourceLocation as SourceLocationInterface;
use Walnut\Lang\Implementation\AST\Node\FunctionBodyNode;
use Walnut\Lang\Implementation\AST\Node\Module\ModuleNode;

final class NodeBuilder implements NodeBuilderInterface {

	public ExpressionNodeBuilderInterface $expression;
	public ModuleLevelNodeBuilderInterface $moduleLevel;
	public TypeNodeBuilderInterface $type;
	public ValueNodeBuilderInterface $value;

	public function __construct(
		private readonly SourceLocator $sourceLocator
	) {
		$this->expression = new ExpressionNodeBuilder($sourceLocator);
		$this->moduleLevel = new ModuleLevelNodeBuilder($sourceLocator);
		$this->type = new TypeNodeBuilder($sourceLocator);
		$this->value = new ValueNodeBuilder($sourceLocator);
	}

	public function build(): ModuleNode {
		return $this->moduleLevel->build();
	}

	private function getSourceLocation(): SourceLocationInterface {
		return $this->sourceLocator->getSourceLocation();
	}

	public function functionBody(ExpressionNode $expression): FunctionBodyNode {
		return new FunctionBodyNode($this->getSourceLocation(), $expression);
	}

}