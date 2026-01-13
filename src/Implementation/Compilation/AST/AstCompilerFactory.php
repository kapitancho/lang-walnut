<?php

namespace Walnut\Lang\Implementation\Compilation\AST;

use Walnut\Lang\Blueprint\AST\Node\FunctionBodyNode;
use Walnut\Lang\Blueprint\Compilation\AST\AstCodeMapper;
use Walnut\Lang\Blueprint\Compilation\AST\AstCompilerFactory as AstCompilerFactoryInterface;
use Walnut\Lang\Blueprint\Compilation\AST\AstFunctionBodyCompiler as AstFunctionBodyCompilerInterface;
use Walnut\Lang\Blueprint\Function\FunctionBody;
use Walnut\Lang\Blueprint\Program\ProgramContext;

final readonly class AstCompilerFactory implements AstCompilerFactoryInterface, AstFunctionBodyCompilerInterface {

	public AstCodeMapper $astCodeMapper;
	public AstProgramCompiler $programCompiler;
	private AstFunctionBodyCompiler $functionBodyCompiler;

	public function __construct(
		ProgramContext $programContext,
		AstCodeMapper $astCodeMapper,
	) {
		$astTypeCompiler = new AstTypeCompiler(
			$programContext->typeRegistry,
			$astCodeMapper
		);
		$astValueCompiler = new AstValueCompiler(
			$programContext->typeRegistry,
			$programContext->valueRegistry,
			$this,
			$astTypeCompiler,
			$astCodeMapper
		);
		$astExpressionCompiler = new AstExpressionCompiler(
			$astTypeCompiler,
			$astValueCompiler,
			$programContext->expressionRegistry,
			$astCodeMapper
		);
		$this->functionBodyCompiler = new AstFunctionBodyCompiler(
			$astExpressionCompiler,
			$programContext->expressionRegistry,
			$astCodeMapper
		);
		$astModuleCompiler = new AstModuleCompiler(
			$programContext,
			$astTypeCompiler,
			$this->functionBodyCompiler,
			$astCodeMapper
		);
		$this->programCompiler = new AstProgramCompiler($astModuleCompiler);
	}

	public function functionBody(FunctionBodyNode $functionBodyNode): FunctionBody {
		return $this->functionBodyCompiler->functionBody($functionBodyNode);
	}

	// Not really in use, but required by the interface
	// @codeCoverageIgnoreStart
	public function validatorBody(FunctionBodyNode $functionBodyNode): FunctionBody {
		return $this->functionBodyCompiler->validatorBody($functionBodyNode);
	}
	// @codeCoverageIgnoreEnd
}