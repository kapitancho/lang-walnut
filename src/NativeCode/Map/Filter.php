<?php

namespace Walnut\Lang\NativeCode\Map;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Function\MethodExecutionContext;
use Walnut\Lang\Blueprint\Type\FunctionType;
use Walnut\Lang\Blueprint\Type\MapType;
use Walnut\Lang\Blueprint\Type\RecordType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\RecordValue;
use Walnut\Lang\Blueprint\Value\FunctionValue;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class Filter implements NativeMethod {
	use BaseType;

	public function __construct(
		private MethodExecutionContext $context
	) {}

	public function analyse(
		Type $targetType,
		Type $parameterType,
	): Type {
		$targetType = $this->toBaseType($targetType);
		if ($targetType instanceof RecordType) {
			$targetType = $targetType->asMapType();
		}
		if ($targetType instanceof MapType) {
			$parameterType = $this->toBaseType($parameterType);
			if ($parameterType instanceof FunctionType && $parameterType->returnType->isSubtypeOf($this->context->typeRegistry->boolean)) {
				if ($targetType->itemType->isSubtypeOf($parameterType->parameterType)) {
					return $this->context->typeRegistry->map(
						$targetType->itemType,
						0,
						$targetType->range->maxLength
					);
				}
				throw new AnalyserException(
					"The parameter type %s of the callback function is not a subtype of %s",
					$targetType->itemType,
					$parameterType->parameterType
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
		if ($targetValue instanceof RecordValue && $parameterValue instanceof FunctionValue) {
			$values = $targetValue->values;
			$result = [];
			$true = $this->context->valueRegistry->true;
			foreach($values as $key => $value) {
				$r = $parameterValue->execute($this->context->globalContext, $value);
				if ($true->equals($r)) {
					$result[$key] = $value;
				}
			}
			return TypedValue::forValue($this->context->valueRegistry->record($result));
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}

}