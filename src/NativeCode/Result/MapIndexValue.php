<?php

namespace Walnut\Lang\NativeCode\Result;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\MethodFinder;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\ArrayType;
use Walnut\Lang\Blueprint\Type\FunctionType;
use Walnut\Lang\Blueprint\Type\ResultType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\ErrorValue;
use Walnut\Lang\Blueprint\Value\FunctionValue;
use Walnut\Lang\Blueprint\Value\TupleValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class MapIndexValue implements NativeMethod {
	use BaseType;

	public function analyse(
		TypeRegistry $typeRegistry,
		MethodFinder $methodFinder,
		Type $targetType,
		Type $parameterType,
	): Type {
		$targetType = $this->toBaseType($targetType);
		if ($targetType instanceof ResultType) {
			$arrayType = $this->toBaseType($targetType->returnType);
			if ($arrayType instanceof ArrayType) {
				$parameterType = $this->toBaseType($parameterType);
				if ($parameterType instanceof FunctionType) {
					$callbackParameterType = $parameterType->parameterType;
					$expectedType = $typeRegistry->record([
						'index' => $typeRegistry->integer(0,
							$arrayType->range->maxLength === PlusInfinity::value ? PlusInfinity::value :
								max($arrayType->range->maxLength - 1, 0)
						),
						'value' => $arrayType->itemType
					]);
					if ($expectedType->isSubtypeOf($callbackParameterType)) {
						$r = $parameterType->returnType;
						$callbackErrorType = $r instanceof ResultType ? $r->errorType : null;
						$returnType = $r instanceof ResultType ? $r->returnType : $r;
						$t = $typeRegistry->array(
							$returnType,
							$arrayType->range->minLength,
							$arrayType->range->maxLength,
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
			throw new AnalyserException(sprintf("[%s] Invalid target type: Result must contain an Array, got %s", __CLASS__, $targetType->returnType));
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
		$targetValue = $target;
		$parameterValue = $parameter;

		// If the target is an error, return it as-is
		if ($targetValue instanceof ErrorValue) {
			return $targetValue;
		}

		// Only errors should reach this point, therefore no logic for Map<> values is needed.

		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}

}
