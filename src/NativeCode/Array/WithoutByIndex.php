<?php

namespace Walnut\Lang\NativeCode\Array;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Type\ArrayType;
use Walnut\Lang\Blueprint\Type\IntegerSubsetType;
use Walnut\Lang\Blueprint\Type\IntegerType;
use Walnut\Lang\Blueprint\Type\TupleType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\IntegerValue;
use Walnut\Lang\Blueprint\Value\TupleValue;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class WithoutByIndex implements NativeMethod {
	use BaseType;

	public function analyse(
		ProgramRegistry $programRegistry,
		Type $targetType,
		Type $parameterType,
	): Type {
		$targetType = $this->toBaseType($targetType);
		$type = $targetType instanceof TupleType ? $targetType->asArrayType() : $targetType;
		if ($type instanceof ArrayType) {
			if ($parameterType instanceof IntegerType || $parameterType instanceof IntegerSubsetType) {
				$returnType = $programRegistry->typeRegistry->record([
					'element' => $type->itemType,
					'array' => $programRegistry->typeRegistry->array(
						$type->itemType,
						max(0, $type->range->minLength - 1),
						$type->range->maxLength === PlusInfinity::value ?
							PlusInfinity::value : $type->range->maxLength - 1
					)
				]);
				return $parameterType->range->minValue < $type->range->maxLength ? $returnType :
					$programRegistry->typeRegistry->result(
						$returnType,
						$programRegistry->typeRegistry->sealed(
							new TypeNameIdentifier("IndexOutOfRange")
						)
					);
			}
			// @codeCoverageIgnoreStart
			throw new AnalyserException(sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType));
			// @codeCoverageIgnoreEnd
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
		
		$targetValue = $this->toBaseValue($targetValue);
		if ($targetValue instanceof TupleValue) {
			if ($parameterValue instanceof IntegerValue) {
				$values = $targetValue->values;
				$p = (string)$parameterValue->literalValue;
				if (!array_key_exists($p, $values)) {
					return TypedValue::forValue($programRegistry->valueRegistry->sealedValue(
						new TypeNameIdentifier('IndexOutOfRange'),
						$programRegistry->valueRegistry->record(['index' => $parameterValue])
					));
				}
				$removed = array_splice($values, $p, 1);
				return TypedValue::forValue($programRegistry->valueRegistry->record([
					'element' => $removed[0],
					'array' => $programRegistry->valueRegistry->tuple($values)
				]));
			}
			// @codeCoverageIgnoreStart
			throw new ExecutionException("Invalid parameter value");
			// @codeCoverageIgnoreEnd
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}

}