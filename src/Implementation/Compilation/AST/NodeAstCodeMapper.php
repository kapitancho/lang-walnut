<?php

namespace Walnut\Lang\Implementation\Compilation\AST;

use Walnut\Lang\Blueprint\AST\Node\SourceLocation;
use Walnut\Lang\Blueprint\AST\Node\SourceNode;
use Walnut\Lang\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Blueprint\Compilation\AST\AstCodeMapper;
use Walnut\Lang\Blueprint\Compilation\AST\AstSourceLocator;
use Walnut\Lang\Blueprint\Function\CustomMethod;
use Walnut\Lang\Blueprint\Function\FunctionBody;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\Value;
use WeakMap;

final class NodeAstCodeMapper implements AstCodeMapper, AstSourceLocator {

	/** @var WeakMap<Expression|Value|Type|FunctionBody|CustomMethod, SourceNode> */
	private WeakMap $nodeMap;

	public function __construct(
	) {
		$this->nodeMap = new WeakMap();
	}

	public function mapNode(SourceNode $node, Expression|Value|Type|FunctionBody|CustomMethod $element): void {
		$this->nodeMap[$element] = $node;
	}

	public function reset(): void {
		$this->nodeMap = new WeakMap();
	}

	public function getSourceNode(Expression|Value|Type|FunctionBody|CustomMethod $element): SourceNode|null {
		return $this->nodeMap[$element] ?? null;
	}

	public function getSourceLocation(Expression|Value|Type|FunctionBody|CustomMethod $element): SourceLocation|null {
		return $this->getSourceNode($element)?->sourceLocation;
	}
}