<?php

namespace Walnut\Lang\Implementation\Function;

use JsonSerializable;
use Walnut\Lang\Blueprint\AST\Node\Expression\ExpressionNode;
use Walnut\Lang\Blueprint\Function\FunctionBodyDraft as FunctionBodyDraftInterface;

final readonly class FunctionBodyDraft implements FunctionBodyDraftInterface, JsonSerializable {

	public function __construct(
		public ExpressionNode $expressionNode
	) {}

	public function __toString(): string {
		return (string)$this->expressionNode->jsonSerialize();
	}

	public function jsonSerialize(): array {
		return [
			'expression' => $this->expressionNode
		];
	}
}