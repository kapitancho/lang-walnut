<?php

namespace Walnut\Lang\NativeCode\Type;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Function\MethodExecutionContext;
use Walnut\Lang\Blueprint\Range\PlusInfinity;
use Walnut\Lang\Blueprint\Type\ArrayType;
use Walnut\Lang\Blueprint\Type\MapType;
use Walnut\Lang\Blueprint\Type\StringType;
use Walnut\Lang\Blueprint\Type\Type as TypeInterface;
use Walnut\Lang\Blueprint\Type\TypeType;
use Walnut\Lang\Blueprint\Value\IntegerValue;
use Walnut\Lang\Blueprint\Value\TypeValue;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class WithLengthRange implements NativeMethod {

	use BaseType;

	public function __construct(
		private MethodExecutionContext $context
	) {}

	public function analyse(
		TypeInterface $targetType,
		TypeInterface $parameterType,
	): TypeInterface {
		if ($targetType instanceof TypeType) {
			$refType = $this->toBaseType($targetType->refType);
			if ($parameterType->isSubtypeOf(
				$this->context->typeRegistry->withName(new TypeNameIdentifier('LengthRange'))
			)) {
				if ($refType instanceof StringType) {
					return $this->context->typeRegistry->type($this->context->typeRegistry->string());
				}
				if ($refType instanceof ArrayType) {
					return $this->context->typeRegistry->type($this->context->typeRegistry->array($refType->itemType));
				}
				if ($refType instanceof MapType) {
					return $this->context->typeRegistry->type($this->context->typeRegistry->map($refType->itemType));
				}
				// @codeCoverageIgnoreStart
				throw new AnalyserException(sprintf("[%s] Invalid target type: %s", __CLASS__, $targetType));
				// @codeCoverageIgnoreEnd
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

		if ($targetValue instanceof TypeValue) {
			$typeValue = $this->toBaseType($targetValue->typeValue);
			if ($parameter->type->isSubtypeOf(
				$this->context->typeRegistry->withName(new TypeNameIdentifier('LengthRange'))
			)) {
				if ($typeValue instanceof StringType) {
					$range = $this->toBaseValue($parameter->value)->values;
					$minValue = $range['minLength'];
					$maxValue = $range['maxLength'];
					$result = $this->context->typeRegistry->string(
						$minValue->literalValue,
						$maxValue instanceof IntegerValue ? $maxValue->literalValue : PlusInfinity::value,
					);
					return TypedValue::forValue($this->context->valueRegistry->type($result));
				}
				if ($typeValue instanceof ArrayType) {
					$range = $this->toBaseValue($parameter->value)->values;
					$minValue = $range['minLength'];
					$maxValue = $range['maxLength'];
					$result = $this->context->typeRegistry->array(
						$typeValue->itemType,
						$minValue->literalValue,
						$maxValue instanceof IntegerValue ? $maxValue->literalValue : PlusInfinity::value,
					);
					return TypedValue::forValue($this->context->valueRegistry->type($result));
				}
				if ($typeValue instanceof MapType) {
					$range = $this->toBaseValue($parameter->value)->values;
					$minValue = $range['minLength'];
					$maxValue = $range['maxLength'];
					$result = $this->context->typeRegistry->map(
						$typeValue->itemType,
						$minValue->literalValue,
						$maxValue instanceof IntegerValue ? $maxValue->literalValue : PlusInfinity::value,
					);
					return TypedValue::forValue($this->context->valueRegistry->type($result));
				}
			}
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid parameter value");
		// @codeCoverageIgnoreEnd
	}

}