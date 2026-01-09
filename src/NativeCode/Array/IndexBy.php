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
use Walnut\Lang\Blueprint\Type\TupleType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\FunctionValue;
use Walnut\Lang\Blueprint\Value\StringValue;
use Walnut\Lang\Blueprint\Value\TupleValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class IndexBy implements NativeMethod {
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
				$returnType = $this->toBaseType($parameterType->returnType);
				if ($returnType->isSubtypeOf($typeRegistry->string())) {
					if ($type->itemType->isSubtypeOf($parameterType->parameterType)) {
						$maxLength = $type->range->maxLength;
						$minLength = (string)$type->range->minLength === '0' ||
							($maxLength !== PlusInfinity::value && (string)$maxLength === '0') ? 0 : $type->range->minLength;

						return $typeRegistry->map(
							$type->itemType,
							$minLength,
							$maxLength,
							$returnType
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
				throw new AnalyserException(
					sprintf(
						"The return type of the callback function must be a subtype of String, got %s",
						$returnType
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
				$key = $parameter->execute($programRegistry->executionContext, $value);
				if ($key instanceof StringValue) {
					$result[$key->literalValue] = $value;
				} else {
					// @codeCoverageIgnoreStart
					throw new ExecutionException("Key function must return a String value");
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
