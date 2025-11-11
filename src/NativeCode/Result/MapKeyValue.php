<?php

namespace Walnut\Lang\NativeCode\Result;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\MethodFinder;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\FunctionType;
use Walnut\Lang\Blueprint\Type\MapType;
use Walnut\Lang\Blueprint\Type\ResultType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\ErrorValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class MapKeyValue implements NativeMethod {
	use BaseType;

	public function analyse(
		TypeRegistry $typeRegistry,
		MethodFinder $methodFinder,
		Type $targetType,
		Type $parameterType,
	): Type {
		$targetType = $this->toBaseType($targetType);
		if ($targetType instanceof ResultType) {
			$mapType = $this->toBaseType($targetType->returnType);
			if ($mapType instanceof MapType) {
				$parameterType = $this->toBaseType($parameterType);
				if ($parameterType instanceof FunctionType) {
					$callbackParameterType = $parameterType->parameterType;
					$expectedType = $typeRegistry->record([
						'key' => $typeRegistry->string(),
						'value' => $mapType->itemType
					]);
					if ($expectedType->isSubtypeOf($callbackParameterType)) {
						$r = $parameterType->returnType;
						$callbackErrorType = $r instanceof ResultType ? $r->errorType : null;
						$returnType = $r instanceof ResultType ? $r->returnType : $r;
						$t = $typeRegistry->map(
							$returnType,
							$mapType->range->minLength,
							$mapType->range->maxLength,
						);
						// Combine error types: original Result error type and callback error type
						if ($callbackErrorType) {
							$combinedErrorType = $typeRegistry->union([$targetType->errorType, $callbackErrorType]);
						} else {
							$combinedErrorType = $targetType->errorType;
						}
						return $typeRegistry->result($t, $combinedErrorType);
					}
					throw new AnalyserException(sprintf(
						"The parameter type %s of the callback function is not a subtype of %s",
						$expectedType,
						$callbackParameterType
					));
				}
				throw new AnalyserException(sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType));
			}
			// @codeCoverageIgnoreStart
			throw new AnalyserException(sprintf("[%s] Invalid target type: Result must contain a Map, got %s", __CLASS__, $targetType->returnType));
			// @codeCoverageIgnoreEnd
		}
		// @codeCoverageIgnoreStart
		throw new AnalyserException(sprintf("[%s] Invalid target type: %s", __CLASS__, $targetType));
		// @codeCoverageIgnoreEnd
	}

	public function execute(
		ProgramRegistry $programRegistry,
		Value $target,
		Value $parameter
	): Value {
		// If the target is an error, return it as-is
		if ($target instanceof ErrorValue) {
			return $target;
		}

		// Only errors should reach this point, therefore no logic for Map<> values is needed.

		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}

}