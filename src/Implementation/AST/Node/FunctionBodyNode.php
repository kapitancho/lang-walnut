<?php

namespace Walnut\Lang\Implementation\AST\Node;

use Walnut\Lang\Blueprint\AST\Node\Expression\ExpressionNode;
use Walnut\Lang\Blueprint\AST\Node\FunctionBodyNode as FunctionBodyNodeInterface;
use Walnut\Lang\Blueprint\AST\Node\SourceLocation;

final readonly class FunctionBodyNode implements FunctionBodyNodeInterface {
	public function __construct(
		public SourceLocation $sourceLocation,
		public ExpressionNode $expression
	) {}

	public function jsonSerialize(): array {
		return [
			'sourceLocation' => $this->sourceLocation,
			'nodeCategory' => 'FunctionBody',
			'nodeName' => 'FunctionBody',
			'expression' => $this->expression
		];
	}
}