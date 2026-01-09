<?php

namespace Walnut\Lang\NativeCode\Map;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\MethodAnalyser;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\FunctionType;
use Walnut\Lang\Blueprint\Type\MapType;
use Walnut\Lang\Blueprint\Type\RecordType;
use Walnut\Lang\Blueprint\Type\ResultType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\ErrorValue;
use Walnut\Lang\Blueprint\Value\FunctionValue;
use Walnut\Lang\Blueprint\Value\RecordValue;
use Walnut\Lang\Blueprint\Value\StringValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class RemapKeys implements NativeMethod {
	use BaseType;

	public function analyse(
		TypeRegistry $typeRegistry,
		MethodAnalyser $methodAnalyser,
		Type $targetType,
		Type $parameterType,
	): Type {
		$targetType = $this->toBaseType($targetType);
		if ($targetType instanceof RecordType) {
			$targetType = $targetType->asMapType();
		}
		if ($targetType instanceof MapType) {
			$parameterType = $this->toBaseType($parameterType);
			if ($parameterType instanceof FunctionType) {
				if ($targetType->keyType->isSubtypeOf($parameterType->parameterType)) {
					$r = $parameterType->returnType;
					$errorType = $r instanceof ResultType ? $r->errorType : null;
					$returnType = $r instanceof ResultType ? $r->returnType : $r;
					if ($returnType->isSubtypeOf($typeRegistry->string())) {
						$t = $typeRegistry->map(
							$targetType->itemType,
							$targetType->range->minLength > 0 ? 1 : 0,
							$targetType->range->maxLength,
							$returnType,
						);
						return $errorType ? $typeRegistry->result($t, $errorType) : $t;
					}
					throw new AnalyserException(
						sprintf(
							"The return type %s of the callback function is not a subtype of String",
							$returnType
						)
					);
				}
				throw new AnalyserException(
                    sprintf(
    					"The parameter type %s of the callback function is not a supertype of %s",
	                    $parameterType->parameterType,
	    				$targetType->keyType,
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
		if ($target instanceof RecordValue && $parameter instanceof FunctionValue) {
			$values = $target->values;
			$result = [];
			foreach($values as $key => $value) {
				$r = $parameter->execute(
					$programRegistry->executionContext,
					$programRegistry->valueRegistry->string($key)
				);
				if ($r instanceof ErrorValue) {
					return $r;
				}
				if ($r instanceof StringValue) {
					$result[$r->literalValue] = $value;
				} else {
					// @codeCoverageIgnoreStart
					throw new ExecutionException("Invalid callback value");
					// @codeCoverageIgnoreEnd
				}
			}
			return $programRegistry->valueRegistry->record($result);
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}

}