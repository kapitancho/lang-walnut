<?php

namespace Walnut\Lang\NativeCode\Enumeration;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Function\MethodExecutionContext;
use Walnut\Lang\Blueprint\Type\NullType;
use Walnut\Lang\Blueprint\Type\Type as TypeInterface;
use Walnut\Lang\Blueprint\Value\EnumerationValue;
use Walnut\Lang\Blueprint\Value\NullValue;
use Walnut\Lang\Implementation\Type\EnumerationSubsetType;
use Walnut\Lang\Implementation\Type\EnumerationType;

final readonly class TextValue implements NativeMethod {

	public function __construct(
		private MethodExecutionContext $context
	) {}

	public function analyse(
		TypeInterface $targetType,
		TypeInterface $parameterType,
	): TypeInterface {
		if ($targetType instanceof EnumerationType || $targetType instanceof EnumerationSubsetType) {
			if ($parameterType instanceof NullType) {
				$min = 0;
				$max = 999999;
				foreach($targetType->subsetValues() as $value) {
					$l = mb_strlen($value->name());
					$min = min($min, $l);
					$max = max($max, $l);
				}
				return $this->context->typeRegistry()->string($min, $max);
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
		
		if ($targetValue instanceof EnumerationValue) {
			if ($parameterValue instanceof NullValue) {
				return TypedValue::forValue($this->context->valueRegistry()->string($targetValue->name()->identifier));
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