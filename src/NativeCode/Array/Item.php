<?php

namespace Walnut\Lang\NativeCode\Array;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Common\Range\MinusInfinity;
use Walnut\Lang\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Blueprint\Function\MethodExecutionContext;
use Walnut\Lang\Blueprint\Function\NativeMethod;
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
				$returnType = $type->itemType;
				if ($targetType instanceof TupleType) {
					$min = $parameterType->range->minValue;
					$max = $parameterType->range->maxValue;
					if ($min !== MinusInfinity::value && $min >= 0) {
						if ($parameterType instanceof IntegerSubsetType) {
							$returnType = $this->context->typeRegistry->union(
								array_map(
									static fn(IntegerValue $value) =>
										$targetType->types[(string)$value->literalValue] ?? $targetType->restType,
									$parameterType->subsetValues
								)
							);
						} elseif ($parameterType instanceof IntegerType) {
							$isWithinLimit = $max !== PlusInfinity::value && $max < count($targetType->types);
							$returnType = $this->context->typeRegistry->union(
								$isWithinLimit ?
								array_slice($targetType->types, (int)(string)$min, (int)(string)$max - (int)(string)$min + 1) :
								[... array_slice($targetType->types, (int)(string)$min), $targetType->restType]
							);
						}
					}
				}

				return $type->range->minLength > $parameterType->range->maxValue ? $returnType :
					$this->context->typeRegistry->result(
						$returnType,
						$this->context->typeRegistry->sealed(
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
			$values = $targetValue->values;
			$result = $values[(string)$parameterValue->literalValue] ?? null;
			if ($result !== null) {
				$targetType = $this->toBaseType($target->type);
				$type = match(true) {
					$targetType instanceof TupleType => ($targetType->types[(string)$parameterValue->literalValue] ?? $targetType->restType),
					$targetType instanceof ArrayType => $targetType->itemType,
					default => $result->type
				};
				return new TypedValue($type, $result);
			}
			return TypedValue::forValue($this->context->valueRegistry->error(
				$this->context->valueRegistry->sealedValue(
					new TypeNameIdentifier('IndexOutOfRange'),
					$this->context->valueRegistry->record(['index' => $parameterValue])
				)
			));
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}

}