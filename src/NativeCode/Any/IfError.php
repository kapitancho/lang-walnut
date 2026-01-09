<?php

namespace Walnut\Lang\NativeCode\Any;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\MethodAnalyser;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\AnyType;
use Walnut\Lang\Blueprint\Type\FunctionType;
use Walnut\Lang\Blueprint\Type\ResultType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\ErrorValue;
use Walnut\Lang\Blueprint\Value\FunctionValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class IfError implements NativeMethod {
	use BaseType;

	public function analyse(
		TypeRegistry $typeRegistry,
		MethodAnalyser $methodAnalyser,
		Type $targetType,
		Type $parameterType,
	): Type {
		$parameterType = $this->toBaseType($parameterType);
		if ($parameterType instanceof FunctionType) {
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
			// Callback should accept the error type
			if ($errorType->isSubtypeOf($parameterType->parameterType)) {
				// Return type is union of success type and callback return type
				$returnType = $parameterType->returnType;
				return $typeRegistry->union([$targetReturnType, $returnType]);
			}
			throw new AnalyserException(sprintf(
				"The parameter type %s of the callback function is not a subtype of %s",
				$errorType,
				$parameterType->parameterType
			));
		}
		throw new AnalyserException(sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType));
	}

	public function execute(
		ProgramRegistry $programRegistry,
		Value $target,
		Value $parameter
	): Value {
		if ($parameter instanceof FunctionValue) {
			return $target instanceof ErrorValue ?
				$parameter->execute(
					$programRegistry->executionContext,
					$target->errorValue
				) : $target;
		}

		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid parameter value");
		// @codeCoverageIgnoreEnd
	}

}