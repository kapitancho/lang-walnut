<?php

namespace Walnut\Lang\Implementation\Program\Builder;

use Walnut\Lang\Blueprint\AST\Node\Expression\ExpressionNode;
use Walnut\Lang\Blueprint\Compilation\AST\AstFunctionBodyCompiler;
use Walnut\Lang\Blueprint\Program\Builder\FunctionBodyBuilder as FunctionBodyBuilderInterface;
use Walnut\Lang\Implementation\Function\FunctionBodyDraft;

final readonly class FunctionBodyBuilder implements FunctionBodyBuilderInterface {

	public function __construct(
		private AstFunctionBodyCompiler $astFunctionBodyCompiler
	) {}

	public function functionBodyDraft(ExpressionNode $expressionNode): FunctionBodyDraft {
		return new FunctionBodyDraft(
			$expressionNode,
			$this->astFunctionBodyCompiler,
		);
	}
}