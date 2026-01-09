<?php

namespace Walnut\Lang\NativeCode\Array;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\MethodAnalyser;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\ArrayType;
use Walnut\Lang\Blueprint\Type\FunctionType;
use Walnut\Lang\Blueprint\Type\ResultType;
use Walnut\Lang\Blueprint\Type\TupleType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\ErrorValue;
use Walnut\Lang\Blueprint\Value\FunctionValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Blueprint\Value\TupleValue;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class FlatMap implements NativeMethod {
	use BaseType;

	public function analyse(
		TypeRegistry $typeRegistry,
		MethodAnalyser $methodAnalyser,
		Type $targetType,
		Type $parameterType,
	): Type {
		$targetType = $this->toBaseType($targetType);
		$type = $targetType instanceof TupleType ? $targetType->asArrayType() : $targetType;
		if ($type instanceof ArrayType) {
			$parameterType = $this->toBaseType($parameterType);
			if ($parameterType instanceof FunctionType) {
				if ($type->itemType->isSubtypeOf($parameterType->parameterType)) {
					$r = $parameterType->returnType;
					$errorType = $r instanceof ResultType ? $r->errorType : null;
					$returnType = $r instanceof ResultType ? $r->returnType : $r;

					// Return type must be an array
					if ($returnType->isSubtypeOf($typeRegistry->array())) {
						$returnType = $this->toBaseType($returnType);
						if ($returnType instanceof TupleType) {
							$returnType = $returnType->asArrayType();
						}
						$minLength = ((int)(string)$type->range->minLength) * ((int)(string)$returnType->range->minLength);
						$maxLength = $type->range->maxLength === PlusInfinity::value ||
							$returnType->range->maxLength === PlusInfinity::value ?
							PlusInfinity::value :
							((int)(string)$type->range->maxLength) * ((int)(string)$returnType->range->maxLength);

						$resultArray = $typeRegistry->array(
							$returnType->itemType,
							$minLength,
							$maxLength
						);
						return $errorType ? $typeRegistry->result($resultArray, $errorType) : $resultArray;
					}
					throw new AnalyserException(
						sprintf(
							"The return type of the callback function must be a subtype of Array, got %s",
							$returnType
						)
					);
				}
				throw new AnalyserException(
					sprintf(
						"The parameter type %s of the callback function is not a subtype of %s",
						$type->itemType,
						$parameterType->parameterType
					)
				);
			}
			throw new AnalyserException(sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType));
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
		if ($target instanceof TupleValue && $parameter instanceof FunctionValue) {
			$values = $target->values;
			$result = [];

			foreach($values as $value) {
				$r = $parameter->execute($programRegistry->executionContext, $value);
				if ($r instanceof ErrorValue) {
					return $r;
				}
				if ($r instanceof TupleValue) {
					$result = array_merge($result, $r->values);
				} else {
					// @codeCoverageIgnoreStart
					throw new ExecutionException("FlatMap callback must return an Array");
					// @codeCoverageIgnoreEnd
				}
			}

			return $programRegistry->valueRegistry->tuple($result);
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}

}
