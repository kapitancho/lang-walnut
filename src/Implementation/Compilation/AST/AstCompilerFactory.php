<?php

namespace Walnut\Lang\Implementation\Compilation\AST;

use Walnut\Lang\Blueprint\AST\Node\FunctionBodyNode;
use Walnut\Lang\Blueprint\Compilation\AST\AstCompilerFactory as AstCompilerFactoryInterface;
use Walnut\Lang\Blueprint\Compilation\AST\AstFunctionBodyCompiler as AstFunctionBodyCompilerInterface;
use Walnut\Lang\Blueprint\Compilation\CompilationContext;
use Walnut\Lang\Blueprint\Function\FunctionBody;

final readonly class AstCompilerFactory implements AstCompilerFactoryInterface, AstFunctionBodyCompilerInterface {
	public AstProgramCompiler $programCompiler;
	private AstFunctionBodyCompiler $functionBodyCompiler;

	public function __construct(
		CompilationContext $compilationContext
	) {
		$astTypeCompiler = new AstTypeCompiler($compilationContext->typeRegistry);
		$astValueCompiler = new AstValueCompiler(
			$compilationContext->valueRegistry,
			$this,
			$astTypeCompiler
		);
		$astExpressionCompiler = new AstExpressionCompiler(
			$astTypeCompiler,
			$astValueCompiler,
			$compilationContext->expressionRegistry,
		);
		$this->functionBodyCompiler = new AstFunctionBodyCompiler(
			$astExpressionCompiler,
			$compilationContext->expressionRegistry,
		);
		$astModuleCompiler = new AstModuleCompiler(
			$compilationContext,
			$astTypeCompiler,
			$astValueCompiler,
			$this->functionBodyCompiler,
		);
		$this->programCompiler = new AstProgramCompiler($astModuleCompiler);
	}

	public function functionBody(FunctionBodyNode $functionBodyNode): FunctionBody {
		return $this->functionBodyCompiler->functionBody($functionBodyNode);
	}
}