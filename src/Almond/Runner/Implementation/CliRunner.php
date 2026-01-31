<?php

namespace Walnut\Lang\Almond\Runner\Implementation;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\StringValue;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\TypeName;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionException;
use Walnut\Lang\Almond\Engine\Blueprint\Program\InvalidEntryPointDependency;
use Walnut\Lang\Almond\Runner\Blueprint\Cli\CliExecutionResult as CliExecutionResultInterface;
use Walnut\Lang\Almond\Runner\Blueprint\Compilation\Error\CompilationErrorType;
use Walnut\Lang\Almond\Runner\Blueprint\Compilation\Error\CompilationFailure as CompilationFailureInterface;
use Walnut\Lang\Almond\Runner\Implementation\Cli\CliExecutionResult;
use Walnut\Lang\Almond\Runner\Implementation\Compilation\Error\CompilationFailure;
use Walnut\Lang\Almond\Runner\Implementation\Compilation\Error\CustomCompilationError;

final readonly class CliRunner {

	public function __construct(
		private Compiler $compiler
	) {}

	/** @param list<string> $args */
	public function run(array $args): CliExecutionResultInterface|CompilationFailureInterface {
		$compilationResult = $this->compiler->compile();
		if ($compilationResult instanceof CompilationFailureInterface) {
			return $compilationResult;
		} else {
			try {
				$cliResult = $compilationResult->program->getEntryPoint(
					new TypeName('CliEntryPoint')
				)->call(
					$compilationResult->programContext->valueRegistry->tuple(
						array_map(
							fn(string $arg): StringValue => $compilationResult->programContext->valueRegistry->string($arg),
							$args
						)
					)
				)->literalValue;
				return new CliExecutionResult(
					$cliResult,
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