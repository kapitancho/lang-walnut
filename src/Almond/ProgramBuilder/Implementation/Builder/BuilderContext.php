<?php

namespace Walnut\Lang\Almond\ProgramBuilder\Implementation\Builder;

use Walnut\Lang\Almond\AST\Blueprint\Node\FunctionBodyNode;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Function\FunctionBody;
use Walnut\Lang\Almond\Engine\Blueprint\Program\ProgramContext;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Builder\FunctionBodyBuilder as FunctionBodyCompilerInterface;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Builder\ProgramBuilder as ProgramCompilerInterface;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\CodeMapper;

final readonly class BuilderContext implements FunctionBodyCompilerInterface {

	private FunctionBodyCompilerInterface $functionBodyBuilder;
	public ProgramCompilerInterface $programBuilder;

	public function __construct(
		ProgramContext $programContext,
		CodeMapper $codeMapper
	) {
		$nameBuilder = new NameBuilder($codeMapper);
		$typeBuilder = new TypeBuilder(
			$nameBuilder,
			$programContext->typeRegistry,
			$codeMapper
		);
		$valueBuilder = new ValueBuilder(
			$nameBuilder,
			$programContext->valueRegistry,
			$programContext->functionValueFactory,
			$this,
			$typeBuilder,
			$codeMapper
		);
		$expressionBuilder = new ExpressionBuilder(
			$nameBuilder,
			$typeBuilder,
			$valueBuilder,
			$programContext->valueRegistry,
			$programContext->expressionRegistry,
			$codeMapper
		);
		$this->functionBodyBuilder = new FunctionBodyBuilder(
			$expressionBuilder,
			$programContext->expressionRegistry,
			$codeMapper
		);
		$moduleBuilder = new ModuleBuilder(
			$nameBuilder,
			$programContext,
			$typeBuilder,
			$this->functionBodyBuilder,
			$codeMapper,
			$programContext->userlandMethodBuilder
		);
		$this->programBuilder = new ProgramBuilder($moduleBuilder);
	}

	//Proxies to overcome the circular dependency

	public function functionBody(FunctionBodyNode $functionBodyNode): FunctionBody {
		return $this->functionBodyBuilder->functionBody($functionBodyNode);
	}

}