<?php

namespace Walnut\Lang\Almond\Runner\Implementation;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RecordValue;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\TypeName;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionException;
use Walnut\Lang\Almond\Engine\Blueprint\Program\InvalidEntryPointDependency;
use Walnut\Lang\Almond\Runner\Blueprint\Compilation\Error\CompilationErrorType;
use Walnut\Lang\Almond\Runner\Blueprint\Compilation\Error\CompilationFailure as CompilationFailureInterface;
use Walnut\Lang\Almond\Runner\Blueprint\Http\HttpExecutionResult as HttpExecutionResultInterface;
use Walnut\Lang\Almond\Runner\Blueprint\Http\Message\HttpRequest;
use Walnut\Lang\Almond\Runner\Implementation\Http\HttpExecutionResult;
use Walnut\Lang\Almond\Runner\Implementation\Compilation\Error\CompilationFailure;
use Walnut\Lang\Almond\Runner\Implementation\Compilation\Error\CustomCompilationError;
use Walnut\Lang\Almond\Runner\Implementation\Http\Mapper\FromRequestMapper;
use Walnut\Lang\Almond\Runner\Implementation\Http\Mapper\ToResponseMapper;

final readonly class HttpRunner {

	private FromRequestMapper $fromRequestMapper;
	private ToResponseMapper $toResponseMapper;

	public function __construct(
		private Compiler $compiler
	) {
		$this->fromRequestMapper = new FromRequestMapper();
		$this->toResponseMapper = new ToResponseMapper();
	}

	public function run(HttpRequest $httpRequest): HttpExecutionResultInterface|CompilationFailureInterface {
		$compilationResult = $this->compiler->compile();
		if ($compilationResult instanceof CompilationFailureInterface) {
			return $compilationResult;
		} else {
			try {
				/** @var RecordValue $httpResult */
				$httpResult = $compilationResult->program->getEntryPoint(
					new TypeName('HttpRequestHandler')
				)->call(
					$this->fromRequestMapper->mapFromRequest($httpRequest,
						$compilationResult->programContext->valueRegistry)
				);
				return new HttpExecutionResult(
					$this->toResponseMapper->mapToResponse($httpResult),
					$compilationResult->program,
					$compilationResult->programContext,
					$compilationResult->moduleLookupContext,
					$compilationResult->rootNode,
				);
			} catch (ExecutionException $ex) {
				return new CompilationFailure(
					$compilationResult->programContext,
					$compilationResult->moduleLookupContext,
					$compilationResult->rootNode,
					[
						new CustomCompilationError(
							CompilationErrorType::executionError,
							$ex->getMessage(),
							[]
						)
					]
				);
			} catch (InvalidEntryPointDependency $ex) {
				return new CompilationFailure(
					$compilationResult->programContext,
					$compilationResult->moduleLookupContext,
					$compilationResult->rootNode,
					[
						new CustomCompilationError(
							CompilationErrorType::missingValue,
							$ex->getMessage(),
							[]
						)
					]
				);
			}
		}
	}

}