<?php

namespace Walnut\Lang\Implementation\Compilation\AST;

use Walnut\Lang\Blueprint\AST\Node\FunctionBodyNode;
use Walnut\Lang\Blueprint\Compilation\AST\AstCompilerFactory as AstCompilerFactoryInterface;
use Walnut\Lang\Blueprint\Compilation\AST\AstFunctionBodyCompiler as AstFunctionBodyCompilerInterface;
use Walnut\Lang\Blueprint\Compilation\CompilationContext;
use Walnut\Lang\Blueprint\Function\FunctionBody;
use Walnut\Lang\Implementation\Program\Builder\ProgramTypeBuilder;

//use Walnut\Lang\Blueprint\Program\Builder\FunctionBodyBuilder as FunctionBodyBuilderInterface;
//use Walnut\Lang\Blueprint\Program\Builder\ProgramTypeBuilder as ProgramTypeBuilderInterface;

final readonly class AstCompilerFactory implements AstCompilerFactoryInterface, AstFunctionBodyCompilerInterface {
	public AstProgramCompiler $programCompiler;
	private AstFunctionBodyCompiler $functionBodyCompiler;
	/*public FunctionBodyBuilderInterface $functionBodyBuilder;
	public ProgramTypeBuilderInterface $programTypeBuilder;*/

	public function __construct(
		CompilationContext $compilationContext
	) {
		$typeRegistry        = $compilationContext->typeRegistry;
		$typeRegistryBuilder = $compilationContext->typeRegistryBuilder;
		$valueRegistry       = $compilationContext->valueRegistry;
		$expressionRegistry  = $compilationContext->expressionRegistry;
		$customMethodRegistryBuilder = $compilationContext->customMethodRegistryBuilder;
		$globalScopeBuilder = $compilationContext->globalScopeBuilder;
		$codeBuilder = $compilationContext->codeBuilder;

		$astTypeCompiler = new AstTypeCompiler($typeRegistry);
		$astValueCompiler = new AstValueCompiler(
			$valueRegistry,
			$this,
			$astTypeCompiler
		);
		$astExpressionCompiler = new AstExpressionCompiler(
			$astTypeCompiler,
			$astValueCompiler,
			$codeBuilder,
			$expressionRegistry
		);
		$this->functionBodyCompiler = new AstFunctionBodyCompiler(
			$astExpressionCompiler,
			$expressionRegistry,
		);
		$programTypeBuilder = new ProgramTypeBuilder(
			$typeRegistry,
			$typeRegistryBuilder,
			$valueRegistry,
			$expressionRegistry,
			$customMethodRegistryBuilder
		);
		$astModuleCompiler = new AstModuleCompiler(
			$programTypeBuilder,
			 $astTypeCompiler,
			 $astValueCompiler,
			 $astExpressionCompiler,
			 $this->functionBodyCompiler,
			 $globalScopeBuilder
		);
		$this->programCompiler = new AstProgramCompiler($astModuleCompiler);
	}

	public function functionBody(FunctionBodyNode $functionBodyNode): FunctionBody {
		return $this->functionBodyCompiler->functionBody($functionBodyNode);
	}
}