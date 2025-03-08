<?php

namespace Walnut\Lang\NativeCode\Array;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Type\ArrayType;
use Walnut\Lang\Blueprint\Type\FunctionType;
use Walnut\Lang\Blueprint\Type\TupleType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\FunctionValue;
use Walnut\Lang\Blueprint\Value\TupleValue;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class FindFirst implements NativeMethod {
	use BaseType;

	public function analyse(
		ProgramRegistry $programRegistry,
		Type $targetType,
		Type $parameterType,
	): Type {
		$targetType = $this->toBaseType($targetType);
		$type = $targetType instanceof TupleType ? $targetType->asArrayType() : $targetType;
		if ($type instanceof ArrayType) {
			$parameterType = $this->toBaseType($parameterType);
			if ($parameterType instanceof FunctionType && $parameterType->returnType->isSubtypeOf($programRegistry->typeRegistry->boolean)) {
				if ($type->itemType->isSubtypeOf($parameterType->parameterType)) {
					return $programRegistry->typeRegistry->result(
						$type->itemType,
						$programRegistry->typeRegistry->atom(new TypeNameIdentifier('ItemNotFound'))
					);
				}
				throw new AnalyserException(
					sprintf("The parameter type %s of the callback function is not a subtype of %s",
						$type->itemType,
						$parameterType->parameterType
					),
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
		TypedValue $target,
		TypedValue $parameter
	): TypedValue {
		$targetValue = $target->value;
		$parameterValue = $parameter->value;

		if ($targetValue instanceof TupleValue && $parameterValue instanceof FunctionValue) {
			$values = $targetValue->values;
			$true = $programRegistry->valueRegistry->true;
			foreach($values as $value) {
				$r = $parameterValue->execute($programRegistry->executionContext, $value)->value;
				if ($true->equals($r)) {
					$resultType = $value instanceof TypedValue ? $value->type : $value->type;
					$resultValue = $value instanceof TypedValue ? $value->value : $value;

					return TypedValue::forValue($resultValue)->withType(
						$programRegistry->typeRegistry->result(
							$resultType,
							$programRegistry->typeRegistry->atom(new TypeNameIdentifier('ItemNotFound'))
						)
					);
				}
			}
			return TypedValue::forValue(
				$programRegistry->valueRegistry->error(
					$programRegistry->valueRegistry->atom(new TypeNameIdentifier('ItemNotFound'))
				)
			);
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}

}