<?php

namespace Walnut\Lang\Implementation\Function;

use JsonSerializable;
use Walnut\Lang\Blueprint\AST\Node\Expression\ExpressionNode;
use Walnut\Lang\Blueprint\Compilation\AST\AstFunctionBodyCompiler;
use Walnut\Lang\Blueprint\Function\FunctionBody;
use Walnut\Lang\Blueprint\Function\FunctionBodyDraft as FunctionBodyDraftInterface;

final class FunctionBodyDraft implements FunctionBodyDraftInterface, JsonSerializable {

	public function __construct(
		public readonly ExpressionNode $expressionNode,
		private readonly AstFunctionBodyCompiler $astFunctionBodyCompiler
	) {}

	public FunctionBody $functionBody {
		get => $this->astFunctionBodyCompiler->functionBody($this->expressionNode);
	}

	public function __toString(): string {
		return (string)$this->expressionNode->jsonSerialize();
	}

	public function jsonSerialize(): array {
		return [
			'expression' => $this->expressionNode
		];
	}
}