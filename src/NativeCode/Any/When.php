<?php

namespace Walnut\Lang\NativeCode\Any;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\MethodFinder;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\AnyType;
use Walnut\Lang\Blueprint\Type\ResultType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\ErrorValue;
use Walnut\Lang\Blueprint\Value\FunctionValue;
use Walnut\Lang\Blueprint\Value\RecordValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class When implements NativeMethod {
	use BaseType;

	public function analyse(
		TypeRegistry $typeRegistry,
		MethodFinder $methodFinder,
		Type $targetType,
		Type $parameterType,
	): Type {
		// Determine the success and error types from the Result
		$targetType = $this->toBaseType($targetType);
		$targetReturnType = match(true) {
			$targetType instanceof ResultType => $targetType->returnType,
			default => $typeRegistry->any,
		};
		$errorType = match(true) {
			$targetType instanceof ResultType => $targetType->errorType,
			$targetType instanceof AnyType => $typeRegistry->any,
			default => $typeRegistry->nothing,
		};

		// Build the expected parameter type: [success: ^T => R1, error: ^E => R2]
		$refType = $typeRegistry->record([
			'success' => $typeRegistry->function(
				$targetReturnType,
				$typeRegistry->any
			),
			'error' => $typeRegistry->function(
				$errorType,
				$typeRegistry->any
			)
		]);

		// Validate the parameter type and extract return types
		$pType = $this->toBaseType($parameterType);
		if ($pType->isSubtypeOf($refType)) {
			$successType = $pType->types['success']->returnType;
			$errorType = $pType->types['error']->returnType;

			// Return type is the union of both callback return types
			return $typeRegistry->union([$successType, $errorType]);
		}
		throw new AnalyserException(sprintf("[%s] Invalid parameter type: %s, a subtype of %s expected",
			__CLASS__, $parameterType, $refType));
	}

	public function execute(
		ProgramRegistry $programRegistry,
		Value $target,
		Value $parameter
	): Value {
		if ($parameter instanceof RecordValue) {
			// Extract the success callback
			$successFn = $parameter->values['success'];
			if (!$successFn instanceof FunctionValue) {
				// @codeCoverageIgnoreStart
				throw new ExecutionException("Invalid parameter value for 'success' key");
				// @codeCoverageIgnoreEnd
			}

			// Extract the error callback
			$errorFn = $parameter->values['error'];
			if (!$errorFn instanceof FunctionValue) {
				// @codeCoverageIgnoreStart
				throw new ExecutionException("Invalid parameter value for 'error' key");
				// @codeCoverageIgnoreEnd
			}

			// Apply the appropriate callback based on whether target is an error
			return $target instanceof ErrorValue ?
				$errorFn->execute(
					$programRegistry->executionContext,
					$target->errorValue
				) : $successFn->execute(
					$programRegistry->executionContext,
					$target
				);
		}

		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid parameter value");
		// @codeCoverageIgnoreEnd
	}

}