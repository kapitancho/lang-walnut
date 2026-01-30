<?php

namespace Walnut\Lang\Almond\ProgramBuilder\Implementation\Builder;

use Walnut\Lang\Almond\AST\Blueprint\Node\FunctionBodyNode;
use Walnut\Lang\Almond\Engine\Blueprint\Function\FunctionBody;
use Walnut\Lang\Almond\Engine\Blueprint\Program\ProgramContext;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\CodeMapper;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Builder\FunctionBodyBuilder as FunctionBodyCompilerInterface;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Builder\ProgramBuilder as ProgramCompilerInterface;

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
			$programContext->typeRegistry,
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
			$nameBuilder,
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

	// Not really in use, but required by the interface
	// @codeCoverageIgnoreStart
	public function validatorBody(FunctionBodyNode $functionBodyNode): FunctionBody {
		return $this->functionBodyBuilder->validatorBody($functionBodyNode);
	}
	// @codeCoverageIgnoreEnd

}