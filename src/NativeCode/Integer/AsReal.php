<?php

namespace Walnut\Lang\NativeCode\Integer;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Function\MethodExecutionContext;
use Walnut\Lang\Blueprint\Range\MinusInfinity;
use Walnut\Lang\Blueprint\Range\PlusInfinity;
use Walnut\Lang\Blueprint\Type\IntegerSubsetType;
use Walnut\Lang\Blueprint\Type\IntegerType;
use Walnut\Lang\Blueprint\Type\RealSubsetType;
use Walnut\Lang\Blueprint\Type\RealType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\IntegerValue;
use Walnut\Lang\Blueprint\Value\RealValue;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class AsReal implements NativeMethod {
	use BaseType;

	public function __construct(
		private MethodExecutionContext $context
	) {}

	public function analyse(
		Type $targetType,
		Type $parameterType,
	): Type {
		$targetType = $this->toBaseType($targetType);
		if ($targetType instanceof IntegerSubsetType || $targetType instanceof RealSubsetType) {
			return $this->context->typeRegistry()->realSubset(
				array_map(fn(RealValue|IntegerValue $v) =>
				$this->context->valueRegistry()->real((float)$v->literalValue()),
					$targetType->subsetValues())
			);
		}
		if ($targetType instanceof IntegerType || $targetType instanceof RealType) {
			return $this->context->typeRegistry()->real(
				$targetType->range()->minValue() === MinusInfinity::value ? MinusInfinity::value :
					(float)$targetType->range()->minValue(),
				$targetType->range()->maxValue() === PlusInfinity::value ? PlusInfinity::value :
					(float)$targetType->range()->maxValue()
			);
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

		$targetValue = $this->toBaseValue($targetValue);
		if ($targetValue instanceof IntegerValue || $targetValue instanceof RealValue) {
			$target = $targetValue->literalValue();
			return TypedValue::forValue($this->context->valueRegistry()->real((float)$target));
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}

}