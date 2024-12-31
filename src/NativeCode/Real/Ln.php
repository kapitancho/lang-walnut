<?php

namespace Walnut\Lang\NativeCode\Real;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Function\MethodExecutionContext;
use Walnut\Lang\Blueprint\Type\RealSubsetType;
use Walnut\Lang\Blueprint\Type\RealType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\IntegerValue;
use Walnut\Lang\Blueprint\Value\RealValue;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class Ln implements NativeMethod {
	use BaseType;

	public function __construct(
		private MethodExecutionContext $context
	) {}

	public function analyse(
		Type $targetType,
		Type $parameterType,
	): Type {
		$targetType = $this->toBaseType($targetType);
        if ($targetType instanceof RealType || $targetType instanceof RealSubsetType) {
			$min = $targetType->range->minValue;
			$max = $targetType->range->maxValue;
            $real = $this->context->typeRegistry->real(max: $max);
            return $min > 0 ? $real :
                $this->context->typeRegistry->result(
                    $real,
                    $this->context->typeRegistry->atom(
                        new TypeNameIdentifier('NotANumber')
                    )
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
		if ($targetValue instanceof RealValue || $targetValue instanceof IntegerValue) {
            $val = $targetValue->literalValue;
			return TypedValue::forValue($val > 0 ? $this->context->valueRegistry->real(
				log((string)$val)
			) : $this->context->valueRegistry->error(
                $this->context->valueRegistry->atom(
                    new TypeNameIdentifier("NotANumber")
                )
            ));
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}
}