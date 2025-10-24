<?php

namespace Walnut\Lang\Implementation\Compilation\AST;

use Walnut\Lang\Blueprint\AST\Node\FunctionBodyNode;
use Walnut\Lang\Blueprint\Compilation\AST\AstCodeMapper;
use Walnut\Lang\Blueprint\Compilation\AST\AstCompilationException;
use Walnut\Lang\Blueprint\Compilation\AST\AstExpressionCompiler;
use Walnut\Lang\Blueprint\Compilation\AST\AstFunctionBodyCompiler as AstFunctionBodyCompilerInterface;
use Walnut\Lang\Blueprint\Function\FunctionBody;
use Walnut\Lang\Blueprint\Program\Registry\ExpressionRegistry;

final readonly class AstFunctionBodyCompiler implements AstFunctionBodyCompilerInterface {

	public function __construct(
		private AstExpressionCompiler $astExpressionCompiler,
		private ExpressionRegistry $expressionRegistry,
		private AstCodeMapper $astCodeMapper,
	) {}

	/** @throws AstCompilationException */
	public function functionBody(FunctionBodyNode $functionBodyNode): FunctionBody {
		$result = $this->expressionRegistry->functionBody(
			$this->astExpressionCompiler->expression($functionBodyNode->expression)
		);
		$this->astCodeMapper->mapNode($functionBodyNode, $result);
		return $result;
	}
}