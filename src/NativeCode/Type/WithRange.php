<?php

namespace Walnut\Lang\NativeCode\Type;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Function\MethodExecutionContext;
use Walnut\Lang\Blueprint\Range\MinusInfinity;
use Walnut\Lang\Blueprint\Range\PlusInfinity;
use Walnut\Lang\Blueprint\Type\IntegerType;
use Walnut\Lang\Blueprint\Type\RealType;
use Walnut\Lang\Blueprint\Type\Type as TypeInterface;
use Walnut\Lang\Blueprint\Type\TypeType;
use Walnut\Lang\Blueprint\Value\IntegerValue;
use Walnut\Lang\Blueprint\Value\RealValue;
use Walnut\Lang\Blueprint\Value\TypeValue;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class WithRange implements NativeMethod {

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
			if ($refType instanceof IntegerType) {
				if ($parameterType->isSubtypeOf(
					$this->context->typeRegistry->withName(new TypeNameIdentifier('IntegerRange'))
				)) {
					return $this->context->typeRegistry->type($this->context->typeRegistry->integer());
				}
				// @codeCoverageIgnoreStart
				throw new AnalyserException(sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType));
				// @codeCoverageIgnoreEnd
			}
			if ($refType instanceof RealType) {
				if ($parameterType->isSubtypeOf(
					$this->context->typeRegistry->withName(new TypeNameIdentifier('RealRange'))
				)) {
					return $this->context->typeRegistry->type($this->context->typeRegistry->real());
				}
				// @codeCoverageIgnoreStart
				throw new AnalyserException(sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType));
				// @codeCoverageIgnoreEnd
			}
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
			if ($typeValue instanceof IntegerType) {
				if ($parameter->type->isSubtypeOf(
					$this->context->typeRegistry->withName(new TypeNameIdentifier('IntegerRange'))
				)) {
					$range = $this->toBaseValue($parameter->value)->values;
					$minValue = $range['minValue'];
					$maxValue = $range['maxValue'];
					$result = $this->context->typeRegistry->integer(
						$minValue instanceof IntegerValue ? $minValue->literalValue : MinusInfinity::value,
						$maxValue instanceof IntegerValue ? $maxValue->literalValue : PlusInfinity::value,
					);
					return TypedValue::forValue($this->context->valueRegistry->type($result));
				}
			}
			if ($typeValue instanceof RealType) {
				if ($parameter->type->isSubtypeOf(
					$this->context->typeRegistry->withName(new TypeNameIdentifier('RealRange'))
				)) {
					$range = $this->toBaseValue($parameter->value)->values;
					$minValue = $range['minValue'];
					$maxValue = $range['maxValue'];
					$result = $this->context->typeRegistry->real(
						$minValue instanceof RealValue ? $minValue->literalValue : MinusInfinity::value,
						$maxValue instanceof RealValue ? $maxValue->literalValue : PlusInfinity::value,
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