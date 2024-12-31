<?php

namespace Walnut\Lang\NativeCode\Map;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Function\MethodExecutionContext;
use Walnut\Lang\Blueprint\Range\PlusInfinity;
use Walnut\Lang\Blueprint\Type\MapType;
use Walnut\Lang\Blueprint\Type\RecordType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\RecordValue;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class Without implements NativeMethod {
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
			$returnType = $this->context->typeRegistry->map(
				$targetType->itemType,
				max(0, $targetType->range->minLength - 1),
				$targetType->range->maxLength === PlusInfinity::value ?
					PlusInfinity::value : $targetType->range->maxLength - 1
			);
			return $this->context->typeRegistry->result(
				$returnType,
				$this->context->typeRegistry->atom(
					new TypeNameIdentifier("ItemNotFound")
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
		$parameterValue = $parameter->value;
		
		$targetValue = $this->toBaseValue($targetValue);
		if ($targetValue instanceof RecordValue) {
			$values = $targetValue->values;
			foreach($values as $key => $value) {
				if ($value->equals($parameterValue)) {
					unset($values[$key]);
					return TypedValue::forValue($this->context->valueRegistry->record($values));
				}
			}
			return TypedValue::forValue($this->context->valueRegistry->error(
				$this->context->valueRegistry->atom(new TypeNameIdentifier("ItemNotFound"))
			));
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}

}