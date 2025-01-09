<?php

namespace Walnut\Lang\Implementation\Compilation\AST;

use Walnut\Lang\Blueprint\AST\Node\Expression\ExpressionNode;
use Walnut\Lang\Blueprint\Compilation\AST\AstCompilerFactory as AstCompilerFactoryInterface;
use Walnut\Lang\Blueprint\Compilation\AST\AstFunctionBodyCompiler as AstFunctionBodyCompilerInterface;
use Walnut\Lang\Blueprint\Compilation\CompilationContext;
use Walnut\Lang\Blueprint\Function\FunctionBody;
use Walnut\Lang\Implementation\Program\Builder\FunctionBodyBuilder;
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
		$customMethodDraftRegistryBuilder = $compilationContext->customMethodDraftRegistryBuilder;
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
		$functionBodyBuilder = new FunctionBodyBuilder(
			$this->functionBodyCompiler,
		);
		$programTypeBuilder = new ProgramTypeBuilder(
			$typeRegistry,
			$functionBodyBuilder,
			$typeRegistryBuilder,
			$customMethodDraftRegistryBuilder
		);
		$astModuleCompiler = new AstModuleCompiler(
			$typeRegistry,
			$valueRegistry,
			$programTypeBuilder,
			$expressionRegistry,
			$customMethodDraftRegistryBuilder,
			 $astTypeCompiler,
			 $astValueCompiler,
			 $functionBodyBuilder,
			 $globalScopeBuilder
		);
		$this->programCompiler = new AstProgramCompiler($astModuleCompiler);
	}

	public function functionBody(ExpressionNode $expressionNode): FunctionBody {
		return $this->functionBodyCompiler->functionBody($expressionNode);
	}
}