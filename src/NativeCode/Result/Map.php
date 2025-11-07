<?php

namespace Walnut\Lang\NativeCode\Result;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\MethodFinder;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\ArrayType;
use Walnut\Lang\Blueprint\Type\FunctionType;
use Walnut\Lang\Blueprint\Type\MapType;
use Walnut\Lang\Blueprint\Type\RecordType;
use Walnut\Lang\Blueprint\Type\ResultType;
use Walnut\Lang\Blueprint\Type\SetType;
use Walnut\Lang\Blueprint\Type\TupleType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Type\UnionType;
use Walnut\Lang\Blueprint\Value\ErrorValue;
use Walnut\Lang\Blueprint\Value\FunctionValue;
use Walnut\Lang\Blueprint\Value\RecordValue;
use Walnut\Lang\Blueprint\Value\SetValue;
use Walnut\Lang\Blueprint\Value\TupleValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class Map implements NativeMethod {
	use BaseType;

	public function analyse(
		TypeRegistry $typeRegistry,
		MethodFinder $methodFinder,
		Type $targetType,
		Type $parameterType,
	): Type {
		$targetType = $this->toBaseType($targetType);
		if ($targetType instanceof ResultType) {
			$innerType = $this->toBaseType($targetType->returnType);
			// Handle both MapType and ArrayType
			if ($innerType instanceof RecordType) {
				$innerType = $innerType->asMapType();
			} elseif ($innerType instanceof TupleType) {
				$innerType = $innerType->asArrayType();
			}

			$parameterType = $this->toBaseType($parameterType);
			if ($parameterType instanceof FunctionType) {
				$r = $parameterType->returnType;
				$callbackErrorType = $r instanceof ResultType ? $r->errorType : null;
				$returnType = $r instanceof ResultType ? $r->returnType : $r;

				// Combine error types: original Result error type and callback error type
				if ($callbackErrorType) {
					$combinedErrorType = $typeRegistry->union([$targetType->errorType, $callbackErrorType]);
				} else {
					$combinedErrorType = $targetType->errorType;
				}

				if ($innerType->isSubtypeOf(
					$typeRegistry->union([
						$typeRegistry->array(),
						$typeRegistry->map(),
						$typeRegistry->set()
					])
				)) {
					if ($innerType instanceof MapType || $innerType instanceof ArrayType || $innerType instanceof SetType) {
						if ($innerType->itemType->isSubtypeOf($parameterType->parameterType)) {
							$t = match(true) {
								$innerType instanceof MapType => $typeRegistry->map(
									$returnType,
									$innerType->range->minLength,
									$innerType->range->maxLength,
								),
								$innerType instanceof ArrayType => $typeRegistry->array(
									$returnType,
									$innerType->range->minLength,
									$innerType->range->maxLength,
								),
								$innerType instanceof SetType => $typeRegistry->set(
									$returnType,
									$innerType->range->minLength > 0 ? 1 : 0,
									$innerType->range->maxLength,
								)
							};
							return $typeRegistry->result($t, $combinedErrorType);
						}
						throw new AnalyserException(
							sprintf(
								"The parameter type %s of the callback function is not a subtype of %s",
								$innerType->itemType,
								$parameterType->parameterType
							)
						);
					}
					// It should be a union type
					if ($innerType instanceof UnionType) {
						$ts = [];
						foreach ($innerType->types as $innerUnionType) {
							$ts[] = match(true) {
								$innerUnionType instanceof MapType => $typeRegistry->map(
									$returnType,
									$innerUnionType->range->minLength,
									$innerUnionType->range->maxLength,
								),
								$innerUnionType instanceof ArrayType => $typeRegistry->array(
									$returnType,
									$innerUnionType->range->minLength,
									$innerUnionType->range->maxLength,
								),
								$innerUnionType instanceof SetType => $typeRegistry->set(
									$returnType,
									$innerUnionType->range->minLength > 0 ? 1 : 0,
									$innerUnionType->range->maxLength,
								)
							};
						}
						return $typeRegistry->result(
							$typeRegistry->union($ts),
							$combinedErrorType
						);
					}
				}
				// @codeCoverageIgnoreStart
				throw new AnalyserException(sprintf("[%s] Invalid target type: Result must contain a Map or Array, got %s", __CLASS__, $targetType->returnType));
				// @codeCoverageIgnoreEnd
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
		$targetValue = $target;
		$parameterValue = $parameter;

		// If the target is an error, return it as-is
		if ($targetValue instanceof ErrorValue) {
			return $targetValue;
		}

		if ($parameterValue instanceof FunctionValue) {
			if ($targetValue instanceof TupleValue) {
				$values = $targetValue->values;
				$result = [];
				foreach ($values as $value) {
					$r = $parameterValue->execute($programRegistry->executionContext, $value);
					if ($r instanceof ErrorValue) {
						return $r;
					}
					$result[] = $r;
				}
				return $programRegistry->valueRegistry->tuple($result);
			}

			if ($targetValue instanceof RecordValue) {
				$values = $targetValue->values;
				$result = [];
				foreach ($values as $key => $value) {
					$r = $parameterValue->execute($programRegistry->executionContext, $value);
					if ($r instanceof ErrorValue) {
						return $r;
					}
					$result[$key] = $r;
				}
				return $programRegistry->valueRegistry->record($result);
			}

			if ($targetValue instanceof SetValue) {
				$values = $targetValue->values;
				$result = [];
				foreach ($values as $value) {
					$r = $parameterValue->execute($programRegistry->executionContext, $value);
					if ($r instanceof ErrorValue) {
						return $r;
					}
					$result[] = $r;
				}
				return $programRegistry->valueRegistry->set($result);
			}

		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}

}
