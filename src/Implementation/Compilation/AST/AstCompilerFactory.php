<?php

namespace Walnut\Lang\Implementation\Compilation\AST;

use Walnut\Lang\Blueprint\AST\Node\FunctionBodyNode;
use Walnut\Lang\Blueprint\Compilation\AST\AstCompilerFactory as AstCompilerFactoryInterface;
use Walnut\Lang\Blueprint\Compilation\AST\AstFunctionBodyCompiler as AstFunctionBodyCompilerInterface;
use Walnut\Lang\Blueprint\Function\FunctionBody;
use Walnut\Lang\Blueprint\Program\ProgramContext;

final readonly class AstCompilerFactory implements AstCompilerFactoryInterface, AstFunctionBodyCompilerInterface {
	public AstProgramCompiler $programCompiler;
	private AstFunctionBodyCompiler $functionBodyCompiler;

	public function __construct(
		ProgramContext $programContext
	) {
		$astTypeCompiler = new AstTypeCompiler($programContext->typeRegistry);
		$astValueCompiler = new AstValueCompiler(
			$programContext->valueRegistry,
			$this,
			$astTypeCompiler
		);
		$astExpressionCompiler = new AstExpressionCompiler(
			$astTypeCompiler,
			$astValueCompiler,
			$programContext->expressionRegistry,
		);
		$this->functionBodyCompiler = new AstFunctionBodyCompiler(
			$astExpressionCompiler,
			$programContext->expressionRegistry,
		);
		$astModuleCompiler = new AstModuleCompiler(
			$programContext,
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