<?php

namespace Walnut\Lang\NativeCode\Array;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Function\MethodExecutionContext;
use Walnut\Lang\Blueprint\Type\ArrayType;
use Walnut\Lang\Blueprint\Type\StringSubsetType;
use Walnut\Lang\Blueprint\Type\StringType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\TupleValue;
use Walnut\Lang\Blueprint\Value\StringValue;
use Walnut\Lang\Implementation\Type\Helper\BaseType;
use Walnut\Lang\Implementation\Type\TupleType;

final readonly class CombineAsString implements NativeMethod {
	use BaseType;

	public function __construct(
		private MethodExecutionContext $context
	) {}

	public function analyse(
		Type $targetType,
		Type $parameterType,
	): Type {
		$targetType = $this->toBaseType($targetType);
		if ($targetType instanceof TupleType) {
			$targetType = $targetType->asArrayType();
		}
		if ($targetType instanceof ArrayType) {
			$itemType = $targetType->itemType();
			if ($itemType->isSubtypeOf($this->context->typeRegistry()->string())) {
				if ($parameterType instanceof StringType || $parameterType instanceof StringSubsetType) {
					return $this->context->typeRegistry()->string(
						$parameterType->range()->minLength() * max(0, $targetType->range()->minLength() - 1), /* +
						$itemType->range()->minLength() * $targetType->range()->minLength(),
						$parameterType->range()->maxLength() === PlusInfinity::value ||
						$itemType->range()->maxLength() === PlusInfinity::value ||
						$targetType->range()->maxLength() === PlusInfinity::value ? PlusInfinity::value :
							$parameterType->range()->maxLength() * max(0, $targetType->range()->maxLength() - 1) +
								$itemType->range()->maxLength() * $targetType->range()->maxLength()*/
					);
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
		$parameterValue = $parameter->value;
		
		$targetValue = $this->toBaseValue($targetValue);
		$parameterValue = $this->toBaseValue($parameterValue);
		if ($targetValue instanceof TupleValue) {
			if ($parameterValue instanceof StringValue) {
				$result = [];
				foreach($targetValue->values() as $value) {
					if ($value instanceof StringValue) {
						$result[] = $value->literalValue();
					} else {
						echo $value::class, $targetValue;
						// @codeCoverageIgnoreStart
						throw new ExecutionException("Invalid parameter value");
						// @codeCoverageIgnoreEnd
					}
				}
				$result = implode($parameterValue->literalValue(), $result);
				return TypedValue::forValue($this->context->valueRegistry()->string($result));
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