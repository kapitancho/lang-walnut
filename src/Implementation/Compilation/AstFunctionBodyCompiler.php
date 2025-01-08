<?php

namespace Walnut\Lang\Implementation\Compilation;

use Walnut\Lang\Blueprint\AST\Compiler\AstCompilationException;
use Walnut\Lang\Blueprint\AST\Compiler\AstExpressionCompiler;
use Walnut\Lang\Blueprint\AST\Compiler\AstFunctionBodyCompiler as AstFunctionBodyCompilerInterface;
use Walnut\Lang\Blueprint\AST\Node\FunctionBodyNode;
use Walnut\Lang\Blueprint\Function\FunctionBody;
use Walnut\Lang\Blueprint\Function\FunctionBodyDraft;
use Walnut\Lang\Blueprint\Program\Registry\ExpressionRegistry;

final readonly class AstFunctionBodyCompiler implements AstFunctionBodyCompilerInterface {

	public function __construct(
		private AstExpressionCompiler $astExpressionCompiler,
		private ExpressionRegistry $expressionRegistry,
	) {}


	/** @throws AstCompilationException */
	public function functionBody(FunctionBodyNode|FunctionBodyDraft $functionBody): FunctionBody {
		return $this->expressionRegistry->functionBody(
			$this->astExpressionCompiler->expression(
				$functionBody instanceof FunctionBodyDraft ?
					$functionBody->expressionNode :
					$functionBody->expression
			)
		);
	}

}