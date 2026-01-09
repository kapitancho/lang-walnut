<?php

namespace Walnut\Lang\NativeCode\Mutable;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\MethodAnalyser;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\FunctionType;
use Walnut\Lang\Blueprint\Type\MapType;
use Walnut\Lang\Blueprint\Type\MutableType;
use Walnut\Lang\Blueprint\Type\ResultType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\FunctionValue;
use Walnut\Lang\Blueprint\Value\MutableValue;
use Walnut\Lang\Blueprint\Value\RecordValue;
use Walnut\Lang\Blueprint\Value\StringValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class REMAPKEYS implements NativeMethod {
	use BaseType;

	public function analyse(
		TypeRegistry $typeRegistry,
		MethodAnalyser $methodAnalyser,
		Type $targetType,
		Type $parameterType,
	): Type {
		$targetType = $this->toBaseType($targetType);
		if ($targetType instanceof MutableType) {
			$valueType = $this->toBaseType($targetType->valueType);
			if ($valueType instanceof MapType) {
				if ($valueType->range->minLength > 1) {
					throw new AnalyserException(
						"Invalid target type: REMAPKEYS can only be used on maps with a minimum size of 0 or 1"
					);
				}
				$parameterType = $this->toBaseType($parameterType);
				if ($parameterType instanceof FunctionType) {
					if ($valueType->keyType->isSubtypeOf($parameterType->parameterType)) {
						$r = $parameterType->returnType;
						$returnType = $r instanceof ResultType ? $r->returnType : $r;
						if ($returnType->isSubtypeOf($valueType->keyType)) {
							return $targetType;
						}
						throw new AnalyserException(
							sprintf(
								"The return type %s of the callback function is not a subtype of %s",
								$returnType,
								$valueType->keyType
							)
						);
					}
					throw new AnalyserException(
						sprintf(
							"The parameter type %s of the callback function is not a supertype of %s",
							$parameterType->parameterType,
							$valueType->keyType,
						)
					);
				}
				throw new AnalyserException(sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType));
			}
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
		if ($target instanceof MutableValue) {
			$v = $target->value;
			if ($v instanceof RecordValue && $parameter instanceof FunctionValue) {
				$values = $v->values;
				$result = [];
				foreach($values as $key => $value) {
					$r = $parameter->execute(
						$programRegistry->executionContext,
						$programRegistry->valueRegistry->string($key)
					);
					if ($r instanceof StringValue) {
						$result[$r->literalValue] = $value;
					} else {
						// @codeCoverageIgnoreStart
						throw new ExecutionException("Invalid callback value");
						// @codeCoverageIgnoreEnd
					}
				}
				$target->value = $programRegistry->valueRegistry->record($result);
				return $target;
			}
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}

}
