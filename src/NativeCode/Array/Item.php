<?php

namespace Walnut\Lang\NativeCode\Array;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Function\MethodExecutionContext;
use Walnut\Lang\Blueprint\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Range\MinusInfinity;
use Walnut\Lang\Blueprint\Range\PlusInfinity;
use Walnut\Lang\Blueprint\Type\ArrayType;
use Walnut\Lang\Blueprint\Type\IntegerSubsetType;
use Walnut\Lang\Blueprint\Type\TupleType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\IntegerValue;
use Walnut\Lang\Blueprint\Value\TupleValue;
use Walnut\Lang\Implementation\Type\Helper\BaseType;
use Walnut\Lang\Implementation\Type\IntegerType;

final readonly class Item implements NativeMethod {
	use BaseType;

	public function __construct(
		private MethodExecutionContext $context
	) {}

	public function analyse(
		Type $targetType,
		Type $parameterType
	): Type {
		$targetType = $this->toBaseType($targetType);
		$type = $targetType instanceof TupleType ? $targetType->asArrayType() : $targetType;
		if ($type instanceof ArrayType) {
			if ($parameterType instanceof IntegerType || $parameterType instanceof IntegerSubsetType) {
				$returnType = $type->itemType();
				if ($targetType instanceof TupleType) {
					$min = $parameterType->range()->minValue();
					$max = $parameterType->range()->maxValue();
					if ($min !== MinusInfinity::value && $min >= 0) {
						if ($parameterType instanceof IntegerSubsetType) {
							$returnType = $this->context->typeRegistry()->union(
								array_map(
									static fn(IntegerValue $value) =>
										$targetType->types()[$value->literalValue()] ?? $targetType->restType(),
									$parameterType->subsetValues()
								)
							);
						} elseif ($parameterType instanceof IntegerType) {
							$isWithinLimit = $max !== PlusInfinity::value && $max < count($targetType->types());
							$returnType = $this->context->typeRegistry()->union(
								$isWithinLimit ?
								array_slice($targetType->types(), $min, $max - $min + 1) :
								[... array_slice($targetType->types(), $min), $targetType->restType()]
							);
						}
					}
				}

				return $type->range()->minLength() > $parameterType->range()->maxValue() ? $returnType :
					$this->context->typeRegistry()->result(
						$returnType,
						$this->context->typeRegistry()->sealed(
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
		TypedValue $target,
		TypedValue $parameter
	): TypedValue {
		$targetValue = $target->value;
		$parameterValue = $parameter->value;
		
		$targetValue = $this->toBaseValue($targetValue);
		if ($targetValue instanceof TupleValue && $parameterValue instanceof IntegerValue) {
			$values = $targetValue->values();
			$result = $values[$parameterValue->literalValue()] ?? null;
			if ($result) {
				$targetType = $this->toBaseType($target->type);
				$type = $targetType instanceof TupleType ?
					($targetType->types()[$parameterValue->literalValue()] ?? null) :
					$targetType->itemType();
				return new TypedValue($type, $result);
			}
			return TypedValue::forValue($this->context->valueRegistry()->error(
				$this->context->valueRegistry()->sealedValue(
					new TypeNameIdentifier('IndexOutOfRange'),
					$this->context->valueRegistry()->record(['index' => $parameterValue])
				)
			));
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}

}